Hello {{$name}},
<br><br>
You have been added to a group in the following project: {{$project->name}}
<br><br>
You may visit the project page <a href="{{action('ProjectController@show', ['id'=>$project->pid])}}">here</a>