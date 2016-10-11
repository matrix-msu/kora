<div class="panel panel-default">
    <div class="panel-heading">
        <div class="checkbox">
            <label style="font-size:1.25em;"><input type="checkbox" name="{{$field->flid}}_dropdown"> {{$field->name}}</label>
        </div>
    </div>
    <div id="input_collapse_{{$field->flid}}" style="display: none;">
        <div class="panel-body">
            <label for="{{$field->flid}}_input">Filename: </label>
            <input class="form-control" type="text" name="{{$field->flid}}_input">
            <label for="{{$field->flid}}_extension">Search with file extension:</label>
            <input type="checkbox" name="{{$field->flid}}_extension">
        </div>
    </div>
</div>