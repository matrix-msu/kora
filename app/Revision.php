<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model {

    protected $fillable = ['id','fid','rid','userId','type','data','oldData','rollback'];

    const EDIT = "edit";
    const CREATE = "create";
    const DELETE = "delete";
    const ROLLBACK = "rollback";

    static public $REVISION_TYPES = [
        self::EDIT,
        self::CREATE,
        self::DELETE,
        self::ROLLBACK
    ];

    /**
     * Gets record associated with a revision.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function record(){
        return $this->belongsTo('App\Record', 'rid');
    }

    /**
     * Gets for associated with a revision.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function form(){
        return $this->belongsTo('App\Form', 'fid');
    }
}
