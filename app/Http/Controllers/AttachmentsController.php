<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpMimeMailParser\Parser;
use App\Email;
use App\Attachment;

class AttachmentsController extends ApiController
{

    public function show(Email $email, $attachmentId)
    {
        $attachment = $email->attachments()->findOrFail($attachmentId);

        $parser = new Parser;
        $parser->setPath($email->fullPath());

        foreach ($parser->getAttachments() as $attachmentParsed) {
            if ($attachment->headers_hashed === $attachment->hashHeaders($attachmentParsed->getHeaders())) {
                $data = stream_get_contents($attachmentParsed->getStream());
                return response($data)->header('Content-Type', $attachmentParsed->getContentType());
            }
        }

        return $this->respondNotFound('Attachment not found.');
    }
}
