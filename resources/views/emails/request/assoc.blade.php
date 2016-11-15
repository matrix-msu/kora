Hello Kora3 Form Administrator!
<br><br>
The following form ({{$myProj->name}} | {{$myForm->name}}), is requesting associator access to the following form: {{$theirProj->name}} | {{$theirForm->name}}
<br><br>
Visit the Manage Associations page <a href="{{action('AssociationController@index', ['pid'=>$theirForm->pid,'fid'=>$theirForm->fid])}}">here</a> to grant access