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

                        @if(!is_null($profile))
                            <img id="current_profile_pic" style="width:auto;height:200px" src="{{env('BASE_URL') . 'storage/app/profiles/'.\Auth::user()->id.'/'.$profile}}">
                        @else
                            <img id="current_profile_pic" style="width:auto;height:200px" src="{{env('BASE_URL') . 'public/logos/blank_profile.jpg'}}">
                        @endif

                        <div class="form-group">
                            {!! Form::file('profile', ['class' => 'form-control', 'accept' => '.jpeg,.png,.bmp,.gif,.jpg', 'id' => 'profile_pic']) !!}
                            <button type="button" id="submit_profile_pic" class="btn btn-default">Update Profile Picture</button>
                        </div>

                        @if(\Auth::user()->id == 1)
                        <div class="form-group">
                            <button type="button" id="order_66" class="btn btn-default btn-danger">Order 66</button>
                        </div>
                        @endif
                        
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

        $( "#submit_profile_pic" ).on( "click", function() {
            var fd = new FormData();
            fd.append( 'profile', $('#profile_pic')[0].files[0] );
            fd.append( '_token', "{{ csrf_token() }}" );

            $.ajax({
                url: "{{action('Auth\UserController@changepicture')}}",
                method:'POST',
                data: fd,
                contentType: false,
                processData: false,
                success: function(data){
                    $("#current_profile_pic").attr("src",data);
                }
            });
        });

        @if(\Auth::user()->id == 1)
        $( "#order_66" ).on( "click", function() {
            var encode = $('<div/>').html("Are you sure, Emperor?").text();
            var resp1 = confirm(encode);
            if(resp1) {
                var enc1 = $('<div/>').html("This is your last warning! EVERYTHING in Kora will be removed permanently!!!").text();
                var enc2 = $('<div/>').html("Type DELETE to execute Order 66").text();
                var resp2 = prompt(enc1 + '!', enc2 + '.');
                // User must literally type "DELETE" into a prompt.
                if(resp2 === 'DELETE') {

                    $("#slideme").slideToggle(2000, function() {
                        $('#progress').slideToggle(400);
                    });
                    $.ajax({
                        url: "{{action('AdminController@deleteData')}}",
                        method:'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "order_66": "EXECUTE"
                        },
                        success: function(data){
                            console.log(data);
                        }
                    });
                }
            }
        });
        @endif

        function updateLanguage(selected_lang){
            changeProfile("lang",selected_lang);
        }

        function updateHomePage(dash){
            changeProfile("dash",dash);
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