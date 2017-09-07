<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Email;
use PhpMimeMailParser\Parser;
use App\Transformers\EmailTransformer;
use App\Filters\EmailFilters;

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
    public function show($id)
    {
        $email = Email::find($id);

        if ( ! $email)
        {
            return $this->respondNotFound('Email does not exist.');
        }

        $acceptedHeaders = ['application/json', 'text/html', 'text/plain', 'message/rfc822'];

        if ('text/html' == request()->prefers($acceptedHeaders))
        {
            $parser = new Parser;
            $parser->setPath($email->fullPath());
            return response($parser->getMessageBody('html'))->header('Content-Type', 'text/html; charset=UTF-8');
        }
        elseif ('text/plain' == request()->prefers($acceptedHeaders))
        {
            $parser = new Parser;
            $parser->setPath($email->fullPath());
            return response($parser->getMessageBody('text'))->header('Content-Type', 'text/plain; charset=UTF-8');
        }
        elseif ('message/rfc822' == request()->prefers($acceptedHeaders))
        {;
            return response(file_get_contents($email->fullPath()))->header('Content-Type', 'message/rfc822; charset=UTF-8');
        }
        else
        {
            $data = $this->emailTransformer->transform($email);


            if ($email->isUnread())
            {
                $email->read();
            }

            return $this->respond(['data' => $data]);

        }
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