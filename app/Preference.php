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

    // Logo Target Options
    const DASHBOARD = 1;
    const PROJECTS = 2;

    // Projects Page Tab Selection Options
    const RECENTLY_MODIFIED = 1;
    const CUSTOM = 2;
    const ALPHABETICAL = 3;

    protected static $logoTargetOptions = array(
        self::DASHBOARD => 'Dashboard',
        self::PROJECTS  => 'Projects',
        self::ALPHABETICAL => 'Alphabetical'
    );

    protected static $projPageTabSelOptions = array(
        self::RECENTLY_MODIFIED => 'Dashboard',
        self::CUSTOM  => 'Projects',
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
}