@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $pid])
    @include('partials.menu.form', ['pid' => $pid, 'fid' => $fid])
@stop

@section("content")

<span><h1>{{trans('advanced_search.title')}}</h1></span>
<form method="POST" name="advanced_search" action="{{action("AdvancedSearchController@search", ["pid" => $pid, "fid" => $fid])}}">
@foreach($fields as $field)
    @if($field->searchable)
        @include("advancedSearch.searchBoxes." . strtolower($field->type), $field)
    @endif
@endforeach
    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <input type="submit" id="advanced_submit" value="{{trans('advanced_search.search_btn')}}" class="btn btn-primary form-control" disabled>
</form>
@stop

@section("footer")
<script>
    var formSelector = $("[name=advanced_search]");

    // If the form is being auto-completed, we should slide down the collapsers that are checked.
    $(window).bind("pageshow", function() {

        validateForm(formSelector);

        $("[name$=dropdown]").each(function() {
            var flid = this.name.split("_")[0];
            var collapser = $("#input_collapse_" + flid);
            var checker = $("[name=" + flid + "_dropdown]");

            if (checker.is(":checked")) {
                collapser.slideDown();
            }
        });
    });

    // Allow for slide down on checkboxes.
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
    // Make sure that all the search fields that are in use are also valid.
    formSelector.on("change keyup paste", function() {
        validateForm(this);
    });

    function validateForm(name) {
        var that = name;
        var formValid = true;
        var checked_dropdowns = [];
        $("[name$=dropdown]").each(function() {
            if(this.checked) {
                checked_dropdowns.push(this)
            }
        });

        if (checked_dropdowns.length == 0) {
            formValid = false;
        }

        checked_dropdowns.forEach(function(entry) {
            var flid = entry.name.split("_")[0];

            // Gets all elements that start with the flid and end with "valid".
            $("[name^=" + flid + "]").filter("[name$=valid]").each(function() {
                if (this.value == "0") {
                    var first = $("[name=" + flid + "_1_valid]");
                    var second = $("[name=" + flid + "_2_valid]");

                    if (this.name == first.attr("name") && second.val() == "0") {
                        // This combo selector is invalid, and so is the other one.
                        formValid = false;
                    }
                    else if (this.name == second.attr("name") && first.val() == "0") {
                        formValid = false;
                    }
                    else if (this.name != second.attr("name") && this.name != first.attr("name")){
                        // This is not a combo list field, so we mark invalid as usual.
                        formValid = false;
                    }
                }
            });
        });

        if (formValid) {
            $("#advanced_submit").prop("disabled", false);
        }
        else {
            $("#advanced_submit").prop("disabled", true);
        }
    }
</script>
@stop