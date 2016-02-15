@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['OptionController@updateGeolocator', $field->pid, $field->fid, $field->flid], 'onsubmit' => 'selectAll()']) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_geolocator.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>

    <div class="list_option_form">
        <div>
            {!! Form::label('default',trans('fields_options_geolocator.def').': ') !!}
            <select multiple class="form-control list_options" name="default[]">
                @foreach(\App\GeolocatorField::getLocationList($field) as $opt)
                    <option value="{{$opt}}">{{$opt}}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-primary remove_option">{{trans('fields_options_geolocator.delete')}}</button>
            <button type="button" class="btn btn-primary move_option_up">{{trans('fields_options_geolocator.up')}}</button>
            <button type="button" class="btn btn-primary move_option_down">{{trans('fields_options_geolocator.down')}}</button>
        </div>
        <div>
            {!! Form::label($field->flid, trans('fields_options_geolocator.desc').': ') !!}
            <input type="text" class="form-control loc_desc">
        </div>
        <div>
            {!! Form::label($field->flid, trans('fields_options_geolocator.type').': ') !!}
            {!! Form::select('loc_type', ['LatLon' => 'LatLon','UTM' => 'UTM','Address' => trans('fields_options_geolocator.addr')], 'LatLon', ['class' => 'form-control loc_type']) !!}
        </div>
        <div class="latlon_container">
            {!! Form::label($field->flid, trans('fields_options_geolocator.lat').': ') !!}
            <input type="number" class="form-control latlon_lat" min=-90 max=90 step=".000001">
            {!! Form::label($field->flid, trans('fields_options_geolocator.lon').': ') !!}
            <input type="number" class="form-control latlon_lon" min=-180 max=180 step=".000001">
        </div>
        <div class="utm_container" style="display:none">
            {!! Form::label($field->flid, trans('fields_options_geolocator.zone').': ') !!}
            <input type="text" class="form-control utm_zone">
            {!! Form::label($field->flid, trans('fields_options_geolocator.east').': ') !!}
            <input type="text" class="form-control utm_east">
            {!! Form::label($field->flid, trans('fields_options_geolocator.north').': ') !!}
            <input type="text" class="form-control utm_north">
        </div>
        <div class="text_container" style="display:none">
            {!! Form::label($field->flid, trans('fields_options_geolocator.addr').': ') !!}
            <input type="text" class="form-control text_addr">
        </div>
        <div>
            <button type="button" class="btn btn-primary add_geo">{{trans('fields_options_geolocator.adddef')}}</button>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('map',trans('fields_options_geolocator.map').': ') !!}
        {!! Form::select('map', ['No' => trans('fields_options_geolocator.no'),'Yes' => trans('fields_options_geolocator.yes')], \App\Http\Controllers\FieldController::getFieldOption($field,'Map'), ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('view',trans('fields_options_geolocator.data').': ') !!}
        {!! Form::select('view', ['LatLon' => 'Lat Long','UTM' => 'UTM Coordinates','Textual' => trans('fields_options_geolocator.text')], \App\Http\Controllers\FieldController::getFieldOption($field,'DataView'), ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit(trans('field_options_generic.submit',['field'=>$field->name]),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    @include('errors.list')

    @include('partials.option_preset')

@stop

@section('footer')
    <script>
        $('#default').select2();

        $('.list_option_form').on('click', '.remove_option', function(){
            $('option:selected', '.list_options').remove();
        });
        $('.list_option_form').on('click', '.move_option_up', function(){
            $('.list_options').find('option:selected').each(function() {
                $(this).insertBefore($(this).prev());
            });
        });
        $('.list_option_form').on('click', '.move_option_down', function(){
            $('.list_options').find('option:selected').each(function() {
                $(this).insertAfter($(this).next());
            });
        });
        $('.list_option_form').on('change', '.loc_type', function(){
            newType = $('.loc_type').val();
            if(newType=='LatLon'){
                $('.latlon_container').show();
                $('.utm_container').hide();
                $('.text_container').hide();
            }else if(newType=='UTM'){
                $('.latlon_container').hide();
                $('.utm_container').show();
                $('.text_container').hide();
            }else if(newType=='Address'){
                $('.latlon_container').hide();
                $('.utm_container').hide();
                $('.text_container').show();
            }
        });
        $('.list_option_form').on('click', '.add_geo', function() {
            //clear errors
            $('.latlon_lat').attr('style','');
            $('.latlon_lon').attr('style','');
            $('.utm_zone').attr('style','');
            $('.utm_east').attr('style','');
            $('.utm_north').attr('style','');
            $('.text_addr').attr('style','');
            $('.loc_desc').attr('style','');

            //check to see if description provided
            var desc = $('.loc_desc').val();
            //if blank
            if(desc=='') {
                $('.loc_desc').attr('style','border: 1px solid red;');
                console.log('bad description');
            }else {
                //check what type
                var type = $('.loc_type').val();

                //determine if info is good for that type
                var valid = true;
                if (type == 'LatLon') {
                    var lat = $('.latlon_lat').val();
                    var lon = $('.latlon_lon').val();

                    if (lat == '' | lon == '') {
                        $('.latlon_lat').attr('style','border: 1px solid red;');
                        $('.latlon_lon').attr('style','border: 1px solid red;');
                        valid = false;
                    }
                }else if(type == 'UTM'){
                    var zone = $('.utm_zone').val();
                    var east = $('.utm_east').val();
                    var north = $('.utm_north').val();

                    if (zone == '' | east == '' | north == '') {
                        $('.utm_zone').attr('style','border: 1px solid red;');
                        $('.utm_east').attr('style','border: 1px solid red;');
                        $('.utm_north').attr('style','border: 1px solid red;');
                        valid = false;
                    }
                }else if(type == 'Address'){
                    var addr = $('.text_addr').val();

                    if(addr == ''){
                        $('.text_addr').attr('style','border: 1px solid red;');
                        valid = false;
                    }
                }

                //if still valid
                if (valid) {
                    //find info for other loc types
                    if (type == 'LatLon') {
                        latLonConvert(lat,lon);
                    }else if(type == 'UTM'){
                        utmConvert(zone,east,north);
                    }else if(type == 'Address'){
                        addrConvert(addr);
                    }
                    $('.latlon_lat').val('');
                    $('.latlon_lon').val('');
                    $('.utm_zone').val('');
                    $('.utm_east').val('');
                    $('.utm_north').val('');
                    $('.text_addr').val('');
                } else {
                    console.log('invalid');
                }
            }
        });

        function latLonConvert(lat,lon){
            $.ajax({
                url: '{{ action('FieldAjaxController@geoConvert',['pid' => $field->pid, 'fid' => $field->fid, 'flid' => $field->flid]) }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    lat: lat,
                    lon: lon,
                    type: 'latlon'
                },
                success:function(result) {
                    desc = $('.loc_desc').val();
                    result = '[Desc]'+desc+'[Desc]'+result;
                    $('.list_options').append($("<option/>", {
                        value: result,
                        text: result
                    }));
                    $('.loc_desc').val('');
                }
            });
        }

        function utmConvert(zone,east,north){
            $.ajax({
                url: '{{ action('FieldAjaxController@geoConvert',['pid' => $field->pid, 'fid' => $field->fid, 'flid' => $field->flid]) }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    zone: zone,
                    east: east,
                    north: north,
                    type: 'utm'
                },
                success: function (result) {
                    desc = $('.loc_desc').val();
                    result = '[Desc]'+desc+'[Desc]'+result;
                    $('.list_options').append($("<option/>", {
                        value: result,
                        text: result
                    }));
                    $('.loc_desc').val('');
                }
            });
        }

        function addrConvert(addr){
            $.ajax({
                url: '{{ action('FieldAjaxController@geoConvert',['pid' => $field->pid, 'fid' => $field->fid, 'flid' => $field->flid]) }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    addr: addr,
                    type: 'geo'
                },
                success: function (result) {
                    desc = $('.loc_desc').val();
                    result = '[Desc]'+desc+'[Desc]'+result;
                    $('.list_options').append($("<option/>", {
                        value: result,
                        text: result
                    }));
                    $('.loc_desc').val('');
                }
            });
        }

        function selectAll(){
            selectBox = $('.list_options > option').each(function(){
                $(this).attr('selected', 'selected');
            });
        }
    </script>
@stop