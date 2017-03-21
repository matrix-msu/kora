Hello Kora3 Project Administrator!
<br><br>
The user, {{\Auth::user()->username}} ({{\Auth::user()->name}}), is requesting access to the following project: {{$project->name}}
<br><br>
Visit the Manage Groups page <a href="{{action('ProjectGroupController@index', ['pid'=>$project->pid])}}">here</a> to grant access