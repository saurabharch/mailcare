<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpMimeMailParser\Parser;
use App\Email;
use App\Attachment;

class AttachmentsController extends ApiController
{

    public function show($emailId, $attachmentId)
    {

        $email = Email::find($emailId);

        if ( ! $email)
        {
            return $this->respondNotFound('Email does not exist.');
        }

        $attachment = $email->attachments->find($attachmentId);

        if ( ! $attachment)
        {
            return $this->respondNotFound('Attachment does not exist.');
        }

        $parser = new Parser;
        $parser->setPath($email->fullPath());

        foreach($parser->getAttachments() as $attachmentParsed)
        {
        	if ($attachment->headers_hashed === $attachment->hashHeaders($attachmentParsed->getHeaders()))
        	{
            	$data = stream_get_contents($attachmentParsed->getStream());
            	return response($data)->header('Content-Type', $attachmentParsed->getContentType());
        	}
        }

        return $this->respondNotFound('Attachment not found.');
    }
}
