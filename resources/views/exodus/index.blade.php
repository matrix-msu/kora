@extends('app')

@section('content')
    <span><h1>{{trans('exodus_index.title')}}</h1></span>

    <hr>

    <div class="form-group">
        {{trans('exodus_index.warning')}}
    </div>

    {!! Form::open(['url' => action('ExodusController@migrate'), 'id' => 'k2_form']) !!}

    <div id="db_info_div">
        <div class="form-group">
            {!! Form::label('host', trans('exodus_index.host').': ') !!}
            {!! Form::text('host','', ['class' => 'form-control', 'id' => 'db_host']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('name', trans('exodus_index.name').': ') !!}
            {!! Form::text('name','', ['class' => 'form-control', 'id' => 'db_name']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('user', trans('exodus_index.user').': ') !!}
            {!! Form::text('user','', ['class' => 'form-control', 'id' => 'db_user']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('pass', trans('exodus_index.pass').': ') !!}
            {!! Form::password('pass', ['class' => 'form-control', 'id' => 'db_pass']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('filePath', trans('exodus_index.files').': ') !!}
            {!! Form::text('filePath','', ['class' => 'form-control', 'placeholder' => '/{system_path}/{Kora2}/files']) !!}
        </div>

        <div class="form-group" id="get_projects_div">
            <button type="button" id="get_projects" class="form-control btn btn-primary">Analyze System</button>
        </div>
    </div>

    <div id="project_selection_div" style="display: none">
        <div class="form-group">
            {!! Form::label('users', 'Migrate System Users: ') !!}
            {!! Form::checkbox('users',1,0,['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('tokens', 'Migrate System Tokens: ') !!}
            {!! Form::checkbox('tokens',1,0,['class' => 'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('projects', 'Select Projects to Migrate: ') !!}
            {!! Form::select('projects[]',array(),null,['class' => 'form-control', 'id' => 'project_select', 'Multiple']) !!}
        </div>

        <div class="form-group" id="k2_submit">
            <button class="form-control btn btn-primary">{{trans('exodus_index.begin')}}</button>
        </div>
    </div>

    <div style="display:none;" id="search_progress" class="progress">
        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
            {{trans('update_index.loading')}}
        </div>
    </div>

    {!! Form::close() !!}

@stop

@section('footer')
    <script>
        $("#get_projects_div").on('click','#get_projects',function(){
            var projURL = "{{action('ExodusController@getProjectList')}}";
            $.ajax({
                url:projURL,
                method:'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "host": $('#db_host').val(),
                    "user": $('#db_user').val(),
                    "name": $('#db_name').val(),
                    "pass": $('#db_pass').val()
                },
                success: function(data){
                    var sel = $('#project_select');
                    $("#project_selection_div").slideDown(200);
                    $("#db_info_div").slideUp(200);
                    sel.select2();

                    for(var pid in data){
                        sel.append($("<option></option>")
                                .attr("value",pid)
                                .attr("selected","selected")
                                .text(data[pid]))
                                .trigger("change").select2("close");
                    }
                }
            });
        });

        $("#k2_form").submit(function(e) { $("#search_progress").slideDown(200); $("#k2_submit").slideUp(200);});
    </script>
@stop