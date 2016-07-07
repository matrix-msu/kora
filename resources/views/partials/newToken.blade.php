{!! Form::open(['method' => 'POST', 'action' => 'TokenController@create']) !!}

    <div class="form-group">
        {!! Form::label('type', trans('partials_newToken.type').': ') !!}
        {!! Form::select('type', ['search' => 'Search', 'edit' => 'Edit', 'create' => 'Create', 'delete' => 'Delete']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('project_list', trans('partials_newToken.projects').': ') !!}
        {!! Form::select('projects[]', $projects, null, ['id' => 'projects', 'class' => 'form-control', 'multiple']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit(trans('partials_newToken.token'), ['class' => 'btn btn-primary form-control']) !!}
    </div>

{!! Form::close() !!}

<script>
    $("#type").select2({ width: 'hybrid' });
</script>
