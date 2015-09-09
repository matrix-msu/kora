<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RecordPreset extends Model {

    protected $fillable = ['name', 'fid', 'rid'];

	public function record() {
        return $this->hasOne('App/Record');
    }

}
