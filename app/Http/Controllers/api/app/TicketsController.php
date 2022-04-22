<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $tickets = Ticket::where(['user_id' => $user->id, 'parent_id' => null])->orderBy("created_at", 'desc')->get();

        return response([
            'message' => "tickets loaded successfully",
            'tickets' => $tickets
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
            'subject' => 'required|string|min:3|max:90',
            'body' => 'required|string|min:3|max:700',
            'parent_id' => 'nullable|numeric|exists:tickets,id',
            'type' => 'required|in:0,1,2,3,4'
        ]);
        $user = Auth::user();

        $inputs = $request->all();
        $inputs['user_id'] = $user->id;
        $admin = User::where('is_admin', 1)->first();
        $inputs['admin_id'] = $admin->id;
        $inputs['status'] = 0;
        $inputs['seen'] = 0;

        $ticket = Ticket::create($inputs);

        return response([
            'message' => 'ticket created successfully',
            'ticket' => $ticket
        ], 200);
    }

    public function answer(Request $request, Ticket $ticket)
    {
        $request->validate([
            'body' => 'required|string|min:3|max:700',
            'parent_id' => 'required|numeric|exists:tickets,id'
        ]);

        $inputs = $request->only(['body', 'parent_id']);
        $inputs['user_id'] = $ticket->user_id;
        $inputs['admin_id'] = $ticket->admin_id;
        $inputs['subject'] = $ticket->subject;
        $inputs['type'] = $ticket->type;
        $inputs['status'] = $ticket->status;
        $inputs['seen'] = 0;

        $answer = Ticket::create($inputs);
        return response([
            'message' => 'answer sent successfully',
            'answer' => $answer
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Ticket $ticket)
    {
        $children = $ticket->children;

        return response([
            'ticket loaded successfully',
            'ticket' => $ticket,
            'children' => $children
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
}
