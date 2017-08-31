<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Email;
use App\Inbox;
use App\Attachment;
use \PhpMimeMailParser\Parser;
use Illuminate\Support\Facades\Storage;

class ReceiveEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:receive {file? : The file of the email}';

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

        if ($file)
        {
            $parser->setPath($file); 
        }
        else
        {
            $parser->setStream(fopen("php://stdin", "r"));
        }

        $inbox = Inbox::updateOrCreate([
            'recipient' => $parser->getHeader('to')
        ]);

        $email = new Email;
        $email->from = $parser->getHeader('from');
        $email->subject = $parser->getHeader('subject');

        if (!empty($parser->getMessageBody('html')))
        {
            $email->is_html = true;
        }
        if (!empty($parser->getMessageBody('text')))
        {
            $email->is_text = true;
        }

        $inbox->emails()->save($email);

        Storage::put($email->path(), file_get_contents($file));

        $email->size_in_bytes = Storage::size($email->path());

        $email->save();

        foreach($parser->getAttachments() as $attachmentParsed)
        {
            $attachment = new Attachment;
            $attachment->email_id = $email->id;
            $attachment->file_name = $attachmentParsed->getFileName();
            $attachment->content_type = $attachmentParsed->getContentType();
            $attachment->size_in_bytes = strlen($attachmentParsed->getMimePartStr());
            $attachment->save();
        }

    }
}
