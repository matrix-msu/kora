<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Plugin extends Model {

    /*
    |--------------------------------------------------------------------------
    | Plugin
    |--------------------------------------------------------------------------
    |
    | This model represents an installed Kora3 plugin
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'pid',
        'name',
        'active',
        'url'
    ];

    /**
     * Returns all of the option settings for a plugin.
     *
     * @return array - The option set
     */
    public function options() {
        return DB::select("select * from ".config('database.connections.mysql.prefix')."plugin_settings where plugin_id=?", [$this->id]);
    }

    /**
     * Returns all users belonging to the plugin.
     *
     * @return array - The users
     */
    public function users() {
        $users = array();
        $gid = DB::select("select gid from ".config('database.connections.mysql.prefix')."plugin_users where plugin_id=?", [$this->id])[0]->gid;
        $uids = DB::select("select user_id from ".config('database.connections.mysql.prefix')."project_group_user where project_group_id=?", [$gid]);
        foreach($uids as $uid) {
            $user = User::where('id','=',$uid->user_id)->get()->first();
            if($user->id!=1)
                array_push($users,$user);
        }

        return $users;
    }

    /**
     * Gets a list of users that are not assigned to the plugin.
     *
     * @return array - The users
     */
    public function new_users(){
        $curr = $this->users();
        $all = array();
        $users = User::all();
        foreach($users as $user) {
            if($user->id!=1)
                array_push($all,$user);
        }

        return array_diff($all,$curr);
    }

    /**
     * Returns the menu URIs that belong to the plugin.
     *
     * @return array - The menu items
     */
    public function menus(){
        return DB::select("select name,url from ".config('database.connections.mysql.prefix')."plugin_menus where plugin_id=? order by `order` ASC", [$this->id]);
    }

    /**
     * Deletes the plugins saved configurations and registered users, then deletes self.
     */
    public function delete() {
        DB::table("plugin_menus")->where("plugin_id", "=", $this->id)->delete();
        DB::table("plugin_settings")->where("plugin_id", "=", $this->id)->delete();
        DB::table("plugin_users")->where("plugin_id", "=", $this->id)->delete();
        DB::table("plugins")->where("id", "=", $this->id)->delete();

        parent::delete();
    }
}
