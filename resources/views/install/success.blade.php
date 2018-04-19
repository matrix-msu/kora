<!doctype html>

<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <title>Kora 3 - Install Success</title>

    <link rel="stylesheet" href="{{config('app.url')}}assets/css/app.css">
</head>
<body class="install-success-body">

<div class="install-success">
    <section class="last-steps">
        <div class="last-desc">
            <img class="logo mt-xxxl" src="{{ config('app.url') }}assets/logos/koraiii-logo-blue.svg">
            <div class="title mt-xxxl">Kora 3</div>
            <div class="sub-title mt-xl">Initialization Complete</div>
            <div class="description mt-m">
                Kora 3 has finished initialization. Before you begin using Kora, we reccomend you activate the
                following permissions commands.
            </div>
        </div>

        <div class="last-cmd mt-xxxl">
            <div class="form-group">
                <label for="preset">Change directory permissions to read access</label>
                <div class="solid-box">chmod -R 0755 -R Kora3/</div>
            </div>
            <div class="form-group mt-xl">
                <label for="preset">Give write access to the following directories</label>
                <div class="solid-box">chmod -R 0775 -R Kora3/bootstrap/cache/</div>
            </div>
            <div class="form-group mt-xs">
                <div class="solid-box">chmod -R 0775 -R Kora3/storage/</div>
            </div>
            <div class="form-group mt-xs">
                <div class="solid-box">chmod -R 0775 -R Kora3/public/assets/javascripts/production/</div>
            </div>
            <div class="form-group mt-xxxl">
                <a href="{{config('app.url')}}" class="btn">Start Using Kora</a>
            </div>
        </div>
    </section>
</div>

@include('partials.install.javascripts')

@if(Auth::guest() || !Auth::user()->active)
    @include('partials.auth.javascripts')

    <script>
        var langURL ="{{action('WelcomeController@setTemporaryLanguage')}}";

        function setTempLang(selected_lang){
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

        Kora.Auth.Auth();
    </script>
@endif
</body>
</html>
