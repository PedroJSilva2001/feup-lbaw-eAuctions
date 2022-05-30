<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\ExpressCheckout;
use Carbon\Carbon;

use App\Models\Transaction;

class PayPalPaymentController extends Controller
{
    /**
     * Handles a payment.
     *
     * @param int $id
     * @param double $amount
     * @return Response
     */
    public function handlePayment($id, $amount)
    {

        $product = [];
        $product['items'] = [
            [
                'name' => 'Add Credit.',
                'price' => $amount,
                'desc'  => 'Add Credit to Account.',
                'qty' => 1
            ]
        ];
  
        $product['invoice_id'] = $id;
        $product['invoice_description'] = "Add Credit #{$product['invoice_id']} Bill";
        $product['return_url'] = url('/payment-success/'. $id .'/'. $amount); 
        $product['cancel_url'] = url('/users/'. $id); 
        $product['total'] = $amount;
  
        $paypalModule = new ExpressCheckout;
  
        $res = $paypalModule->setExpressCheckout($product);
        $res = $paypalModule->setExpressCheckout($product, true);
  
        return redirect($res['paypal_link']);
    }
  
    /**
     * Adds a transaction to the database.
     * 
     * @param Request $request
     * @param int $id
     * @param double $amount
     * @return Response
     */
    public function paymentSuccess(Request $request, $id, $amount)
    {
        $paypalModule = new ExpressCheckout;
        $response = $paypalModule->getExpressCheckoutDetails($request->token);
  
        if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {
            $transaction = new Transaction([
                'user_id'            => $id,
                'value'              => $amount,
                'date'               => Carbon::now(),
                'description'        => 'Add Credit to ',
                'method'             => 'PayPal',
                'status'             => 'Accepted',
            ]);
            $transaction->save();

            return redirect('/users/'. $transaction->user_id);
        }
        
        return redirect()->back();
    }
}