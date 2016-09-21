Hello {{$name}},
<br><br>
Your permissions have been changed for the following project: {{$project->name}}
<br><br>
You may visit the project page <a href="{{action('ProjectController@show', ['id'=>$project->pid])}}">here</a>