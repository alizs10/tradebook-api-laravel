<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $discounts = Discount::all();

        return response([
            'discounts loaded successfully',
            'discounts' => $discounts
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
            'user_id' => 'nullable|numeric|exists:users,id',
            'plan_id ' => 'nullable|numeric|exists:plans,id',
            'value' => 'required|numeric|min:0|max:100',
            'code' => 'required|string|max:20|min:2',
            'status' => 'required|in:0,1',
            'exp_date' => 'nullable|date',
        ]);

        $inputs = $request->all();

        $discount = Discount::create($inputs);

        if ($discount) {
            return response([
                'new discount created successfully',
                'discount' => $discount
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
    public function update(Request $request, Discount $discount)
    {
        $request->validate([
            'user_id' => 'nullable|numeric|exists:users,id',
            'plan_id ' => 'nullable|numeric|exists:plans,id',
            'value' => 'required|numeric|min:0|max:100',
            'code' => 'required|string|max:20|min:2',
            'status' => 'required|in:0,1',
            'exp_date' => 'nullable|date'
        ]);
        $inputs = $request->all();

        $result = $discount->update($inputs);

        if($result)
        {
            return response([
                'message' => 'discount updated successfully',
                'discount' => $discount
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Discount $discount)
    {
        $result = $discount->delete();

        if($result)
        {
            return response([
                'message' => 'discount deleted successfully'
            ], 200);
        }
    }
}
