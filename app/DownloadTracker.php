<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DownloadTracker extends Model
{
    protected $fillable = [
        "fid",
    ];
}
