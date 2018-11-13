<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Automation;
use Illuminate\Support\Str;
use App\Http\Resources\AutomationResource;

class AutomationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return AutomationResource::collection(Automation::all());
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
            'title' => ['required'],
            'has_attachment' => ['boolean'],
            'action_url' => ['required', 'url'],
        ]);

        Automation::create([
            'id' => (string) Str::uuid(),
            'title' => $request->input('title'),
            'sender' => $request->input('sender'),
            'inbox' => $request->input('inbox'),
            'subject' => $request->input('subject'),
            'has_attachments' => $request->input('has_attachments'),
            'action_url' => $request->input('action_url'),
            'action_secret_token' => $request->input('action_secret_token'),
        ]);
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
    public function update(Request $request, Automation $automation)
    {
        $request->validate([
            'title' => ['required'],
            'has_attachment' => ['boolean'],
            'action_url' => ['required', 'url'],
        ]);

        $automation->update([
            'title' => $request->input('title'),
            'sender' => $request->input('sender'),
            'inbox' => $request->input('inbox'),
            'subject' => $request->input('subject'),
            'has_attachments' => $request->input('has_attachments'),
            'action_url' => $request->input('action_url'),
            'action_secret_token' => $request->input('action_secret_token'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Automation $automation)
    {
        $automation->delete();
    }
}
