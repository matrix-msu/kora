<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revision extends Model {

    /*
    |--------------------------------------------------------------------------
    | Revision
    |--------------------------------------------------------------------------
    |
    | This model represents the data for a record revision
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = ['form_id','record_kid','revision'];

    /**
     * @var string - The individual types of a revision
     */
    const CREATE = "create";
    const EDIT = "edit";
    const DELETE = "delete";
    const ROLLBACK = "rollback";
    /**
     * @var array - Array representation of revision types
     */
    static public $REVISION_TYPES = [
        self::CREATE,
        self::EDIT,
        self::DELETE,
        self::ROLLBACK
    ];

    /**
     * Gets record associated with a revision.
     *
     * @return BelongsTo
     */
    public function record() {
        return $this->belongsTo('App\Record', 'record_kid');
    }

    /**
     * Gets for associated with a revision.
     *
     * @return BelongsTo
     */
    public function form() {
        return $this->belongsTo('App\Form', 'form_id');
    }
}
