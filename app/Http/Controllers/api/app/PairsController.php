<?php

namespace App\Http\Controllers\api\app;

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
    public function cryptoPairs()
    {
        $pairs = Pair::where('type', 0)->orderBy('name')->get();

        $data = [];
        foreach ($pairs as $pair) {
            array_push($data, array(
                'value' => $pair['name'],
                'label' => str_replace("/", "", $pair['name']),
                'id' => $pair['id']
            ));
        }

        return response([
            'pairs' => $data
        ], 200);
    }

    public function forexPairs()
    {
        $pairs = Pair::where('type', 1)->orderBy('name')->get();

        $data = [];
        foreach ($pairs as $pair) {
            array_push($data, array(
                'value' => $pair['name'],
                'label' => str_replace("/", "", $pair['name']),
                'id' => $pair['id']
            ));
        }

        return response([
            'pairs' => $data
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
        //
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
}
