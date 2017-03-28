<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Email;
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

        $email = new Email;
        $email->from = $parser->getHeader('from');
        $email->to = $parser->getHeader('to');
        $email->subject = $parser->getHeader('subject');

        $email->save();

        Storage::put('emails/' . $email->created_at->format('Y/m/d/') . $email->id, file_get_contents($file));

    }
}
