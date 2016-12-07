<?php

namespace App\Commands;

use App\Commands\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveKora2Scheme extends CommandKora2 implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public function handle() {

    }
}
