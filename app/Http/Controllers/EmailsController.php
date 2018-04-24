<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Email;
use App\Filters\EmailFilters;
use App\Responses\EmailResponse;
use App\Http\Resources\EmailResource;

class EmailsController extends ApiController
{
    public function index(EmailFilters $filters)
    {
        $limit = request()->input('limit') ?: 25;

        $emails = Email::with('inbox')
                        ->latest()
                        ->filter($filters)
                        ->paginate($limit);

        return EmailResource::collection($emails);
    }

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
