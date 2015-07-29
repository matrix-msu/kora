@extends('app')

@section('content')

    <div class="container">
        <div class="row">
            <form method="post" action={{action("InstallController@install")}}>
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                  <div class="col-md-10 col-md-offset-1">

                      @if (count($errors) > 0)
                          <div class="alert alert-danger">
                                  <strong>Whoops!</strong> Make sure you entered everything correctly<br>
                                  <ul>
                                      @foreach ($errors->all() as $error)
                                          <li>{{ $error }}</li>
                                      @endforeach
                                  </ul>
                              </div>
                      @endif

                      <div class="panel panel-default">
                           <div class="panel-heading">
                            Database
                        </div>
                           <div class="panel-body">




                                <div class="form-group">
                                    <label for="db_driver">Driver:</label>
                                    <select id="db_driver" name="db_driver" class="form-control">
                                        <option value="mysql">MySQL</option>
                                        <option value="pgsql">PostgreSQL</option>
                                        <option value="sqlite">SQLite</option>
                                        <option value="sqlsrv">SQL Server</option>
                                    </select>
                                </div>
                            <div id="not_for_sqlite">
                                <div class="form-group">
                                    <label for="db_host">Host:</label>
                                    <input type="text" class="form-control" id="db_host" name="db_host" value="{{old('db_host')}}">
                                </div>

                                <div class="form-group">
                                    <label for="db_database">Database:</label>
                                    <input type="text" class="form-control" id="db_database" name="db_database" value="{{old('db_database')}}">
                                </div>

                                <div class="form-group">
                                    <label for="db_username">Username:</label>
                                    <input type="text" class="form-control" id="db_username" name="db_username" value="{{old('db_username')}}">
                                </div>

                                <div class="form-group">
                                    <label for="db_password">Password:</label>
                                    <input type="password" class="form-control" id="db_password" name="db_password">
                                </div>
                            </div>
                                <div class="form-group">
                                    <label for="db_prefix">Prefix:</label>
                                    <input type="text" class="form-control" id="db_prefix" name="db_prefix" value="{{'kora3_'}}">
                                </div>




                        </div>
                       </div>

                      <div class="panel panel-default">
                        <div class="panel-heading">
                            Admin User
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="user_username">Username:</label>
                                <input type="text" class="form-control" id="user_username" name="user_username" value="{{old('user_username')}}">
                            </div>

                            <div class="form-group">
                                <label for="user_email">E-Mail:</label>
                                <input type="text" class="form-control" id="user_email" name="user_email" value="{{old('user_email')}}">
                            </div>

                            <div class="form-group">
                                <label for="user_password">Password:</label>
                                <input type="password" class="form-control" id="user_password" name="user_password">
                            </div>

                            <div class="form-group">
                                <label for="user_confirmpassword">Confirm Password:</label>
                                <input type="password" class="form-control" id="user_confrimpassword" name="user_confirmpassword">
                            </div>

                            <div class="form-group">
                                <label for="user_realname">Real Name:</label>
                                <input type="text" class="form-control" id="user_realname" name="user_realname" value="{{old('user_realname')}}">
                            </div>

                            <div class="form-group">
                                <label for="user_language">Language:</label>
                                <input type="text" class="form-control" id="user_language" name="user_language" value="{{ App::getLocale() }}">
                            </div>


                        </div>
                    </div>

                      <div class="panel panel-default">
                              <div class="panel-heading">
                                  Mail
                              </div>
                              <div class="panel-body">
                                  <div class="form-group">
                                      <label for="mail_host">Host:</label>
                                      <input type="text" class="form-control" id="mail_host" name="mail_host" value="{{old('mail_host')}}">
                                  </div>

                                  <div class="form-group">
                                      <label for="mail_from_address">From Address:</label>
                                      <input type="text" class="form-control" id="mail_from_address" name="mail_from_address" value="{{old('mail_from_address')}}">
                                  </div>

                                  <div class="form-group">
                                      <label for="mail_from_name">From Name:</label>
                                      <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" value="{{old('mail_from_name')}}">
                                  </div>

                                  <div class="form-group">
                                      <label for="mail_username">Username:</label>
                                      <input type="text" class="form-control" id="mail_username" name="mail_username" value="{{old('mail_username')}}">
                                  </div>

                                  <div class="form-group">
                                      <label for="mail_password">Password:</label>
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
                                      <label for="recaptcha_public_key">Public Key:</label>
                                      <input type="text" class="form-control" id="recaptcha_public_key" name="recaptcha_public_key" value="{{old('recaptcha_public_key')}}">
                                  </div>

                                  <div class="form-group">
                                      <label for="recaptcha_private_key">Private Key:</label>
                                      <input type="text" class="form-control" id="recaptcha_private_key" name="recaptcha_private_key" value="{{old('recaptcha_private_key')}}">
                                  </div>

                              </div>
                          </div>

                      <div class="panel panel-default">
                              <div class="panel-heading">
                                  Base URL
                              </div>
                              <div class="panel-body">
                                  <div class="form-group">
                                      <label for="baseurl_url">URL:</label>
                                      <input type="text" class="form-control" id="baseurl_url" name="baseurl_url" value="{{old('baseurl_url')}}">
                                  </div>
                              </div>
                          </div>

                      <div class="form-group">
                          <input type="submit" class="btn btn-primary form-control" id="usr" value="Submit">
                      </div>
                   </div>

            </form>
        </div>
    </div>



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
</script>
@endsection

@stop

