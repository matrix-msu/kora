<form id="search_form" method="GET" action="{{action('FormSearchController@keywordSearch',compact('pid','fid'))}}" >

    <div class="form-group form-inline">
        <label for="query">{{trans('search_bar.search')}} : </label>
        <input class="form-control" name="query" type="text" id="query">

        <select class="form-control" name="method">
            <option value="0">{{trans('search_bar.or')}}</option>
            <option value="1">{{trans('search_bar.and')}}</option>
            <option value="2">{{trans('search_bar.exact')}}</option>
        </select>

        <input class="btn btn-primary form-control" type="submit" value="{{trans('search_bar.search')}}">


    </div>

    <div style="display:none;" id="search_progress" class="progress">
        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
            {{trans('update_index.loading')}}
        </div>
    </div>

</form>

<a class="btn btn-primary" href="{{action("AdvancedSearchController@index", compact("pid", "fid"))}}">Advanced Search</a>

<script>
    $("#search_form").submit(function(e) { $("#search_progress").slideDown(200); });
</script>