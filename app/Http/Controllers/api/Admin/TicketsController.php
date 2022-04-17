<?php

namespace App\Http\Controllers\api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tickets = Ticket::whereNull('parent_id')->get();

        return response([
            'tickets loaded successfully',
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
    public function answer(Request $request, Ticket $ticket)
    {
        $request->validate([
            'body' => 'required|string|min:3|max:700',
            'parent_id' => 'required|numeric|exists:tickets,id'
        ]);

        $inputs = $request->only(['body', 'parent_id']);
        $inputs['user_id'] = $ticket->admin_id;
        $inputs['admin_id'] = $ticket->admin_id;
        $inputs['subject'] = $ticket->subject;
        $inputs['type'] = $ticket->type;
        $inputs['status'] = $ticket->status;
        $inputs['seen'] = 1;

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

        $ticket->seen == 0 && $ticket->update(['seen' => 1]);
        $ticket->children()->update(['seen' => 1]);

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
    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'body' => 'required|string|min:3|max:700'
        ]);

        $result = $ticket->update(['body' => $request->body]);
        response([
            'message' => 'answer updated successfully',
            'status' => $result,
            'answer' => $ticket
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ticket $ticket)
    {
    
        $result = DB::transaction(function () use ($ticket) {
            $result = $ticket->delete();
            $ticket->children()->delete();

            return $result;
        });
        response([
            'message' => 'answer deleted successfully',
            'status' => $result
        ], 200);
    }

    public function changeStatus(Ticket $ticket)
    {
        $status = $ticket->status == 0 ? 1 : 0;
        DB::transaction(function () use ($ticket, $status) {
            $ticket->update(['status' => $status]);
            $ticket->children()->update(['status' => $status]);
        });
        return response([
            'message' => "ticket's changed successfully",
            'status' => $status
        ], 200);
    }
}
