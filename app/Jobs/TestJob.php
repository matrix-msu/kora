<?php

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class TestJob extends Job
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Inside test job!");

        $path_to_storage = storage_path() . "/testing/";

        $filename = microtime(false);

        $path = $path_to_storage . $filename;

        for ($i = 0; $i < 10000; $i++) {
            file_put_contents($path, $this->random_base62() . "\n", FILE_APPEND);
        }

        Log::info("Leaving test job!");
    }

    private function random_base62($length = 100) {
        $alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $str = '';
        $count = strlen($alphabet);
        while ($length--) {
            $str .= $alphabet[mt_rand(0, $count-1)];
        }
        return $str;
    }
}
