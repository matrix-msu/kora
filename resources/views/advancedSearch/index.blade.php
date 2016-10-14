@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $pid])
    @include('partials.menu.form', ['pid' => $pid, 'fid' => $fid])
@stop

@section("content")

<span><h1>Advanced Search</h1></span>
<form method="POST" name="advanced_search" action="{{action("AdvancedSearchController@search", ["pid" => $pid, "fid" => $fid])}}">
@foreach($fields as $field)
    @if($field->searchable)
        @include("advancedSearch.searchBoxes." . strtolower($field->type), $field)
    @endif
@endforeach
    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <input type="submit" value="Search" class="btn btn-primary form-control">
</form>
@stop

@section("footer")
<script>
    $(window).bind("pageshow", function() {
        $("[name$=dropdown]").each(function() {
            var flid = this.name.split("_")[0];
            var collapser = $("#input_collapse_" + flid);
            var checker = $("[name=" + flid + "_dropdown]");

            if (checker.is(":checked")) {
                console.log("is checked");

                collapser.slideDown();
            }
        });
    });

    $("[name$=dropdown]").change(function() {
        var flid = this.name.split("_")[0];
        var collapser = $("#input_collapse_" + flid);
        if (this.checked){
            collapser.slideDown();
        }
        else {
            collapser.slideUp();
        }
    });
</script>
@stop