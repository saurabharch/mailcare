<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Email;
use App\Inbox;
use App\Attachment;
use App\Sender;
use \PhpMimeMailParser\Parser;
use Illuminate\Support\Facades\Storage;

class ReceiveEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:receive {file=stream : The file of the email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Receive email from mail server to integrate to database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Receiving email ...");

        $parser = new Parser;

        $file = $this->argument('file');

        if ($file == 'stream')
        {
            $this->info("from stream");
            $parser->setStream(fopen("php://stdin", "r"));
        }
        else
        {
            $this->info("from path $file");
            $parser->setPath($file); 
        }
        
        $sender = Sender::updateOrCreate(
            [
                'email' => $parser->getAddresses('from')[0]["address"]
            ],
            [
                'display_name' => $parser->getAddresses('from')[0]["display"],
                'local_part' => '',
                'domain' => '',
            ]
        );
        
        $inbox = Inbox::updateOrCreate(
            [
                'email' => $parser->getAddresses('to')[0]["address"]
            ],
            [
                'display_name' => $parser->getAddresses('to')[0]["display"],
                'local_part' => '',
                'domain' => '',
            ]
        );

        $email = new Email;
        $email->subject = $parser->getHeader('subject');

        if (!empty($parser->getMessageBody('html')))
        {
            $email->has_html = true;
        }
        if (!empty($parser->getMessageBody('text')))
        {
            $email->has_text = true;
        }

        $email->sender()->associate($sender);

        $inbox->emails()->save($email);

        if ($file == 'stream')
        {
            Storage::put($email->path(), file_get_contents("php://stdin"));
        }
        else
        {
            Storage::put($email->path(), file_get_contents($file));
        }

        $email->size_in_bytes = Storage::size($email->path());

        $email->save();

        foreach($parser->getAttachments() as $attachmentParsed)
        {
            $attachment = new Attachment;
            $attachment->email_id = $email->id;
            $attachment->headers_hashed = $attachment->hashHeaders($attachmentParsed->getHeaders());
            $attachment->file_name = $attachmentParsed->getFileName();
            $attachment->content_type = $attachmentParsed->getContentType();
            $attachment->size_in_bytes = strlen($attachmentParsed->getMimePartStr());
            $attachment->save();
        }

    }
}
