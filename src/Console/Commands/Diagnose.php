<?php

namespace ArtflowStudio\AccountFlow\Console\Commands;

use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\EquityTransaction;
use ArtflowStudio\AccountFlow\Models\Loan;
use ArtflowStudio\AccountFlow\Models\LoanTransaction;
use ArtflowStudio\AccountFlow\Models\PlannedPayment;
use ArtflowStudio\AccountFlow\Models\Transaction;
use ArtflowStudio\AccountFlow\Models\Transfer;
use ArtflowStudio\AccountFlow\Models\UserTransfer;
use ArtflowStudio\AccountFlow\Models\UserWallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Read-only health check for a live AccountFlow install.
 *
 * Run this **before** upgrading and again afterwards. It writes nothing — it
 * reports what is wrong and which command fixes it.
 *
 * Most of what it looks for are the consequences of pre-0.3.0 bugs where a
 * screen recorded something without moving any money.
 */
class Diagnose extends Command
{
    protected $signature = 'accountflow:diagnose {--verbose-rows=10 : How many example rows to show per finding}';

    protected $description = 'Check a live AccountFlow install for data problems (read-only)';

    /** @var list<array{level: string, title: string, detail: string, fix: string|null}> */
    private array $findings = [];

    public function handle(): int
    {
        $this->newLine();
        $this->components->info('AccountFlow health check — nothing is written.');
        $this->newLine();

        if (! $this->schemaReady()) {
            return self::FAILURE;
        }

        $this->checkAdminAccess();
        $this->checkBalanceDrift();
        $this->checkUnpostedTransfers();
        $this->checkLoansWithoutPostings();
        $this->checkEquityWithoutPostings();
        $this->checkPlannedPayments();
        $this->checkWallets();
        $this->checkOrphans();

        return $this->report();
    }

