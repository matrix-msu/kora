{!! Form::open(['method' => 'POST', 'action' => 'TokenController@create']) !!}

    <div class="form-group">
        {!! Form::label('title', 'Title') !!}
        {!! Form::text('title', '', ['id' => 'title', 'class' => 'form-control']) !!}
    </div>

    <div id="checkboxes">
        <span><b>Token Permissions:</b></span>
        <ul class="list-group" id="token_permissions">
            <li class="list-group-item">Search: <input type="checkbox" id="search" name="search"></li>
            <li class="list-group-item">Create: <input type="checkbox" id="create" name="create"></li>
            <li class="list-group-item">Edit: <input type="checkbox" id="edit" name="edit"></li>
            <li class="list-group-item">Delete: <input type="checkbox" id="delete" name="delete"></li>
        </ul>
    </div>

    <div class="form-group">
        {!! Form::label('project_list', trans('partials_newToken.projects').'') !!}
        {!! Form::select('projects[]', $projects, null, ['id' => 'projects', 'class' => 'form-control', 'multiple']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit(trans('partials_newToken.token'), ['class' => 'btn btn-primary form-control']) !!}
    </div>

{!! Form::close() !!}

<script>
    $("#type").select2({ width: 'hybrid' });
</script>
