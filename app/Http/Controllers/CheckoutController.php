<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Cart;
use App\Transaction;
use App\TransactionDetail;

use Exception;

use Midtrans\Snap;
use Midtrans\Config;

class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        # Save User Table
        $user = Auth::user();
        $user->update($request->except('total_price'));
        
        # Proses checkout
        $code = 'STORE-' . mt_rand(0000,9999);
        $carts = Cart::with(['product','user'])
                        ->where('users_id', Auth::user()->id)
                        ->get();

        $transaction = Transaction::create([
            'users_id' => Auth::user()->id,
            'inscurance_price' => 0,
            'shipping_price' => 0,
            'total_price' => $request->total_price,
            'transaction_status' => 'PENDING',
            'code' => $code,
        ]);

        foreach ($carts as $cart) {
            $trx = 'TRX-' . mt_rand(0000,9999);
    
            TransactionDetail::create([
                'transactions_id' => $transaction->id,
                'products_id' => $cart->product->id,
                'price' => $cart->product->price,
                'shipping_status' => 'PENDING',
                'resi' => '',
                'code' => $trx,
            ]);
        }
        
        // Delete cart data
        Cart::with(['product','user'])
                ->where('users_id', Auth::user()->id)
                ->delete();
                
        // Set your Merchant Server Key
        Config::$serverKey = "SB-Mid-server-aS7D8nNnd2jiJeU7arssQpze";
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        Config::$isProduction = false;
        // Set sanitization on (default)
        Config::$isSanitized = true;
        // Set 3DS transaction for credit card to true
        Config::$is3ds = true;

        
        // Delete cart data
        // Cart::with(['product','user'])
        //         ->where('users_id', Auth::user()->id)
        //         ->delete();

        # Buat array untuk dikirim ke midtrans
         $midtrans = [
            'transaction_details' => [
                'order_id' => $code,
                'gross_amount' => (int) $request->total_price,
            ],

            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],

            'enable_payment' => [
                'gopay', 'permata_va', 'bank_transfer'
            ],

            'vtweb' => []
         ];

         try {
            // Get Snap Payment Page URL
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;
            
            // Redirect to Snap Payment Page
            return redirect($paymentUrl);
          }
            catch (Exception $e) {
                echo $e->getMessage();
          }

    }

    public function callback(Request $request)
    {
        # code...
    }
}