    /**
     * Are the tables and 0.3.0 columns present?
     */
    private function schemaReady(): bool
    {
        if (! Schema::hasTable('ac_transactions')) {
            $this->components->error('AccountFlow tables are missing. Run: php artisan migrate');

            return false;
        }

        $missing = [];

        foreach ([
            'ac_transactions' => ['transfer_id', 'reversal_of_id', 'reversed_at'],
            'ac_transfers' => ['from_trx_id', 'to_trx_id'],
        ] as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    $missing[] = "{$table}.{$column}";
                }
            }
        }

        if ($missing !== []) {
            $this->add('error', 'Migrations are not up to date',
                'Missing: '.implode(', ', $missing),
                'php artisan migrate');

            $this->report();

            return false;
        }

        return true;
    }

    /**
     * Can anyone actually be recognised as an AccountFlow admin?
     *
     * `admin_management.check` defaults to 'isAdmin'. If the user model has no
     * such method — which is the normal case for an app using
     * spatie/laravel-permission, where roles are checked with hasRole(), not an
     * attribute — every "manage-*" ability in AccountFlow is denied to every
     * user, including a real admin, with no error and no log line. Only
     * `diagnose` or a locked-out user ever surfaces it.
     */
    private function checkAdminAccess(): void
    {
        $config = config('accountflow.admin_management', []);

        if (! ($config['enabled'] ?? true)) {
            $this->components->twoColumnDetail('Admin management', '<fg=yellow>disabled — every user can manage AccountFlow</>');

            return;
        }

        $userModel = config('auth.providers.users.model');

        if (! is_string($userModel) || ! class_exists($userModel)) {
            return;
        }

        $roles = $config['roles'] ?? null;
        $roleUsable = ($roles !== null && $roles !== [])
            && (method_exists($userModel, 'hasRole') || method_exists($userModel, 'hasAnyRole'));

        $check = $config['check'] ?? null;
        $checkUsable = match (true) {
            $check === null => false,
            is_array($check) => true,                                    // [Class, 'method'] — trusted as-is
            is_string($check) && class_exists($check) => true,           // invokable class — trusted as-is
            is_string($check) => method_exists($userModel, $check),      // plain method name — verifiable
            default => false,
        };

        if ($roleUsable || $checkUsable) {
            $this->components->twoColumnDetail('Admin management', '<fg=green>configured</>');

            return;
        }

        $this->add('error', 'Nobody can be recognised as an AccountFlow admin',
            sprintf(
                "Neither 'roles' nor 'check' resolves to anything real on %s. Every \"manage-*\" "
                .'action — creating transactions, transfers, loans, changing settings — will be '
                .'denied to every user, including yours. This produces no error and no log line; '
                .'it just fails every action.',
                class_basename($userModel),
            ),
            'Set accountflow.admin_management.roles to a role your user model actually has '
            ."(e.g. 'business', if you use spatie/laravel-permission), or set 'check' to a "
            .'method name that really exists on your user model.');
    }

    /**
     * Stored balance versus what the ledger says.
     *
     * Drift means something wrote a transaction without going through
     * TransactionService — which is exactly what the asset screen used to do.
     */
    private function checkBalanceDrift(): void
    {
        $drifted = [];

        foreach (Account::all() as $account) {
            $stored = round((float) $account->balance, 2);
            $ledger = round($account->calculatedBalance(), 2);

            if (abs($stored - $ledger) >= 0.01) {
                $drifted[] = [
                    $account->id,
                    $account->name,
                    number_format($stored, 2),
                    number_format($ledger, 2),
                    number_format($ledger - $stored, 2),
                ];
            }
        }

        if ($drifted === []) {
            $this->components->twoColumnDetail('Account balances', '<fg=green>match the ledger</>');

            return;
        }

        $this->add('warn', sprintf('%d account balance(s) disagree with the ledger', count($drifted)),
            'Something wrote transactions without updating the balance.',
            'php artisan accountflow:recalculate-balances');

        $this->table(['#', 'Account', 'Stored', 'Ledger', 'Difference'], array_slice($drifted, 0, $this->rows()));
    }

    /**
     * Transfers that never moved any money.
     */
    private function checkUnpostedTransfers(): void
    {
        $unposted = Transfer::query()
            ->whereNull('from_trx_id')
            ->orWhereNull('to_trx_id')
            ->get();

        if ($unposted->isEmpty()) {
            $this->components->twoColumnDetail('Transfers', '<fg=green>all posted to the ledger</>');

            return;
        }

        $this->add('error', sprintf('%d transfer(s) never moved any money', $unposted->count()),
            sprintf('Totalling %s. Before 0.3.0 a transfer wrote a row and no ledger entries.',
                number_format((float) $unposted->sum('amount'), 2)),
            'php artisan accountflow:backfill-transfers');

        $this->table(
            ['#', 'Ref', 'Amount', 'From', 'To', 'Date'],
            $unposted->take($this->rows())->map(fn (Transfer $t): array => [
                $t->id, $t->unique_id, number_format((float) $t->amount, 2),
                $t->from_account, $t->to_account, $t->date?->toDateString() ?? '—',
            ])->all(),
        );
    }

    /**
     * Loans recorded without any cash movement.
     */
    private function checkLoansWithoutPostings(): void
    {
        if (! Schema::hasTable('ac_loans')) {
            return;
        }

        $unposted = Loan::query()
            ->whereNotIn('id', LoanTransaction::query()->select('loan_id')->whereNotNull('loan_id'))
            ->get();

        if ($unposted->isEmpty()) {
            $this->components->twoColumnDetail('Loans', '<fg=green>all posted to the ledger</>');

            return;
        }

        $this->add('warn', sprintf('%d loan(s) have no ledger entry', $unposted->count()),
            sprintf('Totalling %s. Recording a loan used to move no money, so the cash never appeared. '
                .'These cannot be posted automatically — the account and date are unknown.',
                number_format((float) $unposted->sum('amount'), 2)),
            'Re-enter them, or post a matching transaction per loan.');

        $this->table(
            ['#', 'Name', 'Amount', 'Type', 'Date'],
            $unposted->take($this->rows())->map(fn (Loan $l): array => [
                $l->id, $l->name, number_format((float) $l->amount, 2),
                $l->type()?->label() ?? '—', $l->date?->toDateString() ?? '—',
            ])->all(),
        );
    }

    /**
     * Cash-moving equity movements with no ledger entry.
     */
    private function checkEquityWithoutPostings(): void
    {
        if (! Schema::hasTable('ac_equity_trx')) {
            return;
        }

        // Types 1 and 2 (contribution, withdrawal) move cash; 3 and 4 do not.
        $unposted = EquityTransaction::query()
            ->whereIn('type', [1, 2])
            ->whereNull('trx_id')
            ->get();

        if ($unposted->isEmpty()) {
            $this->components->twoColumnDetail('Equity movements', '<fg=green>all posted to the ledger</>');

            return;
        }

        $this->add('warn', sprintf('%d equity movement(s) have no ledger entry', $unposted->count()),
            sprintf('Totalling %s in contributions and withdrawals that never moved cash.',
                number_format((float) $unposted->sum('amount'), 2)),
            'Re-enter them, or post a matching transaction per movement.');
    }

    /**
     * Planned payments that should have posted and never did.
     */
    private function checkPlannedPayments(): void
    {
        if (! Schema::hasTable('ac_planned_payments')) {
            return;
        }

        $neverRan = PlannedPayment::query()
            ->where('auto_post', true)
            ->whereNull('last_run_date')
            ->get();

        $overdue = PlannedPayment::query()
            ->where('auto_post', true)
            ->whereNotNull('next_run_date')
            ->whereDate('next_run_date', '<', now()->toDateString())
            ->get();

        if ($neverRan->isEmpty() && $overdue->isEmpty()) {
            $this->components->twoColumnDetail('Planned payments', '<fg=green>up to date</>');

            return;
        }

        $this->add('warn',
            sprintf('%d planned payment(s) are due or have never run', $neverRan->count() + $overdue->count()),
            'Nothing ran the schedule before 0.3.0. Review the dates before posting — '
            .'a payment dated last year will post with that date.',
            'php artisan accountflow:post-planned-payments --dry-run');
    }

    /**
     * Wallet balances that do not reflect the transfers recorded against them.
     *
     * Nothing maintained `ac_user_wallets.balance` before 0.3.0, so any install
     * that used the module has balances stuck wherever they started.
     */
    private function checkWallets(): void
    {
        if (! Schema::hasTable('ac_user_wallets') || ! Schema::hasTable('ac_user_transfers')) {
            return;
        }

        $wallets = UserWallet::all();

        if ($wallets->isEmpty()) {
            return;
        }

        $suspect = $wallets->filter(function (UserWallet $wallet): bool {
            $movement = (float) UserTransfer::where('to', $wallet->user_id)->sum('amount')
                - (float) UserTransfer::where('from', $wallet->user_id)->sum('amount');

            // A zero balance next to real transfers means nothing ever applied them.
            return abs((float) $wallet->balance) < 0.01 && abs($movement) >= 0.01;
        });

        if ($suspect->isEmpty()) {
            $this->components->twoColumnDetail('Wallets', '<fg=green>balances look consistent</>');

            return;
        }

        $this->add('warn',
            sprintf('%d wallet(s) have transfers recorded but a zero balance', $suspect->count()),
            'Wallet balances were never maintained before 0.3.0. These cannot be rebuilt '
            .'automatically — a wallet balance also depends on top-ups and settlements that '
            .'were never recorded either.',
            'Set the opening balances by hand, then use the wallet screens from now on.');
    }

    /**
     * Ledger rows pointing at records that no longer exist.
     */
    private function checkOrphans(): void
    {
        $orphanAccounts = Transaction::query()
            ->whereNotNull('account_id')
            ->whereNotIn('account_id', Account::query()->select('id'))
            ->count();

        if ($orphanAccounts > 0) {
            $this->add('error', "{$orphanAccounts} transaction(s) reference a missing account",
                'These will not appear in any account balance.',
                'Reassign them, or restore the deleted accounts.');

            return;
        }

        $this->components->twoColumnDetail('Referential integrity', '<fg=green>no orphaned transactions</>');
    }

    private function add(string $level, string $title, string $detail, ?string $fix): void
    {
        $this->findings[] = compact('level', 'title', 'detail', 'fix');

        $this->newLine();

        $level === 'error'
            ? $this->components->error($title)
            : $this->components->warn($title);

        $this->line("  {$detail}");
    }

    private function report(): int
    {
        $this->newLine();

        if ($this->findings === []) {
            $this->components->info('No problems found.');
            $this->newLine();

            return self::SUCCESS;
        }

        $this->components->info('Suggested next steps, in order:');
        $this->newLine();

        $step = 1;

        foreach ($this->findings as $finding) {
            if ($finding['fix'] === null) {
                continue;
            }

            $this->line("  <fg=cyan>{$step}.</> {$finding['title']}");
            $this->line("     <comment>{$finding['fix']}</comment>");
            $step++;
        }

        $this->newLine();
        $this->line('  See <comment>docs/08-upgrading.md</comment> for the full runbook.');
        $this->newLine();

        // Findings are informational; a health check that fails the build on
        // pre-existing data would be unhelpful in CI.
        return self::SUCCESS;
    }

    private function rows(): int
    {
        try {
            return max(1, (int) $this->option('verbose-rows'));
        } catch (Throwable) {
            return 10;
        }
    }
}
