<?php

use ArtflowStudio\AccountFlow\Services\AccountFlowManager;

if (! function_exists('accountflow')) {
    /**
     * Entry point to every AccountFlow service.
     *
     * @example
     * accountflow()->transactions()->income(1500, 'Invoice #221');
     * accountflow()->accounts()->getBalance($accountId);
     * accountflow()->reports()->profitAndLoss($from, $to);
     */
    function accountflow(): AccountFlowManager
    {
        return app('accountflow');
    }
}
