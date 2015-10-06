@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateRequired', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required','Required: ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit("Update Required",['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    <div class="list_option_form">
        <div>
            {!! Form::label('default','Default: ') !!}
            <select multiple class="form-control list_options">
                @foreach(\App\Http\Controllers\FieldController::getDateList($field) as $opt)
                    <option value="{{$opt}}">{{$opt}}</option>
                @endforeach
            </select>
            <button class="btn btn-primary remove_option">Delete</button>
            <button class="btn btn-primary move_option_up">Up</button>
            <button class="btn btn-primary move_option_down">Down</button>
        </div>
        <div>
            {!! Form::label($field->flid, 'Description: ') !!}
            <input type="text" class="form-control loc_desc">
        </div>
        <div>
            {!! Form::label($field->flid, 'Type: ') !!}
            {!! Form::select('loc_type', ['LatLon' => 'LatLon','UTM' => 'UTM','Address' => 'Address'], 'LatLon', ['class' => 'form-control loc_type']) !!}
        </div>
        <div class="latlon_container">
            {!! Form::label($field->flid, 'Latitude: ') !!}
            <input type="number" class="form-control latlon_lat" min=-90 max=90 step=".000001">
            {!! Form::label($field->flid, 'Longitude: ') !!}
            <input type="number" class="form-control latlon_lon" min=-180 max=180 step=".000001">
        </div>
        <div class="utm_container" style="display:none">
            {!! Form::label($field->flid, 'Zone: ') !!}
            <input type="text" class="form-control utm_zone">
            {!! Form::label($field->flid, 'Easting: ') !!}
            <input type="text" class="form-control utm_east">
            {!! Form::label($field->flid, 'Northing: ') !!}
            <input type="text" class="form-control utm_north">
        </div>
        <div class="text_container" style="display:none">
            {!! Form::label($field->flid, 'Address: ') !!}
            <input type="text" class="form-control text_addr">
        </div>
        <div>
            <button class="btn btn-primary form-control add_geo">Add to Default</button>
        </div>
    </div>

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','Map') !!}
    <div class="form-group">
        {!! Form::label('value','Map View: ') !!}
        {!! Form::select('value', ['No' => 'No','Yes' => 'Yes'], \App\Http\Controllers\FieldController::getFieldOption($field,'Map'), ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit("Update Map View",['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','DataView') !!}
    <div class="form-group">
        {!! Form::label('value','Data View: ') !!}
        {!! Form::select('value', ['LatLon' => 'Lat Long','UTM' => 'UTM Coordinates','Textual' => 'Textual'], \App\Http\Controllers\FieldController::getFieldOption($field,'DataView'), ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit("Update Data View",['class' => 'btn btn-primary form-control']) !!}
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
            SaveList();
        });
        $('.list_option_form').on('click', '.move_option_up', function(){
            $('.list_options').find('option:selected').each(function() {
                $(this).insertBefore($(this).prev());
            });
            SaveList();
        });
        $('.list_option_form').on('click', '.move_option_down', function(){
            $('.list_options').find('option:selected').each(function() {
                $(this).insertAfter($(this).next());
            });
            SaveList();
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
                url: '{{ action('FieldController@geoConvert',['pid' => $field->pid, 'fid' => $field->fid, 'flid' => $field->flid]) }}',
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
                    SaveList();
                }
            });
        }

        function utmConvert(zone,east,north){
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
                    desc = $('.loc_desc').val();
                    result = '[Desc]'+desc+'[Desc]'+result;
                    $('.list_options').append($("<option/>", {
                        value: result,
                        text: result
                    }));
                    $('.loc_desc').val('');
                    SaveList();
                }
            });
        }

        function addrConvert(addr){
            $.ajax({
                url: '{{ action('FieldController@geoConvert',['pid' => $field->pid, 'fid' => $field->fid, 'flid' => $field->flid]) }}',
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
                    SaveList();
                }
            });
        }

        function SaveList() {
            options = new Array();
            $(".list_options option").each(function(){
                options.push($(this).val());
            });

            $.ajax({
                url: '{{ action('FieldController@saveDateList',['pid' => $field->pid, 'fid' => $field->fid, 'flid' => $field->flid]) }}',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    action: 'SaveDateList',
                    options: options
                },
                success: function (result) {
                    //location.reload();
                }
            });
        }
    </script>
@stop