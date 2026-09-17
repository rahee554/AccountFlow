<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::equity.transactions') }}" wire:navigate>Equity Transactions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>Add Equity Transaction</h1></div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('accountflow::equity.transactions') }}" class="btn btn-soft-secondary" wire:navigate>
                <i data-lucide="arrow-left"></i> Back to Transactions
            </a>
        </div>
    </div>
</div>
