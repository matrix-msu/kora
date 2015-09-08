<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentsField extends Model {

    protected $fillable = [
        'rid',
        'flid',
        'documents'
    ];

    protected $primaryKey = "id";

    public function record(){
        return $this->belongsTo('App\Record');
    }

}
