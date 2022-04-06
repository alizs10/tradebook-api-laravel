<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'plan_id' => 'required|numeric|exists:plans,id',
            'discount_code' => 'nullable|string|max:20',
            'order_date' => 'required|date',
            'status' => 'required|in:0,1'
        ]);
        $inputs = $request->all();

        $user = User::find($request->user_id);
        $plan = Plan::find($request->plan_id);

        $inputs['amount'] = $plan->price;
        $inputs['discount_amount'] = 0;
        $has_discount = false;

        if (!empty($request->discount_code)) {
            $discount = Discount::where(['code' => $request->discount_code, 'status' => 0])->first();
            if ($discount) {
                $is_discount_available_for_plan = true;

                if ($discount->plan_id !== $plan->id) {
                    $is_discount_available_for_plan = false;
                }

                if ($is_discount_available_for_plan) {
                    $is_discount_available_for_user = true;

                    if ($discount->user_id !== $user->id) {
                        $is_discount_available_for_user = false;
                    }

                    if ($is_discount_available_for_user) {
                        $user_discounts = $user->discounts;

                        foreach ($user_discounts as $user_discount) {
                            if ($user_discount->code === $discount->code) {
                                $is_discount_available_for_user = false;
                                break;
                            }
                        }

                        if ($is_discount_available_for_user) {
                            $inputs['discount_id'] = $discount->id;
                            $inputs['discount_amount'] = $inputs['amount'] * $discount->value / 100;
                            $has_discount = true;
                        }
                    }
                }
            }
        }

        $inputs['total_amount'] = $inputs['amount'] - $inputs['discount_amount'];

        $order = Order::create($inputs);

        if ($has_discount) {
            $user->discounts()->attach($discount->id);
            $discount->update(['status' => 1]);
        }

        if ($order) {
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

        if ($result) {
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

        $result = DB::transaction(function () use ($order) {
            if ($order->discount_id) {
                $discount = Discount::find($order->discount_id);
                $user = User::find($order->user_id);
                if ($discount->user_id === $user->id) {
                    $discount->update(['status' => 0]);
                }
                $user->discounts()->detach($order->discount_id);
            }
            return $order->delete();
        });


        if ($result) {
            return response([
                'message' => 'order deleted successfully'
            ], 200);
        }
    }
}
