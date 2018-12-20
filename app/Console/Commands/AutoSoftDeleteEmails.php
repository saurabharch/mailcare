<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Email;
use App\Statistic;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AutoSoftDeleteEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailcare:auto-soft-delete-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $size = $this->getSizeToDelete();

        Email::where('favorite', false)->oldest()->chunkById(100, function ($emails) use ($size) {

            foreach ($emails as $email)
            {
                if ($size <= 0) {
                    return false;
                }

                $size = $size - $email->size_in_bytes;
                $email->delete();
            }
        });
    }

    public function getSizeToDelete()
    {
        $storageUsedPeriod1 = Statistic::storageUsedBetween(
            Carbon::now()->subMonths(2),
            Carbon::now()->subMonth()
        );

        $storageUsedPeriod2 = Statistic::storageUsedBetween(
            Carbon::now()->subMonth(),
            Carbon::now()
        );

        if (empty($storageUsedPeriod1) || empty($storageUsedPeriod2)) {
            return 0;
        }

        $pourcentage = (($storageUsedPeriod2 - $storageUsedPeriod1) / $storageUsedPeriod1);
        $calculatedStorage = $storageUsedPeriod2 + ($pourcentage * $storageUsedPeriod2);

        if ($this->getDiskFreeSpace() > $calculatedStorage) {
            return 0;
        }

        return $calculatedStorage;
    }

    public function getDiskFreeSpace()
    {
        return disk_free_space(storage_path());
    }
}
