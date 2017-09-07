<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Email;
use App\Transformers\EmailTransformer;
use App\Filters\EmailFilters;
use App\Responses\EmailResponse;

class EmailsController extends ApiController
{
    protected $emailTransformer;

    public function __construct(EmailTransformer $emailTransformer)
    {
        $this->emailTransformer = $emailTransformer;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(EmailFilters $filters)
    {
        $limit = request()->input('limit') ?: 25;

        $emails = Email::with('inbox')
                        ->latest()
                        ->filter($filters)
                        ->paginate($limit);

        return $this->respondWithPagination($emails, [
            'data' => $this->emailTransformer->transformCollection($emails->all()),
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, EmailResponse $emailResponse)
    {

        $email = Email::findOrFail($id);

        return $emailResponse->make($email);
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

    public function favorite(Email $email)
    {
        if (!$email->favorite) {
            $email->favorite = true;
            $email->save();
        }
    }

    public function unfavorite(Email $email)
    {
        if ($email->favorite) {
            $email->favorite = false;
            $email->save();
        }
    }
}