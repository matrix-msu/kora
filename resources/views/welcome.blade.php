@extends('app')

@section('body')
<style>
    .body_quote {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 400px;
        color: #B0BEC5;
        display: table;
        font-weight: 100;
        font-family: 'Lato';
    }

    .container_quote {
        text-align: center;
        display: table-cell;
        vertical-align: middle;
    }

    .content_quote {
        text-align: center;
        display: inline-block;
    }

    .title_quote {
        font-size: 96px;
        margin-bottom: 40px;
    }

    .quote {
        font-size: 24px;
    }
</style>
<div class="body_quote">
    <div class="container_quote">
        <div class="content_quote">
            @if(!isset($not_installed))
                <img src="{{ env('BASE_URL') }}logos/koraiii-logo-blue.svg">
            @else
                <img src="logos/koraiii-logo-blue.svg">
            @endif
            <br><br>
            <div class="quote">{{ Inspiring::quote() }}</div>
            <div class="quote">Powered by Laravel</div>

        </div>
    </div>
    <div style="position: fixed; bottom:50; left:50;">
        @if (Auth::guest())
            <button id="langselect" type="button" class="btn btn-default" data-container="body" data-toggle="popover" data-html="true" data-placement="right" data-title="Language" data-content="
                 <ul style='list-style-type: none; padding-left: 0;'>
                    @foreach($languages_available->keys() as $lang)
                        <li><a onclick='setTempLang({{$lang}})' href='#'>{{$languages_available->get($lang)[1]}}</a> </li>
                    @endforeach
                    </ul>">
                <span class="glyphicon glyphicon-globe"></span>  {{App::getLocale()}}
            </button>
        @endif
    </div>

</div>

@if (Auth::guest() && !isset($not_installed))
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">{{trans('auth_login.login')}}</div>
                <div class="panel-body">
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <strong>{{trans('auth_login.whoops')}}!</strong> {{trans('auth_login.problems')}}.<br><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/auth/login') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="form-group">
                            <label class="col-md-4 control-label">{{trans('auth_login.user')}}</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="email" value="{{ old('email') }}" autofocus>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">{{trans('auth_login.pass')}}</label>
                            <div class="col-md-6">
                                <input type="password" class="form-control" name="password">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember"> {{trans('auth_login.remember')}}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">{{trans('auth_login.login')}}</button>

                                <a class="btn btn-link" href="{{ url('/password/email') }}">{{trans('auth_login.forgot')}}?</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif


@endsection


@section('footer')
    <script>
        $(document).ready(function(){
           $("#langselect").popover();
        });
        function setTempLang(selected_lang){
            var langURL ="{{action('WelcomeController@setTemporaryLanguage')}}";
            console.log("Language change started: "+langURL);
            $.ajax({
                url:langURL,
                method:'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "templanguage": selected_lang
                },
                success: function(data){
                    console.log(data);
                    location.reload();
                }
            });
        }
    </script>

@stop

