<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ \App\Http\Controllers\FormController::getForm($fid)->name }}<b class="caret"></b></a>
    <ul class="dropdown-menu">
        <li><a href="{{ url('/projects/'.$pid).'/forms/'.$fid}}">Form Layout</a></li>
        <li class="dropdown-submenu"> <a href="#" data-toggle="dropdown">Jump to Form</a>
            <ul class="dropdown-menu">
                @foreach(\Auth::user()->allowedForms($pid) as $form)
                    @if($form->fid != $fid)
                        <li><a href="{{ url('/projects/'.$pid).'/forms/'.$form->fid }}">{{ $form->name }}</a></li>
                    @endif
                @endforeach
            </ul>
        </li>
        <li class="divider"></li>
        <li><a href="{{ url('/projects/'.$pid).'/forms/'.$fid.'/records'}}">Records</a></li>
        <li><a href="{{ url('/projects/'.$pid).'/forms/'.$fid.'/records/create'}}">New Record</a></li>
        <li class="divider"></li>
        <li><a href="{{url('/projects/'.$pid).'/forms/'.$fid.'/metadata/setup'}}">Metadata</a></li>
    </ul>
</li>