<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class OptionPreset extends Model {

    protected $fillable = ['pid', 'type', 'name', 'preset'];

	public function project() {
        return $this->belongsTo('App\Project','pid');
    }

}
