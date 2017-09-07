<?php
namespace App\Responses;

use Illuminate\Http\Request;
use PhpMimeMailParser\Parser;
use App\Email;
use App\Transformers\EmailTransformer;

class EmailResponse
{
	protected $request, $parser;
	protected $acceptedHeaders = ['application/json', 'text/html', 'text/plain', 'message/rfc822'];

	public function __construct(Request $request, Parser $parser)
	{
		$this->request = $request;
		$this->parser = $parser;
	}

	public function make(Email $email)
	{
		$this->email = $email;

		if ($this->requestPrefer('text/html'))
		{
			return $this->makeHtml();
		}
		elseif ($this->requestPrefer('text/plain'))
		{
			return $this->makeText();
		}
		elseif ($this->requestPrefer('message/rfc822'))
		{
			return $this->makeRaw();
		}
		else 
		{
			return $this->makeJson();
		}
	}

	protected function requestPrefer($contentType)
	{
		return $contentType == $this->request->prefers($this->acceptedHeaders);
	}

	protected function makeHtml()
	{
        $this->parser->setPath($this->email->fullPath());

        return response($this->parser->getMessageBody('html'))->header('Content-Type', 'text/html; charset=UTF-8');
	}

	protected function makeText()
	{
        $this->parser->setPath($this->email->fullPath());

        return response($this->parser->getMessageBody('text'))->header('Content-Type', 'text/plain; charset=UTF-8');
	}

	protected function makeRaw()
	{
        return response(file_get_contents($this->email->fullPath()))->header('Content-Type', 'message/rfc822; charset=UTF-8');
	}

	protected function makeJson()
	{
        $data = (new EmailTransformer)->transform($this->email);


        if ($this->email->isUnread())
        {
            $this->email->read();
        }

        return response()->json(['data' => $data]);
	}
}