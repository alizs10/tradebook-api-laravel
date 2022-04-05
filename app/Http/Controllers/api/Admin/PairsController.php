<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pair;
use Illuminate\Http\Request;

class PairsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pairs = Pair::all();

        return response([
            'pairs loaded successfully',
            'pairs' => $pairs
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
            'name' => 'required|string|min:3|max:20|uniqure:pairs,name',
            'type' => 'required|in:0,1',
            'status' => 'required|in:0,1',
        ]);

        $inputs = $request->all();

        $pair = Pair::create($inputs);

        if ($pair) {
            return response([
                'new pair created successfully',
                'pair' => $pair
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
    public function update(Request $request, Pair $pair)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:20|uniqure:pairs,name',
            'type' => 'required|in:0,1',
            'status' => 'required|in:0,1',
        ]);

        $inputs = $request->all();

        $result = $pair->update($inputs);

        if ($result) {
            return response([
                'pair updated successfully',
                'pair' => $pair
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pair $pair)
    {
        $result = $pair->delete();

        if ($result) {
            return response([
                'pair deleted successfully',
                'pair' => $pair
            ], 200);
        }
    }

}
