<?php

use ArtflowStudio\AccountFlow\Facades\Accountflow;

if (! function_exists('accountflow')) {
    /**
     * Get AccountFlow facade for accessing all services
     *
     * @return \ArtflowStudio\AccountFlow\App\Services\AccountFlowManager
     */
    function accountflow(): \ArtflowStudio\AccountFlow\App\Services\AccountFlowManager
    {
        return app('accountflow');
    }
}

if (! function_exists('transaction_service')) {
    /**
     * Get a TransactionService instance for method chaining
     *
     * @deprecated Use Accountflow::transactions() instead
     */
    function transaction_service()
    {
        return Accountflow::transactions();
    }
}

if (! function_exists('create_transaction')) {
    /**
     * Create a transaction with auto-filled defaults
     *
     * @param array $data Transaction data
     * @return \App\Models\AccountFlow\Transaction
     *
     * @deprecated Use Accountflow::transactions()->create() instead
     */
    function create_transaction(array $data)
    {
        return Accountflow::transactions()->create($data);
    }
}

if (! function_exists('create_income')) {
    /**
     * Create an income transaction
     *
     * @param array $data Transaction data
     * @return \App\Models\AccountFlow\Transaction
     *
     * @deprecated Use Accountflow::transactions()->createIncome() instead
     */
    function create_income(array $data)
    {
        return Accountflow::transactions()->createIncome($data);
    }
}

if (! function_exists('create_expense')) {
    /**
     * Create an expense transaction
     *
     * @param array $data Transaction data
     * @return \App\Models\AccountFlow\Transaction
     *
     * @deprecated Use Accountflow::transactions()->createExpense() instead
     */
    function create_expense(array $data)
    {
        return Accountflow::transactions()->createExpense($data);
    }
}
