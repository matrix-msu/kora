<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ \App\Http\Controllers\ProjectController::getProject($pid)->name }}<b class="caret"></b></a>
    <ul class="dropdown-menu">
        <li><a href="{{ url('/projects/'.$pid) }}">Project Home</a></li>
        <li class="dropdown-submenu"> <a onmouseover="href='#'" data-toggle="dropdown">Jump to Project</a>
            <ul class="dropdown-menu">
                @foreach(\Auth::user()->allowedProjects() as $project)
                    <li><a href="{{ url('/projects/'.$project->pid) }}">{{ $project->name }}</a></li>
                @endforeach
            </ul>
        </li>
    </ul>
</li>