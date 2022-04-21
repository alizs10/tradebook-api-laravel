<?php

namespace App\Services;

use App\Models\Payment as ModelsPayment;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;

class PaymentServices
{

    protected $url = "http://localhost:3000/panel/payment/";

    public function payment($payment)
    {
        // Create new invoice.
        $invoice = (new Invoice)->amount($payment->amount);

        // Purchase the given invoice.
        return Payment::callbackUrl($this->url)->purchase(
            $invoice,
            function ($driver, $transactionId) use($payment) {
                // We can store $transactionId in database.
                $payment->update(['transaction_id' => $transactionId]);
            }
        )->pay()->toJson();
    }

    public function verifyPayment($transaction_id)
    {
        $payment = ModelsPayment::where(["transaction_id" => $transaction_id])->first();
        
        try {
            $receipt = Payment::amount($payment->amount)->transactionId($transaction_id)->verify();
            $payment->order()->update(['status' => 1]);
            $result = $payment->update([
                'status' => 1,
                'reference_id' => $receipt->getReferenceId(),
                'payment_date' => now()
            ]);

            if ($result) {
                return [true, $result];
            }

            return [false, $result];

        } catch (InvalidPaymentException $exception) {
            $result = $payment->update([
                'status' => 0,
                'payment_date' => now()
            ]);
            $payment->order()->update(['status' => 3]);
            return [false, $exception->getMessage()]; 
        }
    }
}
