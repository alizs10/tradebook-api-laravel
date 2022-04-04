<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlansController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $plans = Plan::all();

        return response([
            'plans' => $plans
        ]);
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
            'name' => 'required|string|min:3|max:90',
            'valid_for' => 'required|numeric',
            'price' => 'required|numeric'
        ]);

        $inputs = $request->all();

        $plan = Plan::create($inputs);

        if ($plan) {
            return response([
                'message' => 'plan created successfully',
                'plan' => $plan
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
    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:90',
            'valid_for' => 'required|numeric',
            'price' => 'required|numeric'
        ]);

        $inputs = $request->all();

        $result = $plan->update($inputs);

        if ($result) {
            return response([
                'message' => 'plan updated successfully',
                'plan' => $plan
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Plan $plan)
    {
        $result = $plan->delete();
        if ($result) {
            return response([
                'message' => 'plan deleted successfully'
            ], 200);
        }

    }
}
