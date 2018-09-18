<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Preference extends Model {

    /*
    |--------------------------------------------------------------------------
    | Page
    |--------------------------------------------------------------------------
    |
    | This model represents preferences for a user
    |
    */

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    // Logo Target Options
    const DASHBOARD = 1;
    const PROJECTS = 2;

    protected static $logoTargetOptions = array(
        self::DASHBOARD => 'Dashboard',
        self::PROJECTS  => 'Projects',
    );

    // Projects Page Tab Selection Options
    const ARCHIVED = 1;
    const CUSTOM = 2;
    const ALPHABETICAL = 3;

    protected static $projPageTabSelOptions = array(
        self::ARCHIVED => 'Archived',
        self::CUSTOM  => 'Custom',
        self::ALPHABETICAL => 'Alphabetical'
    );

    // Single Project Page Tab Selection
    const SINGLE_RECENTLY_MODIFIED = 1;
    const SINGLE_CUSTOM = 2;
    const SINGLE_ALPHABETICAL = 3;

    protected static $singleProjTabSelOptions = array(
        self::SINGLE_CUSTOM  => 'Custom',
        self::SINGLE_ALPHABETICAL => 'Alphabetical'
    );

    // Side Menu on Wider Screens
    const KEEP_OPEN = 1;
    const LET_CLOSE = 2;

    protected static $sideMenuOptions = array(
        self::KEEP_OPEN => 'Keep Side Menu Open',
        self::LET_CLOSE => 'Let Side Menu Close Automatically'
    );

    /**
     * @var array - Attributes that cannot be mass assigned to model
     */
    protected $guarded = [];

    public static function logoTargetOptions() {
        return static::$logoTargetOptions;
    }

    public static function projPageTabSelOptions() {
        return static::$projPageTabSelOptions;
    }

    public static function singleProjTabSelOptions() {
        return static::$singleProjTabSelOptions;
    }

    public static function sideMenuOptions() {
        return static::$sideMenuOptions;
    }
}
