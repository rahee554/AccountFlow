<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        // * --------------- Accounts ---------------*//
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true)->comment('false = Inactive, true = Active');
            $table->decimal('opening_balance', 10, 2)->default(0.00);
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->timestamps(0);
        });
        // * --------------- Payment Methods ---------------*//
        Schema::create('ac_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('info')->comment('WYSIWYG Data of the Payment Method')->nullable();
            $table->string('logo_icon')->nullable();
            $table->foreignId('account_id')->nullable()->constrained('accounts');
            $table->tinyInteger('status')->default(1)->comment('1 = active, 2=inactive');
            $table->timestamps(0);
        });

        // * --------------- Categories ---------------*//
        Schema::create('ac_categories', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('type')->nullable()->comment('1 = Income, 2 = Expense');
            $table->string('name')->nullable();
            $table->integer('parent_id')->nullable()->comment('Its id from account_categories table. If parent_id is null, then it is the main category, else it is a sub-category');
            $table->tinyInteger('privacy')->default(1)->comment('1 = locked, 2 = unlocked');
            $table->string('icon')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0 = Inactive, 1 = Active');
            $table->integer('added_by')->nullable();
            $table->timestamps(0);
        });

        // * --------------- Transactions ---------------*//

        Schema::create('ac_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->index();
            $table->foreignId('category_id')->constrained('ac_categories');
            $table->string('unique_id', 30)->unique(); // Add unique constraint here
            $table->integer('invoice_id')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->date('date')->nullable()->index();
            $table->longText('description')->nullable();
            $table->foreignId('payment_method')->nullable()->constrained('ac_payment_methods');
            $table->tinyInteger('type')->default(1)->comment('1 = Income, 2 = Expense');
            $table->foreignId('added_by')->nullable()->constrained('users');
            $table->timestamps(0);
        });

        // * --------------- Equity Partners ---------------*//
        Schema::create('ac_equity_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Partner full name
            $table->string('email')->nullable(); // Email contact
            $table->string('phone')->nullable(); // Phone contact
            $table->string('address')->nullable(); // Physical address
            $table->string('national_id')->nullable(); // CNIC / Passport / National ID
            $table->string('company')->nullable(); // Company name if applicable
            $table->decimal('ownership_percentage', 5, 2)->nullable()->comment('Fixed share % if agreed, else null');
            $table->decimal('current_equity', 15, 2)->default(0.00)->comment('Running balance of equity');
            $table->date('joined_at')->nullable()->comment('Date when partner joined');
            $table->date('left_at')->nullable()->comment('Date when partner exited');
            $table->boolean('is_active')->default(true)->comment('1 = Active, 0 = Inactive');
            $table->text('notes')->nullable(); // Extra remarks about partner
            $table->timestamps();
        });

        // * --------------- Equity Transactions ---------------*//
        Schema::create('ac_equity_trx', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')
                ->nullable()
                ->constrained('ac_equity_partners')
                ->onDelete('cascade'); // Related partner
            $table->foreignId('trx_id')
                ->nullable()
                ->constrained('ac_transactions')
                ->onDelete('cascade'); // Linked to main accounting transactions
            $table->tinyInteger('type')->comment('1 = Contribution, 2 = Withdrawal, 3 = Profit Share, 4 = Loss Share');
            $table->decimal('amount', 15, 2)->comment('Equity amount change');
            $table->string('description')->nullable()->comment('Reason or note for this transaction');
            $table->timestamps();
        });

        // * --------------- Assets ---------------*//

        Schema::create('ac_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('value', 10, 2)->default(0.00);
            $table->foreignId('category_id')->nullable()->constrained('ac_categories');
            $table->tinyInteger('status')->default(1)->comment('1 = Operating / Active, 2 = Not Operating / InActive, 3 = SoldOut');
            $table->date('acquisition_date');
            $table->timestamps(0);
        });
        Schema::create('ac_assets_trx', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique();
            $table->foreignId('asset_id')->nullable()->constrained('ac_assets');
            $table->foreignId('trx_id')->nullable()->constrained('ac_transactions');
            $table->timestamps(0);
        });

        // * --------------- Planned Payments ---------------*//
        Schema::create('ac_planned_payments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('ac_categories')->nullOnDelete();
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2)->default(0.00);

            $table->date('start_date')->nullable()->comment('first due date or start of schedule');
            $table->date('end_date')->nullable()->comment('optional, end of recurrence');

            $table->enum('schedule_type', ['once', 'daily', 'weekly', 'monthly', 'quarterly', 'half_yearly', 'yearly'])->default('once');
            $table->json('weekly_days')->nullable()->comment('array of weekdays for weekly schedule');
            $table->tinyInteger('monthly_day')->nullable()->comment('day of month 1–31');

            $table->boolean('auto_post')->default(true)->comment('true = auto post, false = manual');
            $table->date('last_run_date')->nullable()->comment('when the system auto-post last time');
            $table->date('next_run_date')->nullable()->comment('when the system should auto-post next');
            $table->timestamps(0);
        });

        // * --------------- Planned Payments Transactions ---------------*//
        Schema::create('ac_planned_payment_trx', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planned_payment_id')->constrained('ac_planned_payments')->cascadeOnDelete();
            $table->foreignId('trx_id')->constrained('ac_transactions')->cascadeOnDelete();
            $table->timestamps(0);
        });

        // * --------------- Purchases ---------------*//
        Schema::create('ac_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->tinyInteger('type')->default(1)->comment('1 = Advance, 2 = Installments');
            $table->integer('installments')->nullable();
            $table->integer('repayment')->nullable()->comment('1 = monthly, 2 = quarterly, 3=halfYearly, 4 = Annualy');
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->tinyInteger('status')->nullable()->comment('1 = piad, 2 = Partially paid, 3 = Not Paid');
            $table->date('date');
            $table->foreignId('category_id')->nullable()->constrained('ac_categories');
            $table->timestamps(0);
        });
        Schema::create('ac_purchase_trx', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique();
            $table->foreignId('purchase_id')->nullable()->constrained('ac_purchases');
            $table->foreignId('trx_id')->nullable()->constrained('ac_transactions');
            $table->timestamps(0);
        });
        // * --------------- Settings ---------------*//
        Schema::create('ac_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('key')->unique();
            $table->string('value');
            $table->tinyInteger('type')->default(1)->comment('1 = Feature, 2 = Value, 3 = permission')->nullable();
            $table->timestamps(0);
        });
        // * --------------- Loans ---------------*//
        Schema::create('ac_loan_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact');
            $table->string('cnic');
            $table->string('company')->nullable();
            $table->text('note')->nullable();
            $table->timestamps(0);
        });

        Schema::create('ac_loans', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id', 10)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->tinyInteger('loan_type')->comment('1 = Lended, 2 = Borrowed');
            $table->unsignedBigInteger('loan_partner_id');
            $table->tinyInteger('roi')->nullable();
            $table->tinyInteger('installments')->nullable()->comment('Number of Installments');
            $table->tinyInteger('installment_type')->nullable()->comment('1 = Monthly, 2 = Quarterly, 3 = Half Yearly, 4 = Annually');
            $table->tinyInteger('status')->nullable()->comment('1 = Returned, 2 = Partially Returned, 3 = Not Returned');
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->timestamps(0);
        });

        Schema::create('ac_loan_trx', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique();
            $table->foreignId('loan_id')->nullable()->constrained('ac_loans');
            $table->foreignId('trx_id')->nullable()->constrained('ac_transactions');
            $table->timestamps(0);
        });
        // * --------------- Transfers ---------------*//
        Schema::create('ac_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id', 10)->unique();

            $table->decimal('amount', 10, 2)->default(0.00);
            // Foreign key references to the 'users' table
            $table->unsignedBigInteger('from_account');
            $table->foreign('from_account')->references('id')->on('accounts');

            // Foreign key references to the 'users' table
            $table->unsignedBigInteger('to_account');
            $table->foreign('to_account')->references('id')->on('accounts');

            $table->text('description')->nullable();
            $table->date('date');

            // Foreign key references to the 'users' table
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');

            $table->timestamps(0);
        });
        // * --------------- User Wallets and Transfers ---------------*//
        Schema::create('ac_user_wallets', function (Blueprint $table) {
            $table->id();

            $table->decimal('balance', 10, 2)->default(0.00);
            // Foreign key references to the 'users' table
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->tinyInteger('status')->nullable()->comment('1 = Active, 2 = Freezed');

            $table->timestamps(0);
        });
        Schema::create('ac_user_transfers', function (Blueprint $table) {
            $table->id();

            $table->decimal('amount', 10, 2)->default(0.00);
            // Foreign key references to the 'users' table
            $table->unsignedBigInteger('from');

            $table->unsignedBigInteger('to');

            $table->foreign('from')->references('id')->on('users');
            $table->foreign('to')->references('id')->on('users');
            $table->date('date');
            $table->timestamps(0);
        });

        // * --------------- Budgets ---------------*//
        Schema::create('ac_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()->constrained('accounts');
            $table->foreignId('category_id')->nullable()->constrained('ac_categories');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->string('period')->default('monthly')->comment('monthly, yearly');
            $table->year('year')->nullable();
            $table->tinyInteger('month')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps(0);
        });

        // * --------------- Audit Trail ---------------*//
        Schema::create('ac_audit_trail', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('action');
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->timestamps(0);
        });

        Schema::create('ac_trx_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Template name, e.g. Office Rent, Monthly Salary');
            $table->foreignId('account_id')->nullable()->constrained('accounts')->comment('Default account for the transaction');
            $table->foreignId('category_id')->nullable()->constrained('ac_categories')->comment('Default category');
            $table->decimal('amount', 15, 2)->nullable()->comment('Default amount');
            $table->foreignId('payment_method')->nullable()->constrained('ac_payment_methods')->comment('Default payment method');
            $table->tinyInteger('type')->default(1)->comment('1 = Income, 2 = Expense');
            $table->text('description')->nullable()->comment('Default description');
            $table->json('meta')->nullable()->comment('For any additional fields or preferences');
            $table->foreignId('created_by')->nullable()->constrained('users')->comment('User who created the template');
            $table->boolean('active')->default(true)->comment('Template can be enabled/disabled');
            $table->timestamps(0);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ac_trx_templates');
        Schema::dropIfExists('ac_audit_trail');
        Schema::dropIfExists('ac_budgets');
        Schema::dropIfExists('ac_user_transfers');
        Schema::dropIfExists('ac_user_wallets');
        Schema::dropIfExists('ac_transfers');
        Schema::dropIfExists('ac_loan_trx');
        Schema::dropIfExists('ac_loans');
        Schema::dropIfExists('ac_loan_partners');
        Schema::dropIfExists('ac_purchase_trx');
        Schema::dropIfExists('ac_purchases');
        Schema::dropIfExists('ac_planned_payments_trx');
        Schema::dropIfExists('ac_planned_payments');
        Schema::dropIfExists('ac_assets_trx');
        Schema::dropIfExists('ac_assets');
        Schema::dropIfExists('ac_equity_trx');
        Schema::dropIfExists('ac_equity_partners');
        Schema::dropIfExists('ac_transactions');
        Schema::dropIfExists('ac_categories');
        Schema::dropIfExists('ac_payment_methods');
        Schema::dropIfExists('accounts');
    }
};
