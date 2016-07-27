<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Plugin extends Model
{
    protected $fillable = [
        'pid',
        'name',
        'active',
        'url'
    ];

    public function options(){
        return DB::select("select * from ".env('DB_PREFIX')."plugin_settings where plugin_id=?", [$this->id]);
    }

    public function users(){
        $users = array();
        $gid = DB::select("select gid from ".env('DB_PREFIX')."plugin_users where plugin_id=?", [$this->id])[0]->gid;
        $uids = DB::select("select user_id from ".env('DB_PREFIX')."project_group_user where project_group_id=?", [$gid]);
        foreach($uids as $uid){
            $user = User::where('id','=',$uid->user_id)->get()->first();
            if($user->id!=1)
                array_push($users,$user);
        }

        return $users;
    }

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
}
