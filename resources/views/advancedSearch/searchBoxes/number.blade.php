<div class="panel panel-default">
    <div class="panel-heading">
        <div class="checkbox">
            <label style="font-size:1.25em;"><input type="checkbox" name="{{$field->flid}}_dropdown"> {{$field->name}}</label>
        </div>
    </div>
    <div id="input_collapse_{{$field->flid}}" style="display: none;">
        <div class="panel-body">
            <div class="form-inline">
                <input class="form-control" type="number" id="{{$field->flid}}_left" name="{{$field->flid}}_left" placeholder="{{trans('advanced_search.left_index')}}"> :
                <input class="form-control" type="number" id="{{$field->flid}}_right" name="{{$field->flid}}_right" placeholder="{{trans('advanced_search.right_index')}}">
                {{trans('advanced_search.invert')}}: <input id="{{$field->flid}}_invert" type="checkbox" name="{{$field->flid}}_invert">
            </div>
            <div style="margin-top: 1em" id="{{$field->flid}}_info">
                {{trans('advanced_search.interval_text')}}}: <span id="{{$field->flid}}_interval">{{trans('advanced_search.invalid')}}</span>
            </div>
        </div>
        <input type="hidden" id="{{$field->flid}}_valid" name="{{$field->flid}}_valid" value="0">
    </div>
</div>

@include("advancedSearch.searchBoxes.number-validation", ["prefix" => $field->flid])
