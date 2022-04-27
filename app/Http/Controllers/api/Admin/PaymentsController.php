<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PlansServices;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $payments = Payment::orderBy("payment_date", "desc")->get();

        return response([
            'message' => 'payments loaded successfully',
            'payments' => $payments
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'nullable|numeric|exists:orders,id',
            'transaction_id' => 'nullable|string',
            'payment_date' => 'required|date',
            'status' => 'required|in:0,1',
            'type' => 'required|in:0,1',
        ]);

        $inputs = $request->all();
        $order = Order::find($request->order_id);
        $plan = $order->plan;
        $user = $order->user;
        $inputs['user_id'] = $user->id;
        $inputs['amount'] = $order->total_amount;

        if ($inputs['status'] == 1) {
            $order->update(['status' => 1]);

            // we should charge user's account
            $planServices = new PlansServices;
            $planServices->giveUser($user->id, $plan->id);

        } else {
            $order->update(['status' => 3]);
        }


        $payment = Payment::create($inputs);

        if ($payment) {
            return response([
                'message' => 'payment created successfully',
                'payment' => $payment
            ], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'user_id' => 'required|numeric|exists:users,id',
            'order_id' => 'nullable|numeric|exists:orders,id',
            'transaction_id' => 'nullable',
            'bank_first_response' => 'nullable',
            'bank_second_response' => 'nullable',
            'amount' => 'required|numeric',
            'payment_date' => 'required|date',
            'status' => 'required|in:0,1',
            'type' => 'required|in:0,1',
        ]);

        $inputs = $request->all();

        $result = $payment->update($inputs);

        if ($result) {
            return response([
                'message' => 'payment updated successfully',
                'payment' => $payment
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payment $payment)
    {
        $result = $payment->delete();

        if ($result) {
            return response([
                'message' => 'payment deleted successfully'
            ], 200);
        }
    }
}
