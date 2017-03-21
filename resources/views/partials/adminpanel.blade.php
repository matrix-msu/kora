@if (\Auth::user()->admin)
    <hr/>

    <h4> {{trans('partials_adminpanel.admin')}}</h4>

    <form action="{{ action('AdminController@users') }}" style="display: inline">
        <button type="submit" class="btn btn-default"> {{trans('partials_adminpanel.users')}} </button>
    </form>

    <form action="{{ action('TokenController@index') }}" style="display: inline">
        <button type="submit" class="btn btn-default"> {{trans('partials_adminpanel.tokens')}} </button>
    </form>

    @if(Auth::user()->id == 1)
    <form action="{{ action('BackupController@index') }}" style="display: inline">
        <button type="submit" class="btn btn-default"> {{trans('partials_adminpanel.backups')}} </button>
    </form>
    @endif

    <form action="{{ action('InstallController@editEnvConfigs') }}" style="display: inline">
        <button type="submit" class="btn btn-default"> {{trans('partials_adminpanel.env')}}</button>
    </form>

    <form action="{{ action('PluginController@index') }}" style="display: inline">
        <button type="submit" class="btn btn-default"> {{trans('partials_adminpanel.plugin')}} </button>
    </form>

    <form action="{{ action('UpdateController@index') }}" style="display: inline">
        <button type="submit" class="btn btn-default"> {{trans('partials_adminpanel.update')}} </button>
    </form>

    <form action="{{ action('ExodusController@index') }}" style="display: inline">
        <button type="submit" class="btn btn-default"> {{trans('partials_adminpanel.exodus')}} </button>
    </form>

@endif