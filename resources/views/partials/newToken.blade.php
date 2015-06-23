{!! Form::open(['method' => 'POST', 'action' => 'TokenController@create']) !!}

    <div class="form-group">
        {!! Form::label('type', 'Type: ') !!}
        {!! Form::select('type', ['search' => 'Search', 'edit' => 'Edit', 'create' => 'Create', 'delete' => 'Delete']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('project_list', 'Projects: ') !!}
        {!! Form::select('projects[]', $projects, null, ['id' => 'projects', 'class' => 'form-control', 'multiple']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit('Generate Token', ['class' => 'btn btn-primary form-control']) !!}
    </div>

{!! Form::close() !!}

@section('footer')
    <script> $('#projects').select2(); </script>
@stop