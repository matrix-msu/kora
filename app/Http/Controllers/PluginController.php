<?php

namespace App\Http\Controllers;

use App\Field;
use App\Form;
use App\FormGroup;
use App\Metadata;
use App\Plugin;
use App\Project;
use App\ProjectGroup;
use App\RecordPreset;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PluginController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Plugin Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles plugin activation/management
    |
    */

    /**
     * Constructs controller and makes sure user is authenticated.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
        $this->middleware('admin');
    }

    /**
     * Gets the view for the plugin management page.
     *
     * @return View
     */
    public function index() {
        $newPlugs = array();

        foreach(new \DirectoryIterator(env('BASE_PATH') . 'storage/app/plugins/') as $folder) {
            if($folder->isDir() && $folder->getFilename()!='.' && $folder->getFilename()!='..') {
                $results = DB::select("select * from ".env('DB_PREFIX')."plugins where name = :name", ['name'=>$folder->getFilename()]);
                if(sizeof($results)==0)
                    array_push($newPlugs,$folder->getFilename());
            }
        }

        $plugins = Plugin::all();

        return view('plugins.index', compact('newPlugs','plugins'));
    }

    /**
     * Triggers the install function for a plugin.
     *
     * @param  string $name - Name of the plugin
     */
    public function install($name) {
        $values = array();
        $menus = array();
        $options = array();
        $handle = fopen(env('BASE_PATH') . 'storage/app/plugins/' . $name . '/k3plugin.config', "r");
        if($handle) {
            $values['name'] = $name;
            while(($buffer = fgets($handle, 4096)) !== false) {
                $buffer = trim($buffer);
                $index = explode('=',$buffer)[0];
                $value = explode('=',$buffer)[1];

                if($index=='plugin_url') {
                    $values['url'] = $value;
                } else if($index=='plugin_project') {
                    $proj = explode(':',$value);
                    $values['project']['name'] = $proj[0];
                    $values['project']['slug'] = $proj[1];
                } else if($index=='plugin_description') {
                    $values['project']['desc'] = $value;
                } else if($index=='plugin_menu') {
                    $menu = array();
                    $m = explode(':',$value);
                    $menu['name'] = $m[0];
                    $menu['url'] = $m[1];
                    $menu['order'] = $m[2];

                    array_push($menus,$menu);
                } else if($index=='plugin_option') {
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
        foreach($menus as $menu) {
            DB::insert("insert into ".env('DB_PREFIX')."plugin_menus (plugin_id, name, url, `order`) values (?, ?, ?, ?)",
                [$plugin->id, $menu['name'], $menu['url'], $menu['order']]);
        }

        //plugin menus
        foreach($options as $option) {
            DB::insert("insert into ".env('DB_PREFIX')."plugin_settings (plugin_id, `option`, value) values (?, ?, ?)",
                [$plugin->id, $option['option'], $option['value']]);
        }

        //assign project group to plugin
        DB::insert("insert into ".env('DB_PREFIX')."plugin_users (plugin_id, gid) values (?, ?)",
            [$plugin->id, $proj->adminGID]);

        //import forms associated with plugin
        foreach(new \DirectoryIterator(env('BASE_PATH') . 'storage/app/plugins/'.$name.'/Forms/') as $file) {
            if($file->isFile()) {
                $this->importForm($plugin->pid,env('BASE_PATH') . 'storage/app/plugins/'.$name.'/Forms/'.$file->getFilename());
            }
        }

        flash()->overlay(trans('controller_plugin.install'),trans('controller_plugin.goodjob'));
    }

    /**
     * Update configuration settings for a plugin.
     *
     * @param  Request $request
     */
    public function update(Request $request) {
        //initialize variables we need
        $plugin_id = $request->plugin_id;
        $options = $request->options;
        $users = $request->users;
        $plugin = self::getPlugin($plugin_id);
        $project = ProjectController::getProject($plugin->pid);
        $gid = $project->adminGID;

        //Update each option
        if(!is_null($options)) {
            foreach($options as $key=>$opt) {
                DB::update("update ".env('DB_PREFIX')."plugin_settings set value=? where `option`= ?", [$opt,$key]);
            }
        }

        //Clear users in group and re-add
        DB::delete("delete from ".env('DB_PREFIX')."project_group_user where project_group_id=? and user_id!=1",[$gid]);
        if(!is_null($users)) {
            foreach($users as $uid) {
                DB::insert("insert into ".env('DB_PREFIX')."project_group_user (project_group_id, user_id) values (?, ?)",
                    [$gid, $uid]);
            }
        }
    }

    /**
     * Activate or deactivate a plugin.
     *
     * @param  Request $request
     */
    public function activate(Request $request) {
        $plid = $request->plid;
        $checked = $request->checked;

        $plugin = self::getPlugin($plid);
        if($checked=='true')
            $plugin->active = 1;
        else
            $plugin->active = 0;
        $plugin->save();
    }

    /**
     * Uninstalls a plugin from the installation.
     *
     * @param  int $plid - Plugin ID
     */
    public function destroy($plid) {
        $plugin = self::getPlugin($plid);
        $project = ProjectController::getProject($plugin->pid);

        $project->delete();
        $plugin->delete();

        flash()->overlay(trans('controller_plugin.deleted'),trans('controller_plugin.goodjob'));
    }

    /**
     * Gets a specific plugin model.
     *
     * @param  int $plid - Plugin ID
     * @return Plugin - The model itself
     */
    public static function getPlugin($plid) {
        $plugin = Plugin::where('id','=',$plid)->first();

        return $plugin;
    }

    /**
     * Import a form file for a plugin project installation.
     *
     * @param  int $pid - Project ID
     * @param  string $filepath - Path to k3Form file
     */
    private function importForm($pid, $filepath) {
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->admin && !\Auth::user()->isProjectAdmin($project)) {
            return redirect('projects/'.$pid);
        }

        $fileArray = json_decode(file_get_contents($filepath));

        $form = new Form();

        $form->pid = $project->pid;
        $form->name = $fileArray->name;
        if(Form::where('slug', '=', $fileArray->slug)->exists()) {
            $unique = false;
            $i=1;
            while(!$unique) {
                if(Form::where('slug', '=', $fileArray->slug.$i)->exists()) {
                    $i++;
                } else {
                    $form->slug = $fileArray->slug.$i;
                    $unique = true;
                }
            }
        } else {
            $form->slug = $fileArray->slug;
        }
        $form->description = $fileArray->desc;
        $form->preset = $fileArray->preset;
        $form->public_metadata = $fileArray->metadata;

        $form->save();

        //make admin group
        $admin = $this->makeFormAdminGroup($form);
        $this->makeDefaultGroup($form);
        $form->adminGID = $admin->id;
        $form->save();

        //pages
        $pages = $fileArray->pages;
        $pConvert = array();

        foreach($pages as $page) {
            $p = new Page();

            $p->parent_type = $page->parent_type;
            $p->fid = $form->fid;
            $p->title = $page->title;
            $p->sequence = $page->sequence;

            $p->save();

            $pConvert[$page->id] = $p->id;
        }

        //record presets
        $recPresets = $fileArray->recPresets;

        foreach($recPresets as $pre) {
            $rec = new RecordPreset();

            $rec->fid = $form->fid;
            $rec->name = $pre->name;
            $rec->preset = $pre->preset;

            $rec->save();
        }

        $fields = $fileArray->fields;

        foreach($fields as $fieldArray) {
            $field = new Field();

            $field->pid = $project->pid;
            $field->fid = $form->fid;
            $field->page_id = $fieldArray->page_id;
            $field->sequence = $fieldArray->sequence;
            $field->type = $fieldArray->type;
            $field->name = $fieldArray->name;
            if(Field::where('slug', '=', $fieldArray->slug)->exists()) {
                $unique = false;
                $i=1;
                while(!$unique) {
                    if(Field::where('slug', '=', $fieldArray->slug.$i)->exists()) {
                        $i++;
                    } else {
                        $field->slug = $fieldArray->slug.$i;
                        $unique = true;
                    }
                }
            } else {
                $field->slug = $fieldArray->slug;
            }
            $field->desc = $fieldArray->desc;
            $field->required = $fieldArray->required;
            $field->searchable = $fieldArray->searchable;
            $field->extsearch = $fieldArray->extsearch;
            $field->viewable = $fieldArray->viewable;
            $field->viewresults = $fieldArray->viewresults;
            $field->extview = $fieldArray->extview;
            $field->default = $fieldArray->default;
            $field->options = $fieldArray->options;

            $field->save();

            //metadata
            if($fieldArray->metadata!="") {
                $meta = new Metadata();
                $meta->flid = $field->flid;
                $meta->pid = $project->pid;
                $meta->fid = $form->fid;
                $meta->name = $fieldArray->metadata;
                $meta->save();
            }
        }
    }

    /**
     * Creates the form's admin group.
     *
     * @param  Form $form - Form to create group for
     * @return FormGroup - The newly created group
     */
    private function makeFormAdminGroup(Form $form) {
        $groupName = $form->name;
        $groupName .= ' Admin Group';

        $adminGroup = new FormGroup();
        $adminGroup->name = $groupName;
        $adminGroup->fid = $form->fid;
        $adminGroup->save();

        $formProject = $form->project()->first();
        $projectAdminGroup = $formProject->adminGroup()->first();

        $projectAdmins = $projectAdminGroup->users()->get();
        $idArray = [];

        //Add all current project admins to the form's admin group.
        foreach($projectAdmins as $projectAdmin)
            $idArray[] .= $projectAdmin->id;


        $idArray = array_unique(array_merge(array(\Auth::user()->id), $idArray));

        if(!empty($idArray))
            $adminGroup->users()->attach($idArray);

        $adminGroup->create = 1;
        $adminGroup->edit = 1;
        $adminGroup->delete = 1;
        $adminGroup->ingest = 1;
        $adminGroup->modify = 1;
        $adminGroup->destroy = 1;

        $adminGroup->save();

        return $adminGroup;
    }

    /**
     * Creates the form's default group.
     *
     * @param  Form $form - Form to create group for
     */
    private function makeDefaultGroup(Form $form) {
        $groupName = $form->name;
        $groupName .= ' Default Group';

        $defaultGroup = new FormGroup();
        $defaultGroup->name = $groupName;
        $defaultGroup->fid = $form->fid;
        $defaultGroup->save();

        $defaultGroup->create = 0;
        $defaultGroup->edit = 0;
        $defaultGroup->delete = 0;
        $defaultGroup->ingest = 0;
        $defaultGroup->modify = 0;
        $defaultGroup->destroy = 0;

        $defaultGroup->save();
    }

    /**
     * Gets and loads a specific plugin view.
     *
     * @param  string $name - Name of plugin
     * @param  string $view - View of plugin
     * @return View
     */
    public function loadView($name, $view) {
        $fullName = Plugin::where('url','=',$name)->first()->name;

        include(env('BASE_PATH').'storage/app/plugins/'.$fullName.'/'.$name.'.php');

        $namespace = "App\\Http\\Controllers\\";
        $nameClass = "{$namespace}".$name;

        $controller = new $nameClass();

        return $controller->loadView($view);
    }

    /**
     * Executes a plugin controller method.
     *
     * @param  string $name - Plugin name
     * @param  string $action - Name of method in controller
     * @param  Request $request
     * @return mixed - The return result of the method
     */
    public function action($name, $action, Request $request) {
        $fullName = Plugin::where('url','=',$name)->first()->name;

        include(env('BASE_PATH').'storage/app/plugins/'.$fullName.'/'.$name.'.php');

        $namespace = "App\\Http\\Controllers\\";
        $nameClass = "{$namespace}".$name;

        $controller = new $nameClass();

        return $controller->action($name, $action, $request);
    }
}
