<div class="form-group">
    <?php
    if($geolocator==null){
        $value = '';
        $value2 = \App\Http\Controllers\FieldController::getDateList($field);
    }else{
        $value = explode('[!]',$geolocator->locations);
        $value2 = array();
        foreach($value as $val){
            $value2[$val] = $val;
        }
    }
    ?>
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif
    <div class="list_option_form{{$field->flid}}">
        <div>
            {!! Form::select($field->flid.'[]',$value2,$value,
            ['class' => 'form-control list-options'.$field->flid, 'Multiple', 'id' => 'list'.$field->flid]) !!}
            <button type="button" class="btn btn-primary remove_option{{$field->flid}}">Delete</button>
            <button type="button" class="btn btn-primary move_option_up{{$field->flid}}">Up</button>
            <button type="button" class="btn btn-primary move_option_down{{$field->flid}}">Down</button>
        </div>
        <div>
            {!! Form::label($field->flid, 'Description: ') !!}
            <input type="text" class="form-control loc_desc{{$field->flid}}">
        </div>
        <div>
            {!! Form::label($field->flid, 'Type: ') !!}
            {!! Form::select('loc_type', ['LatLon' => 'LatLon','UTM' => 'UTM','Address' => 'Address'], 'LatLon', ['class' => 'form-control loc_type'.$field->flid]) !!}
        </div>
        <div class="latlon_container{{$field->flid}}">
            {!! Form::label($field->flid, 'Latitude: ') !!}
            <input type="number" class="form-control latlon_lat{{$field->flid}}" min=-90 max=90 step=".000001">
            {!! Form::label($field->flid, 'Longitude: ') !!}
            <input type="number" class="form-control latlon_lon{{$field->flid}}" min=-180 max=180 step=".000001">
        </div>
        <div class="utm_container{{$field->flid}}" style="display:none">
            {!! Form::label($field->flid, 'Zone: ') !!}
            <input type="text" class="form-control utm_zone{{$field->flid}}">
            {!! Form::label($field->flid, 'Easting: ') !!}
            <input type="text" class="form-control utm_east{{$field->flid}}">
            {!! Form::label($field->flid, 'Northing: ') !!}
            <input type="text" class="form-control utm_north{{$field->flid}}">
        </div>
        <div class="text_container{{$field->flid}}" style="display:none">
            {!! Form::label($field->flid, 'Address: ') !!}
            <input type="text" class="form-control text_addr{{$field->flid}}">
        </div>
        <div>
            <button type="button" class="btn btn-primary form-control add_geo{{$field->flid}}">Add Location</button>
        </div>
    </div>
</div>

