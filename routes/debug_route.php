<?php

use App\Models\SessionBilliard;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\Route;

Route::get('/debug-session', function () {
    $session = SessionBilliard::latest('id')->first();
    if (!$session) return 'No sessions found';

    $hasItemRelation = $session->transactionItem;
    $rawItem = TransactionItem::where('session_id', $session->id)->first();
    $doesntHaveCheck = SessionBilliard::where('id', $session->id)->whereDoesntHave('transactionItem')->exists();

    return response()->json([
        'session_id' => $session->id,
        'status' => $session->status,
        'has_relation_loaded' => $hasItemRelation ? 'YES' : 'NO',
        'raw_item_found' => $rawItem ? 'YES' : 'NO',
        'whereDoesntHave_says_it_is_orphaned' => $doesntHaveCheck ? 'YES' : 'NO',
        'raw_item' => $rawItem
    ]);
});
