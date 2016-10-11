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
        </div>
    </div>
</div>

<script>
    $("#{{$field->flid}}_type").on('change', function(){
        var newType = $('#{{$field->flid}}_type').val();

        console.log(newType);

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
    });
</script>