<div class="panel panel-default">
    <div class="panel-heading">
        <div class="checkbox">
            <label style="font-size:1.25em;"><input type="checkbox" name="{{$field->flid}}_dropdown"> {{$field->name}}</label>
            <label style="font-size:1.25em;float: right"><input type="checkbox" name="{{$field->flid}}_negative">Negative (returns records that do not meet this search)</label>
        </div>
    </div>
    <div id="input_collapse_{{$field->flid}}" style="display: none;">
        <div class="panel-body">
            <label for="{{$field->flid}}_input"> {{trans('advanced_search.search_text')}}: </label>
            <input class="form-control" type="text" name="{{$field->flid}}_input">
            {{trans('advanced_search.input_text')}}: <span id="{{$field->flid}}_valid_text">{{trans('advanced_search.invalid')}}</span>.
        </div>
        <input type="hidden" id="{{$field->flid}}_valid" name="{{$field->flid}}_valid" value="0">
    </div>
</div>
<script>
    $("[name={{$field->flid}}_input]").keyup(function() {
        if (this.value != "") {
            $("#{{$field->flid}}_valid_text").html("{{trans('advanced_search.valid')}}");
            $("#{{$field->flid}}_valid").val("1")
        }
        else {
            $("#{{$field->flid}}_valid_text").html("{{trans('advanced_search.invalid')}}");
            $("#{{$field->flid}}_valid").val("0");
        }
    });
</script>

