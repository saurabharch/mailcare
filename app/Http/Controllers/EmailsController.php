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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Email $email, EmailResponse $emailResponse)
    {
        return $emailResponse->make($email);
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
