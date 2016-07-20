@extends('app')

@section('content')

    <!--<div class="container">-->
        <div class="row">
           {{-- <form method="post" action={{action("InstallController@install")}}> --}}
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                  <div class="col-md-10 col-md-offset-1">

                      @if (count($errors) > 0)
                          <div class="alert alert-danger">
                                  <strong>{{trans('install_install.whoops')}}!</strong> {{trans('install_install.makesure')}}<br>
                                  <ul>
                                      @foreach ($errors->all() as $error)
                                          <li>{{ $error }}</li>
                                      @endforeach
                                  </ul>
                              </div>
                      @endif

                      <div class="panel panel-default">
                           <div class="panel-heading">
                               {{trans('install_install.data')}}
                        </div>
                           <div class="panel-body">




                                <div class="form-group">
                                    <label for="db_driver">{{trans('install_install.driver')}}:</label>
                                    <select id="db_driver" name="db_driver" class="form-control">
                                        <option value="mysql">MySQL</option>
                                        <option value="pgsql">PostgreSQL</option>
                                        <option value="sqlite">SQLite</option>
                                        <option value="sqlsrv">SQL Server</option>
                                    </select>
                                </div>
                            <div id="not_for_sqlite">
                                <div class="form-group">
                                    <label for="db_host">{{trans('install_install.host')}}:</label>
                                    <input type="text" class="form-control" id="db_host" name="db_host" value="{{old('db_host')}}">
                                </div>

                                <div class="form-group">
                                    <label for="db_database">{{trans('install_install.data')}}:</label>
                                    <input type="text" class="form-control" id="db_database" name="db_database" value="{{old('db_database')}}">
                                </div>

                                <div class="form-group">
                                    <label for="db_username">{{trans('install_install.user')}}:</label>
                                    <input type="text" class="form-control" id="db_username" name="db_username" value="{{old('db_username')}}">
                                </div>

                                <div class="form-group">
                                    <label for="db_password">{{trans('install_install.pass')}}:</label>
                                    <input type="password" class="form-control" id="db_password" name="db_password">
                                </div>
                            </div>
                                <div class="form-group">
                                    <label for="db_prefix">{{trans('install_install.prefix')}}:</label>
                                    <input type="text" class="form-control" id="db_prefix" name="db_prefix" value="{{'kora3_'}}">
                                </div>




                        </div>
                       </div>

                      <div class="panel panel-default">
                        <div class="panel-heading">
                            {{trans('install_install.admin')}}
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="user_username">{{trans('install_install.user')}}:</label>
                                <input type="text" class="form-control" id="user_username" name="user_username" value="{{old('user_username')}}">
                            </div>

                            <div class="form-group">
                                <label for="user_email">{{trans('install_install.email')}}:</label>
                                <input type="text" class="form-control" id="user_email" name="user_email" value="{{old('user_email')}}">
                            </div>

                            <div class="form-group">
                                <label for="user_password">{{trans('install_install.pass')}}:</label>
                                <input type="password" class="form-control" id="user_password" name="user_password">
                            </div>

                            <div class="form-group">
                                <label for="user_confirmpassword">{{trans('install_install.confirmpass')}}:</label>
                                <input type="password" class="form-control" id="user_confirmpassword" name="user_confirmpassword">
                            </div>

                            <div class="form-group">
                                <label for="user_realname">{{trans('install_install.name')}}:</label>
                                <input type="text" class="form-control" id="user_realname" name="user_realname" value="{{old('user_realname')}}">
                            </div>

                           {{-- <div class="form-group">
                                <label for="user_language">Language:</label>
                                <input type="text" class="form-control" id="user_language" name="user_language" value="{{ App::getLocale() }}">
                            </div> --}}

                            <div class="form-group">
                                <label for="user_language" class="control-label">{{trans('install_install.language')}}</label>
                                <select id="user_language" name="user_language" class="form-control">
                                    <!--{{$languages_available = Config::get('app.locales_supported')}} -->
                                    @foreach($languages_available->keys() as $lang)
                                        <option value='{{$languages_available->get($lang)[0]}}'>{{$languages_available->get($lang)[1]}} </option>
                                    @endforeach
                                </select>
                            </div>



                        </div>
                    </div>

                      <div class="panel panel-default">
                              <div class="panel-heading">
                                  {{trans('install_install.mail')}}
                              </div>
                              <div class="panel-body">
                                  <div class="form-group">
                                      <label for="mail_host">{{trans('install_install.host')}}:</label>
                                      <input type="text" class="form-control" id="mail_host" name="mail_host" value="{{old('mail_host')}}">
                                  </div>

                                  <div class="form-group">
                                      <label for="mail_from_address">{{trans('install_install.fromAdd')}}:</label>
                                      <input type="text" class="form-control" id="mail_from_address" name="mail_from_address" value="{{old('mail_from_address')}}">
                                  </div>

                                  <div class="form-group">
                                      <label for="mail_from_name">{{trans('install_install.fromName')}}:</label>
                                      <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" value="{{old('mail_from_name')}}">
                                  </div>

                                  <div class="form-group">
                                      <label for="mail_username">{{trans('install_install.user')}}:</label>
                                      <input type="text" class="form-control" id="mail_username" name="mail_username" value="{{old('mail_username')}}">
                                  </div>

                                  <div class="form-group">
                                      <label for="mail_password">{{trans('install_install.pass')}}:</label>
                                      <input type="password" class="form-control" id="mail_password" name="mail_password">
                                  </div>
                              </div>
                          </div>

                      <div class="panel panel-default">
                              <div class="panel-heading">
                                  Recaptcha
                              </div>
                              <div class="panel-body">
                                  <div class="form-group">
                                      <label for="recaptcha_public_key">{{trans('install_install.public')}}:</label>
                                      <input type="text" class="form-control" id="recaptcha_public_key" name="recaptcha_public_key" value="{{old('recaptcha_public_key')}}">
                                  </div>

                                  <div class="form-group">
                                      <label for="recaptcha_private_key">{{trans('install_install.private')}}:</label>
                                      <input type="text" class="form-control" id="recaptcha_private_key" name="recaptcha_private_key" value="{{old('recaptcha_private_key')}}">
                                  </div>

                              </div>
                          </div>

                      <div class="panel panel-default">
                          <div class="panel-heading">
                              {{trans('install_install.base')}}
                          </div>
                          <div class="panel-body">
                              <div class="form-group">
                                  <label for="baseurl_url">URL:</label>
                                  <input type="text" class="form-control" id="baseurl_url" name="baseurl_url" value="{{old('baseurl_url')}}">
                              </div>
                              <div class="form-group">
                                  <label for="basepath">{{trans('install_install.path')}}:</label>
                                  <input type="text" class="form-control" id="basepath" name="basepath" value="{{old('basepath')}}">
                              </div>
                          </div>
                      </div>

                      <div class="form-group">
                          <button class="btn btn-primary form-control" id="usr">{{trans('install_install.install')}}</button>
                      </div>
                   </div>

        </div>
   <!-- </div> -->
