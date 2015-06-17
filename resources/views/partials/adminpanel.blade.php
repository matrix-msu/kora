@if ($userId==1)
    <hr/>

    <h4> Admin Panel</h4>
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-default"><a href="{{ action('AdminController@users') }}">Manage Users</a></button>
    </div>
@endif