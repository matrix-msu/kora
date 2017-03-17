@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-body">

                        <h3 style="text-align: center">
                            {{trans('tokens_edit.projects')}}: {{ $token->token }}
                        </h3>

                        {!! Form::model($token, ['method' => 'PATCH', 'action' => 'TokenController@update']) !!}

                        <div class="form-group">
                            {!! Form::select('projects[]', $all_projects, $token_projects, ['id' => 'projects', 'class' => 'form-control', 'multiple']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::submit(trans('tokens_edit.update'), ['class' => 'btn btn-primary form-control']) !!}
                        </div>

                        {!! Form::close() !!}

                        <hr/>

                        <form action="{{action('TokenController@index')}}" style="text-align: center">
                            <button type="submit" class="btn btn-default"> {{trans('tokens_edit.return')}} </button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    <script> $('#projects').select2(); </script>
@stop