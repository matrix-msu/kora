<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Metadata extends Model {

    /*
    |--------------------------------------------------------------------------
    | Metadata
    |--------------------------------------------------------------------------
    |
    | This model represents the metadata settings for fields within a form
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'pid',
        'fid',
        'flid',
        'name',
        'primary'
    ];

    /**
     * @var string - Database column that represents the primary key
     */
    protected $primaryKey = "flid";

    /**
     * @var string - Force the table name since 5.4 assumes the singular
     */
    protected $table = "metadatas";

    /**
     * Returns the field that this metadata represents.
     *
     * @return BelongsTo
     */
    public function field(){
        return $this->belongsTo('App\Field','flid','flid');
    }
}
