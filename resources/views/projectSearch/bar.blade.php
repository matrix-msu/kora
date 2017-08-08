<?php
if (count($projectArrays) == 1) {
    $url = action('ProjectSearchController@keywordSearch', $projectArrays[0]['pid']);

}
else {
    $url = action('ProjectSearchController@keywordSearch');
}

echo '<form id="project_search" method="GET" action="'. $url .'">';

?>

    <div class="form-group form-inline">
        <label for="query">{{trans('search_bar.search')}} : </label>
        <input class="form-control" name="query" type="text" id="query">

        <select required class="form-control" id="projectSelector" name="forms[]">
            @foreach($projectArrays as $projectArray)
                <optgroup label="{{$projectArray["name"]}}" onclick="alert('hello')">

                @foreach($projectArray["forms"] as $form)
                    <option id="form" value="{{$form["fid"]}}">{{$form["name"]}}</option>
                @endforeach

                </optgroup>
            @endforeach
        </select>

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

<script>
    var select = $("#projectSelector").select2({
        placeholder: "Select Forms",
        multiple: true,
        closeOnSelect: false
    });

    select.select2("val", "");

    $("#project_search").submit(function(e) {$("#search_progress").slideDown(200);});
</script>