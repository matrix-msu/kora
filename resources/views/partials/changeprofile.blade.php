<form class=navbar-form" role="search">
<div class="form-group">

    <div class="input-group">
        <span class="input-group-addon">{{trans('profile.username')}}&nbsp&nbsp&nbsp</span>
        <input name="username" type="text" class="form-control" disabled value="{{Auth::user()->username}}">
    </div>
    <br>
    <div class="input-group">
        <span class="input-group-addon">{{trans('profile.email')}}&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</span>
        <input name="email" type="text" class="form-control" disabled value="{{Auth::user()->email}}">
    </div>
    <br>
    <div class="input-group">
        <span class="input-group-addon">{{trans('profile.realname')}}&nbsp&nbsp</span>
        <input id="realname" name="realname" type="text" class="form-control" placeholder="{{Auth::user()->name}}">
        <span class="input-group-btn">
          <button onclick="updateRealName()" class="btn btn-default" type="button">Update</button>
        </span>
    </div>
    <br>
    <div class="input-group">
        <span class="input-group-addon">{{trans('profile.organization')}}:</span>
        <input id="organization" name="organization" type="text" class="form-control" placeholder="{{Auth::user()->organization}}">
        <span class="input-group-btn">
          <button onclick="updateOrganization()" class="btn btn-default" type="button">Update</button>
        </span>
    </div>
    <br>
    <div class="input-group">
        <span class="input-group-addon">{{trans('profile.language')}}&nbsp&nbsp&nbsp&nbsp</span>
        <input type="text" class="form-control" disabled value="{{Auth::user()->language}}">
        <div class="input-group-btn">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Select <span class="caret"></span></button>
            <ul class="dropdown-menu dropdown-menu-right">
                @foreach($languages_available as $lang)
                    <li><a onclick="updateLanguage('{{$lang[0]}}')" href="#en">{{$lang[1]}}</a> </li>
                @endforeach
            </ul>

        </div><!-- /btn-group -->
    </div><!-- /input-group -->

</div>

</form>

