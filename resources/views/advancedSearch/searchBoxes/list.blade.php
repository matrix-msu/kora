<div class="panel panel-default">
    <div class="panel-heading">
        <div class="checkbox">
            <label style="font-size:1.25em;"><input type="checkbox" name="{{$field->flid}}_dropdown"> {{$field->name}}</label>
        </div>
    </div>
    <div id="input_collapse_{{$field->flid}}" style="display: none;">
        <div class="panel-body">
            <label for="{{$field->flid}}_input">Select option:</label></br>
            {!! Form::select( $field->flid . "_input", \App\ListField::getList($field, true), $field->default, ["class" => "form-control", "style" => "width: 100%"]) !!}
            Input is: <span id="{{$field->flid}}_valid_selection">invalid</span>.
        </div>
    </div>
    <input type="hidden" id="{{$field->flid}}_valid" name="{{$field->flid}}_valid" value="0">
</div>
<script>
    $("[name={{$field->flid}}_input]").select2();
    $("[name={{$field->flid}}_input]").change(function() {
       if (this.value == "") {
           $("#{{$field->flid}}_valid_selection").html("invalid");
           $("#{{$field->flid}}_valid").val("0");
       }
       else {
           $("#{{$field->flid}}_valid_selection").html("valid");
           $("#{{$field->flid}}_valid").val("1");
       }
    });
</script>