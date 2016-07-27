<?php

namespace App\Http\Controllers;

use App\Plugin;
use App\Project;
use App\ProjectGroup;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

class PluginController extends Controller
{
    /**
     * User must be logged in and admin to access views in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $newPlugs = array();

        foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/plugins/') as $folder) {
            if ($folder->isDir() && $folder->getFilename()!='.' && $folder->getFilename()!='..') {
                $results = DB::select("select * from ".env('DB_PREFIX')."plugins where name = :name", ['name'=>$folder->getFilename()]);
                if(sizeof($results)==0){
                    array_push($newPlugs,$folder->getFilename());
                }
            }
        }

        $plugins = Plugin::all();

        return view('plugins.index', compact('newPlugs','plugins'));
    }

    public function install($name){
        $values = array();
        $menus = array();
        $options = array();
        $handle = fopen(env('BASE_PATH') . 'storage/app/plugins/' . $name . '/k3plugin.config', "r");
        if ($handle) {
            $values['name'] = $name;
            while (($buffer = fgets($handle, 4096)) !== false) {
                $index = explode('=',$buffer)[0];
                $value = explode('=',$buffer)[1];

                if($index=='plugin_url') {
                    $values['url'] = $value;
                }else if($index=='plugin_project'){
                    $proj = explode(':',$value);
                    $values['project']['name'] = $proj[0];
                    $values['project']['slug'] = $proj[1];
                }else if($index=='plugin_description'){
                    $values['project']['desc'] = $value;
                }else if($index=='plugin_menu'){
                    $menu = array();
                    $m = explode(':',$value);
                    $menu['name'] = $m[0];
                    $menu['url'] = $m[1];
                    $menu['order'] = $m[2];

                    array_push($menus,$menu);
                }
                else if($index=='plugin_option'){
                    $option = array();
                    $o = explode(':',$value);
                    $option['option'] = $o[0];
                    $option['value'] = $o[1];

                    array_push($options,$option);
                }
            }
            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }

        //Now that info is gathered neatly from the config, insert

        //project
        $proj = new Project();
        $proj->name = $values['project']['name'];
        $proj->slug = $values['project']['slug'];
        $proj->description = $values['project']['desc'];
        $proj->active = 1;
        $proj->save();

        $groupName = $proj->name;
        $groupName .= ' Admin Group';

        $adminGroup = new ProjectGroup();
        $adminGroup->name = $groupName;
        $adminGroup->pid = $proj->pid;
        $adminGroup->save();

        $adminGroup->users()->attach(array(\Auth::user()->id));

        $adminGroup->create = 1;
        $adminGroup->edit = 1;
        $adminGroup->delete = 1;

        $adminGroup->save();

        $proj->adminGID = $adminGroup->id;
        $proj->save();

        //plugin
        $plugin = new Plugin();
        $plugin->pid = $proj->pid;
        $plugin->name = $values['name'];
        $plugin->active = 0;
        $plugin->url = $values['url'];
        $plugin->save();

        //plugin menus
        foreach($menus as $menu){
            DB::insert("insert into ".env('DB_PREFIX')."plugin_menus (plugin_id, name, url, `order`) values (?, ?, ?, ?)",
                [$plugin->id, $menu['name'], $menu['url'], $menu['order']]);
        }

        //plugin menus
        foreach($options as $option){
            DB::insert("insert into ".env('DB_PREFIX')."plugin_settings (plugin_id, `option`, value) values (?, ?, ?)",
                [$plugin->id, $option['option'], $option['value']]);
        }

        //assign project group to plugin
        DB::insert("insert into ".env('DB_PREFIX')."plugin_users (plugin_id, gid) values (?, ?)",
            [$plugin->id, $proj->adminGID]);

        flash()->overlay(trans('controller_plugin.install'),trans('controller_plugin.goodjob'));
    }

    public function update(Request $request){
        //initialize variables we need
        $plugin_id = $request->plugin_id;
        $options = $request->options;
        $users = $request->users;
        $plugin = PluginController::getPlugin($plugin_id);
        $project = ProjectController::getProject($plugin->pid);
        $gid = $project->adminGID;

        //Update each option
        if(!is_null($options)) {
            foreach ($options as $key=>$opt) {
                DB::update("update ".env('DB_PREFIX')."plugin_settings set value=? where `option`= ?", [$opt,$key]);
            }
        }

        //Clear users in group and re-add
        DB::delete("delete from ".env('DB_PREFIX')."project_group_user where project_group_id=? and user_id!=1",[$gid]);
        if(!is_null($users)) {
            foreach ($users as $uid) {
                DB::insert("insert into ".env('DB_PREFIX')."project_group_user (project_group_id, user_id) values (?, ?)",
                    [$gid, $uid]);
            }
        }
    }

    public function activate(Request $request){
        $plid = $request->plid;
        $checked = $request->checked;

        $plugin = PluginController::getPlugin($plid);
        if($checked=='true')
            $plugin->active = 1;
        else
            $plugin->active = 0;
        $plugin->save();
    }

    public function destroy($plid){
        $plugin = PluginController::getPlugin($plid);
        $project = ProjectController::getProject($plugin->pid);

        $project->delete();
        $plugin->delete();

        flash()->overlay(trans('controller_plugin.deleted'),trans('controller_plugin.goodjob'));
    }

    public static function getPlugin($id){
        $plugin = Plugin::where('id','=',$id)->first();

        return $plugin;
    }
}
