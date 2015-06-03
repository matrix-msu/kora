<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ \App\Http\Controllers\FormController::getForm($fid)->name }}<b class="caret"></b></a>
    <ul class="dropdown-menu">
        <li><a href="{{ url('/projects/'.$pid).'/forms/'.$fid}}">Form Home</a></li>
        <li class="divider"></li>
        <li><a href="{{ url('/projects/'.$pid).'/forms/'.$fid.'/records'}}">Records</a></li>
        <li><a href="{{ url('/projects/'.$pid).'/forms/'.$fid.'/records/create'}}">New Record</a></li>
    </ul>
</li>