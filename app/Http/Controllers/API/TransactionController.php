<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $status = $request->input('status');

        if ($id) {
            $transaction = Transaction::find($id)->with(['items.product']);

            if ($transaction) {
                return ResponseFormatter::success($transaction, 'Data list transaksi berhasil didapatkan');
            }
            return ResponseFormatter::error(null, 'Data transaksi tidak ada', 404);
        }

        $transaction = Transaction::where('users_id', Auth::user()->id)->with(['items.product']);

        if ($status) {
            $transaction->where('status', $status);
        }

        return ResponseFormatter::success($transaction->paginate($limit), 'Data list transaksi berhasil didapatkan');
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'exist:products,id',
            'total_price'=> 'required',
            'shipping_price' => 'required',
            'status' => 'required|in:PENDING,SUCCESS,CANCELLED,FAILED,SHIPPING,SHIPPED'
        ]);

        $transaction = Transaction::create([
            'users_id' => Auth::user()->id,
            'address' => $request->address,
            'total_price' => $request->total_price,
            'shipping_price' => $request->shipping_price,
            'status' => $request->status,
        ]);

        foreach ($transaction as $product) {
            TransactionItem::create([
                'users_id' => Auth::user()->id,
                'products_id' => $product['id'],
                'transactions_id' => $transaction->id,
                'quantity' => $product['quantity']
            ]);
        }

        return ResponseFormatter::success($transaction->load(['items.product']),'Transaksi Berhasil');
    }
}