@stop


@section('footer')
<script>
    $("#db_driver").on('change',function() {
        if(this.value == 'sqlite') {
            $("#not_for_sqlite").hide('slow');
            $("#db_host").val('');
            $("#db_database").val('');
            $("#db_username").val('');
            $("#db_password").val('');
        }
        else{
            $("#not_for_sqlite").show('slow');
        }
    });

    $("#usr").on('click',function(){
        sendEnvironmentConfiguration();
    });

    function sendEnvironmentConfiguration() {
        $.ajax({
            url: '{{ action('InstallController@installKora')}}',
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                db_host: $("#db_host").val(),
                db_database: $("#db_database").val(),
                db_username: $("#db_username").val(),
                db_password: $("#db_password").val(),
                db_prefix: $("#db_prefix").val(),
                mail_host: $("#mail_host").val(),
                mail_from_address: $("#mail_from_address").val(),
                mail_from_name: $("#mail_from_name").val(),
                mail_username: $("#mail_username").val(),
                mail_password: $("#mail_password").val(),
                db_driver: $("#db_driver").val(),
                recaptcha_public_key: $("#recaptcha_public_key").val(),
                recaptcha_private_key: $("#recaptcha_private_key").val(),
                baseurl_url: $("#baseurl_url").val(),
                basepath: $("#basepath").val(),
            },
            success: function (result,textStatus,jqXHRs) {
                if (result.status = true) {
                    console.log(result);
                    //alert("Database, mail, and recaptcha settings saved");
                    if(jqXHRs.status ==301){
                        var encode = $("<div/>").html("{{ trans('install_install.problem') }}").text();
                        alert(encode + ".");
                    }
                    else{
                        startApplicationConfiguration();
                    }

                }
            },
            error: function (result,textStatus,jqXHRs) {
                console.log(result);
                var message = "{{trans('install_install.problem')}}";
                if(result.status == 422){
                    message = "{{trans('install_install.settings')}}:\n ";
                    for(var prop in result.responseJSON){
                        if(!result.responseJSON.hasOwnProperty(prop)){
                            continue;
                        }
                        message += (prop + "\n");
                    }
                }
                if(result.status == false){
                    message = result.message;
                }
                alert(message);
                //location.reload();
            }
        });
    }

    function startApplicationConfiguration() {
        $.ajax({
            url: '{{ action('InstallController@runMigrate')}}',
            type: 'POST',
            data: {
                "_token": "{{ csrf_token() }}",
                user_username: $("#user_username").val(),
                user_email: $("#user_email").val(),
                user_password: $("#user_password").val(),
                user_confirmpassword: $("#user_confirmpassword").val(),
                user_realname: $("#user_realname").val(),
                user_language: $("#user_language").val(),
            },
            success: function (result) {
                if (result.status = true) {
                    console.log(result);
                    //alert("Database migration completed and admin account created");
                    location.reload();
                }
            },
            error: function (result) {
                console.log(result);
                var message = "{{trans('install_install.adminproblem')}}";
                if(result.status == 422){
                    message = "{{trans('install_install.settings')}}:\n ";
                    for(prop in result.responseJSON){
                        if(!result.responseJSON.hasOwnProperty(prop)){
                            continue;
                        }
                        message += (prop + "\n");
                    }
                }
                if(result.status == false){
                    message = result.message;
                }
                alert(message);
                //location.reload();
            }
        });
    }




</script>

@stop

