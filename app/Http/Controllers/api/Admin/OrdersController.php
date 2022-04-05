<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::all();

        return response([
            'message' => 'orders loaded successfully',
            'orders' => $orders
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
            'user_id' => 'required|numeric|exists:users,id',
            'plan_id ' => 'nullable|numeric|exists:plans,id',
            'discount_id ' => 'nullable|numeric|exists:discounts,id',
            'amount' => 'required|numeric',
            'discount_amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'order_date' => 'required|date',
            'status' => 'required|in:0,1'
        ]);

        $inputs = $request->all();

        $order = Order::create($inputs);

        if($order)
        {
            return response([
                'message' => 'order created successfully',
                'order' => $order
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
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'user_id' => 'required|numeric|exists:users,id',
            'plan_id ' => 'nullable|numeric|exists:plans,id',
            'discount_id ' => 'nullable|numeric|exists:discounts,id',
            'amount' => 'required|numeric',
            'discount_amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'order_date' => 'required|date',
            'status' => 'required|in:0,1'
        ]);

        $inputs = $request->all();

        $result = $order->update($inputs);

        if($result)
        {
            return response([
                'message' => 'order updated successfully',
                'order' => $order
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        $result = $order->delete();

        if ($result) {
            return response([
                'message' => 'order deleted successfully'
            ], 200);
        }
    }
}
