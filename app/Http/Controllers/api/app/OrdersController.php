<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $orders = $user->orders;

        return response([
            'message' => "user's orders loaded successfully",
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
    public function store(Plan $plan)
    {
        $inputs['status'] = 0;
        $user = Auth::user();

        $inputs['plan_id'] = $plan->id;
        $inputs['user_id'] = $user->id;
        $inputs['amount'] = $plan->price;
        $inputs['discount_amount'] = 0;
        $inputs['order_date'] = now();
        $inputs['total_amount'] = $inputs['amount'] - $inputs['discount_amount'];

        $order = Order::create($inputs);

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
    public function show(Order $order)
    {
        $user = Auth::user();
        if (!$this->validateUserOwnsOrder($user, $order)) {
            return response([
                'message' => 'there in no such order'
            ], 422);
        }

        $plan_name = $order->plan->name;

        return response([
            'message' => 'order loaded successfully',
            'order' => $order,
            'plan_name' => $plan_name
        ], 200);
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function checkAndApplyDiscountCode(Request $request, Order $order)
    {
        $user = Auth::user();
        if (!$this->validateUserOwnsOrder($user, $order) && $order->status != 0) {
            return response([
                'message' => 'there in no such order'
            ], 422);
        }

        $request->validate([
            'discount_code' => 'required|string'
        ]);

        $inputs = $request->only(['discount_code']);
        $plan = $order->plan;


        $inputs['amount'] = $plan->price;
        $inputs['discount_amount'] = 0;
        $has_discount = false;
        $response_message = "بدون کد تخفیف";

        if (!empty($order->discount_id)) {
            if ($order->discount->code === $request->discount_code) {
                return response([
                    'message' => 'discount has been applied before',
                    'order' => $order,
                    'response_message' => "کد تخفیف یکبار اعمال شده است",
                    'status' => true
                ], 200);
            } else {

                $old_discount = Discount::find($order->discount_id);

                if ($old_discount->user_id === $user->id) {
                    $old_discount->update(['status' => 0]);
                }
                $user->discounts()->detach($order->discount_id);
                $order->update([
                    'discount_id' => null,
                    'discount_code' => null,
                    'discount_amount' => 0,
                    'total_amount' => $order->amount,
                ]);
            }
        }

        if (!empty($inputs["discount_code"])) {
            $discount = Discount::where(['code' => $request->discount_code])->where('exp_date', '>', now())->first();
            if ($discount) {
                $is_discount_available_for_plan = true;

                if ($discount->plan_id !== $plan->id && !empty($discount->plan_id)) {
                    $response_message = "کد تخفیف برای این محصول قابل استفاده نمی باشد.";
                    $is_discount_available_for_plan = false;
                }

                if ($is_discount_available_for_plan) {
                    $is_discount_available_for_user = true;

                    if ($discount->user_id !== $user->id && !empty($discount->user_id)) {
                        $response_message = "کد تخفیف برای این کاربر قابل استفاده نمی باشد.";
                        $is_discount_available_for_user = false;
                    }

                    if ($is_discount_available_for_user) {
                        $user_discounts = $user->discounts;

                        foreach ($user_discounts as $user_discount) {
                            if ($user_discount->code === $discount->code) {

                                $response_message = "کد تخفیف برای این کاربر قابل استفاده نمی باشد.";
                                $is_discount_available_for_user = false;
                                break;
                            }
                        }

                        if ($is_discount_available_for_user) {
                            $inputs['discount_id'] = $discount->id;
                            $inputs['discount_amount'] = $inputs['amount'] * $discount->value / 100;
                            $response_message = "تخفیف با موفقیت اعمال شد.";
                            $has_discount = true;
                        }
                    }
                }
            } else {
                $response_message = "کد تخفیف نامعتبر";
            }
        }

        $inputs['total_amount'] = $inputs['amount'] - $inputs['discount_amount'];

        $result = $order->update($inputs);

        if ($has_discount) {
            $user->discounts()->attach($discount->id);
            $discount->status == 0 &&  $discount->update(['status' => 1]);
        }

        if ($result) {
            return response([
                'message' => 'order updated successfully',
                'order' => $order,
                'response_message' => $response_message,
                'status' => $has_discount
            ], 200);
        }
    }

    public function cancel(Order $order)
    {
        $user = Auth::user();
        if (!$this->validateUserOwnsOrder($user, $order) && $order->status != 0) {
            return response([
                'message' => 'there in no such order'
            ], 422);
        }

        if (!empty($order->discount_id)) {

            $discount = Discount::find($order->discount_id);

            if ($discount->user_id === $user->id) {
                $discount->update(['status' => 0]);
            }
            $user->discounts()->detach($order->discount_id);
        }

        $result = $order->update([
            'discount_id' => null,
            'discount_code' => null,
            'discount_amount' => 0,
            'total_amount' => $order->amount,
            'status' => 2
        ]);

        if ($result) {
            return response([
                'message' => 'order canceled successfully',
                'order' => $order
            ], 200);
        }
        
    }

    protected function validateUserOwnsOrder($user, $order)
    {
        if ($user->id === $order->user_id)
            return true;
        return false;
    }
}
