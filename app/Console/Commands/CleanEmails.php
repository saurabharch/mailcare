<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Email;
use Carbon\Carbon;

class CleanEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailcare:clean-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean emails soft deleted';

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
        $date = Carbon::now()->subMonth();

        $emails = Email::onlyTrashed()->where('deleted_at', '<', $date)->get();
        $emails->each->forceDelete();
    }
}
