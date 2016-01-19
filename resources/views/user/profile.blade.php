@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>{{trans('user_profile.heading',['name'=>Auth::user()->name])}}</h1>
                    </div>

                    <div class="panel-body">
                        <h3>{{trans('user_profile.info')}}:</h3>

                        @include('partials.changeprofile',compact('languages_available'))

                        <hr>

                        @include('partials.changepassword')

                        <hr>

                        @include('partials.showpermissions')

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')

    <script>
        $( ".panel-heading" ).on( "click", function() {
            if ($(this).siblings('.collapseTest').css('display') == 'none' ){
                $(this).siblings('.collapseTest').slideDown();
            }else {
                $(this).siblings('.collapseTest').slideUp();
            }
        });

        function updateLanguage(selected_lang){
            changeProfile("lang",selected_lang);
        }

       function updateOrganization(){
           changeProfile("org",$("#organization").val());
       }
       function updateRealName(){
           changeProfile("name",$("#realname").val());
       }

        function changeProfile(rtype,rvalue){
            var updateURL ="{{action('Auth\UserController@changeprofile')}}";
            $.ajax({
                url:updateURL,
                method:'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "type": rtype,
                    "field": rvalue
                },
                success: function(data){
                    window.location.replace('{{action('Auth\UserController@index')}}');
                }
            });
        }

    </script>

@stop