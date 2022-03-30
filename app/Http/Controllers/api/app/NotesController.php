<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notes = Auth::user()->notes;
        return response([
            'notes' => $notes
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
            'note' => 'required|string|min:1|max:500',
        ]);


        $inputs = $request->only(['note']);
        $inputs['user_id'] = Auth::user()->id;

        $note = Note::create($inputs);

        return response([
            'message' => 'note created successfully',
            'note' => $note

        ], 201);
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
    public function update(Request $request, Note $note)
    {
        $user = Auth::user();

        if (!$this->validateUserOwnsNote($user, $note)) {
            return response([
                'message' => 'there in no such note'
            ], 422);
        }

        $request->validate([
            'note' => 'required|string|min:1|max:500',
        ]);

        $inputs = $request->only(['note']);
        $result = $note->update($inputs);

        if ($result) {
            return response([
                'message' => 'note updated successfully',
                'note' => $note,
                'status' => $result

            ], 200);
        }

        return response([
            'message' => 'something went wrong, try again',
            'status' => $result

        ], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Note $note)
    {
        $user = Auth::user();

        if (!$this->validateUserOwnsNote($user, $note)) {
            return response([
                'message' => 'there in no such note'
            ], 422);
        }

        $result = $note->delete();

        if ($result) {
            return response([
                'message' => 'note deleted successfully',
                'status' => $result
            ], 200);
        }

        return response([
            'message' => 'something went wrong, try again',
            'status' => $result

        ], 500);
    }

    protected function validateUserOwnsNote($user, $note)
    {
        if ($user->id === $note->user_id)
            return true;
        return false;
    }
}
