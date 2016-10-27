<div class="panel panel-default">
    <div class="panel-heading">
        <div class="checkbox">
            <label style="font-size:1.25em;"><input type="checkbox" name="{{$field->flid}}_dropdown"> {{$field->name}}</label>
        </div>
    </div>
    <div id="input_collapse_{{$field->flid}}" style="display: none;">
        <div class="panel-body">
            <div>
                {!! Form::label($field->flid."_type", trans('records_fieldInput.type').': ') !!}
                {!! Form::select('loc_type', ['LatLon' => 'LatLon','UTM' => 'UTM','Address' => trans('records_fieldInput.addr')], 'LatLon', ['id' => $field->flid . "_type",'class' => 'form-control loc_type'.$field->flid, "name" => $field->flid."_type"]) !!}
            </div>
            <div class="latlon_container{{$field->flid}}">
                {!! Form::label($field->flid . "_lat", trans('records_fieldInput.lat').': ') !!}
                <input type="number" class="form-control latlon_lat{{$field->flid}}" name="{{$field->flid}}_lat" min=-90 max=90 step=".000001">
                {!! Form::label($field->flid . "_lon", trans('records_fieldInput.lon').': ') !!}
                <input type="number" class="form-control latlon_lon{{$field->flid}}" name="{{$field->flid}}_lon" min=-180 max=180 step=".000001">
            </div>
            <div class="utm_container{{$field->flid}}" style="display:none">
                {!! Form::label($field->flid . "_zone", trans('records_fieldInput.zone').': ') !!}
                <input type="text" class="form-control utm_zone{{$field->flid}}" name="{{$field->flid}}_zone">
                {!! Form::label($field->flid . "_east", trans('records_fieldInput.east').': ') !!}
                <input type="text" class="form-control utm_east{{$field->flid}}" name="{{$field->flid}}_east">
                {!! Form::label($field->flid . "_north", trans('records_fieldInput.north').': ') !!}
                <input type="text" class="form-control utm_north{{$field->flid}}" name="{{$field->flid}}_north">
            </div>
            <div class="text_container{{$field->flid}}" style="display:none">
                {!! Form::label($field->flid . "_address", trans('records_fieldInput.addr').': ') !!}
                <input type="text" class="form-control text_addr{{$field->flid}}" name="{{$field->flid}}_address">
            </div>
            <label for="{{$field->flid}}_range">Range (in kilometers):</label>
            <input type="number" class="form-control" name="{{$field->flid}}_range">
            Input is: <span id="{{$field->flid}}_valid_text">invalid</span>.
        </div>
        <input type="hidden" id="{{$field->flid}}_valid" name="{{$field->flid}}_valid" value="0">
    </div>
</div>

<script>
    $("#{{$field->flid}}_type").on('change', function(){
        var newType = $('#{{$field->flid}}_type').val();

        if(newType=='LatLon'){
            $('.latlon_container{{$field->flid}}').show();
            $('.utm_container{{$field->flid}}').hide();
            $('.text_container{{$field->flid}}').hide();
        }else if(newType=='UTM'){
            $('.latlon_container{{$field->flid}}').hide();
            $('.utm_container{{$field->flid}}').show();
            $('.text_container{{$field->flid}}').hide();
        }else if(newType=='Address'){
            $('.latlon_container{{$field->flid}}').hide();
            $('.utm_container{{$field->flid}}').hide();
            $('.text_container{{$field->flid}}').show();
        }
        validate_{{$field->flid}}(newType);
    });

    $("[name={{$field->flid}}_range]").keyup(function() {validate_{{$field->flid}}($('#{{$field->flid}}_type').val())});
    $("[name={{$field->flid}}_lat]").keyup(function() {validate_{{$field->flid}}("LatLon")});
    $("[name={{$field->flid}}_lon]").keyup(function() {validate_{{$field->flid}}("LatLon")});
    $("[name={{$field->flid}}_zone]").keyup(function() {validate_{{$field->flid}}("UTM")});
    $("[name={{$field->flid}}_east]").keyup(function() {validate_{{$field->flid}}("UTM")});
    $("[name={{$field->flid}}_north]").keyup(function() {validate_{{$field->flid}}("UTM")});
    $("[name={{$field->flid}}_address]").keyup(function() {validate_{{$field->flid}}("Address")});

    function validate_{{$field->flid}}(type) {
        var valid = true;

        var range = $("[name={{$field->flid}}_range]").val();
        if (range == "" || parseInt(range) < 0) {
            // Search range cannot be negative.
            valid = false;
        }
        else if (type == "LatLon") {
            // Latitude in [-90, 90] and Longitude in [-180, 180].
            var lat = parseInt($("[name={{$field->flid}}_lat]").val());
            var lon = parseInt($("[name={{$field->flid}}_lon]").val());

            valid = (lat >= -90 && lat <= 90) && (lon >= -180 && lon <= 180);
        }
        else if (type == "UTM") {
            // Make sure zone is non-empty and east/northing values are nonnegative.
            var zone = $("[name={{$field->flid}}_zone]").val();
            var easting = parseInt($("[name={{$field->flid}}_east]").val());
            var northing = parseInt($("[name={{$field->flid}}_north]").val());

            valid = (zone != "") && (easting >= 0) && (northing >= 0);
        }
        else {
            // Address is only invalid if it is empty.
            valid = $("[name={{$field->flid}}_address]").val() != "";
        }

        if (valid) {
            $("#{{$field->flid}}_valid_text").html("valid");
            $("#{{$field->flid}}_valid").val("1")
        }
        else {
            $("#{{$field->flid}}_valid_text").html("invalid");
            $("#{{$field->flid}}_valid").val("0");
        }
    }
</script>