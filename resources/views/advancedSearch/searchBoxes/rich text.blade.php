<div class="panel panel-default">
    <div class="panel-heading">
        <div class="checkbox">
            <label style="font-size:1.25em;"><input type="checkbox" name="{{$field->flid}}_dropdown"> {{$field->name}}</label>
        </div>
    </div>
    <div id="input_collapse_{{$field->flid}}" style="display: none;">
        <div class="panel-body">
            <label for="{{$field->flid}}_input"> Search text: </label>
            <input class="form-control" type="text" name="{{$field->flid}}_input">
            Input is: <span id="{{$field->flid}}_valid_text">invalid</span>.
        </div>
        <input type="hidden" id="{{$field->flid}}_valid" name="{{$field->flid}}_valid" value="0">
    </div>
</div>
<script>
    $("[name={{$field->flid}}_input]").keyup(function() {
        if (this.value != "") {
            $("#{{$field->flid}}_valid_text").html("valid");
            $("#{{$field->flid}}_valid").val("1")
        }
        else {
            $("#{{$field->flid}}_valid_text").html("invalid");
            $("#{{$field->flid}}_valid").val("0");
        }
    });
</script>