<script>
    $('.list_option_form{{$field->flid}}').on('click', '.remove_option{{$field->flid}}', function(){
        $('option:selected', '#list{{$field->flid}}').remove();
    });
    $('.list_option_form{{$field->flid}}').on('click', '.move_option_up{{$field->flid}}', function(){
        $('#list{{$field->flid}}').find('option:selected').each(function() {
            $(this).insertBefore($(this).prev());
        });
    });
    $('.list_option_form{{$field->flid}}').on('click', '.move_option_down{{$field->flid}}', function(){
        $('#list{{$field->flid}}').find('option:selected').each(function() {
            $(this).insertAfter($(this).next());
        });
    });
    $('.list_option_form{{$field->flid}}').on('change', '.loc_type{{$field->flid}}', function(){
        newType = $('.loc_type{{$field->flid}}').val();
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
    $('.list_option_form{{$field->flid}}').on('click', '.add_geo{{$field->flid}}', function() {
        //clear errors
        $('.latlon_lat{{$field->flid}}').attr('style','');
        $('.latlon_lon{{$field->flid}}').attr('style','');
        $('.utm_zone{{$field->flid}}').attr('style','');
        $('.utm_east{{$field->flid}}').attr('style','');
        $('.utm_north{{$field->flid}}').attr('style','');
        $('.text_addr{{$field->flid}}').attr('style','');
        $('.loc_desc{{$field->flid}}').attr('style','');

        //check to see if description provided
        var desc = $('.loc_desc{{$field->flid}}').val();
        //if blank
        if(desc=='') {
            $('.loc_desc{{$field->flid}}').attr('style','border: 1px solid red;');
            console.log('bad description');
        }else {
            //check what type
            var type = $('.loc_type{{$field->flid}}').val();

            //determine if info is good for that type
            var valid = true;
            if (type == 'LatLon') {
                var lat = $('.latlon_lat{{$field->flid}}').val();
                var lon = $('.latlon_lon{{$field->flid}}').val();

                if (lat == '' | lon == '') {
                    $('.latlon_lat{{$field->flid}}').attr('style','border: 1px solid red;');
                    $('.latlon_lon{{$field->flid}}').attr('style','border: 1px solid red;');
                    valid = false;
                }
            }else if(type == 'UTM'){
                var zone = $('.utm_zone{{$field->flid}}').val();
                var east = $('.utm_east{{$field->flid}}').val();
                var north = $('.utm_north{{$field->flid}}').val();

                if (zone == '' | east == '' | north == '') {
                    $('.utm_zone{{$field->flid}}').attr('style','border: 1px solid red;');
                    $('.utm_east{{$field->flid}}').attr('style','border: 1px solid red;');
                    $('.utm_north{{$field->flid}}').attr('style','border: 1px solid red;');
                    valid = false;
                }
            }else if(type == 'Address'){
                var addr = $('.text_addr{{$field->flid}}').val();

                if(addr == ''){
                    $('.text_addr{{$field->flid}}').attr('style','border: 1px solid red;');
                    valid = false;
                }
            }

            //if still valid
            if (valid) {
                //find info for other loc types
                if (type == 'LatLon') {
                    $.ajax({
                        url: '{{ action('FieldController@geoConvert',['pid' => $field->pid, 'fid' => $field->fid, 'flid' => $field->flid]) }}',
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            lat: lat,
                            lon: lon,
                            type: 'latlon'
                        },
                        success:function(result) {
                            desc = $('.loc_desc{{$field->flid}}').val();
                            result = '[Desc]'+desc+'[Desc]'+result;
                            $('#list{{$field->flid}}').append($("<option/>", {
                                value: result,
                                text: result,
                                selected: ''
                            }));
                            $('.loc_desc{{$field->flid}}').val('');
                        }
                    });
                }else if(type == 'UTM'){
                    $.ajax({
                        url: '{{ action('FieldController@geoConvert',['pid' => $field->pid, 'fid' => $field->fid, 'flid' => $field->flid]) }}',
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            zone: zone,
                            east: east,
                            north: north,
                            type: 'utm'
                        },
                        success: function (result) {
                            desc = $('.loc_desc{{$field->flid}}').val();
                            result = '[Desc]'+desc+'[Desc]'+result;
                            $('#list{{$field->flid}}').append($("<option/>", {
                                value: result,
                                text: result,
                                selected: ''
                            }));
                            $('.loc_desc{{$field->flid}}').val('');
                        }
                    });
                }else if(type == 'Address'){
                    $.ajax({
                        url: '{{ action('FieldController@geoConvert',['pid' => $field->pid, 'fid' => $field->fid, 'flid' => $field->flid]) }}',
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            addr: addr,
                            type: 'geo'
                        },
                        success: function (result) {
                            desc = $('.loc_desc{{$field->flid}}').val();
                            result = '[Desc]'+desc+'[Desc]'+result;
                            $('#list{{$field->flid}}').append($("<option/>", {
                                value: result,
                                text: result,
                                selected: ''
                            }));
                            $('.loc_desc{{$field->flid}}').val('');
                        }
                    });
                }
                $('.latlon_lat{{$field->flid}}').val('');
                $('.latlon_lon{{$field->flid}}').val('');
                $('.utm_zone{{$field->flid}}').val('');
                $('.utm_east{{$field->flid}}').val('');
                $('.utm_north{{$field->flid}}').val('');
                $('.text_addr{{$field->flid}}').val('');
            } else {
                //error and break
                console.log('invalid');
            }
        }
    });
</script>