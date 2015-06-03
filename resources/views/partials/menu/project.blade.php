<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ \App\Http\Controllers\ProjectController::getProject($pid)->name }}<b class="caret"></b></a>
    <ul class="dropdown-menu">
        <li><a href="{{ url('/projects/'.$pid) }}">Project Home</a></li>
    </ul>
</li>