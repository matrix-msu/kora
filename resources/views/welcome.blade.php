@extends('app')

@section('content')
<style>
    .body_quote {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
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
                <img src="{{ env('BASE_URL') }}public/logos/koraiii-logo-blue.svg">
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

