<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TablesActionsController extends Controller
{
    //

    public function deleteTransaction($id)
    {
        // Assuming you have a Transaction model
        $transaction = \App\Models\Transaction::find($id);

        if ($transaction) {
            // Check other tables for references to this transaction ID
            $relatedTables = [
                \App\Models\RelatedTable1::class,
                \App\Models\RelatedTable2::class,
                // Add other related tables here
            ];

            foreach ($relatedTables as $table) {
                $relatedRecords = $table::where('transaction_id', $id)->get();

                foreach ($relatedRecords as $record) {
                    // Adjust or delete related records as needed
                    $record->update(['transaction_id' => null]); // Example adjustment
                }
            }

            // Delete the transaction
            $transaction->delete();
            return response()->json(['message' => 'Transaction and related records adjusted successfully.']);
        }

        return response()->json(['message' => 'Transaction not found.'], 404);
    }
}
