<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model {

    protected $fillable = ['id','fid','rid','userId','type','data','oldData','rollback'];

	public function record(){
        return $this->belongsTo('App\Record', 'rid');
    }

    public function form(){
        return $this->belongsTo('App\Form', 'fid');
    }
}
