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
        <div class="latlon_container">
            {!! Form::label($field->flid, 'Description: ') !!}
            <span><input type="text" class="latlon_desc"></span>
            {!! Form::label($field->flid, 'Latitude: ') !!}
            <span><input type="number" class="latlon_lat" min=-90 max=90 step=".000001"></span>
            {!! Form::label($field->flid, 'Longitude: ') !!}
            <span><input type="number" class="latlon_lon" min=-180 max=180 step=".000001"></span>
            <span><button class="btn btn-primary add_latlon">Add</button></span>
        </div>
        <div class="utm_container" style="display:none">
            {!! Form::label($field->flid, 'Description: ') !!}
            <span><input type="text" class="utm_desc"></span>
            {!! Form::label($field->flid, 'Zone: ') !!}
            <span><input type="text" class="utm_zone"></span>
            {!! Form::label($field->flid, 'Easting: ') !!}
            <span><input type="text" class="utm_east"></span>
            {!! Form::label($field->flid, 'Northing: ') !!}
            <span><input type="text" class="utm_north"></span>
            <span><button class="btn btn-primary add_utm">Add</button></span>
        </div>
        <div class="text_container" style="display:none">
            {!! Form::label($field->flid, 'Description: ') !!}
            <span><input type="text" class="text_desc"></span>
            {!! Form::label($field->flid, 'Address: ') !!}
            <span><input type="text" class="text_addr"></span>
            <span><button class="btn btn-primary add_text">Add</button></span>
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
        $('.latlon_container').on('click', '.add_latlon', function() {
            desc = $('.latlon_desc').val();
            desc = desc.trim();
            lat = $('.latlon_lat').val();
            lat = lat.trim();
            lon = $('.latlon_lon').val();
            lon = lon.trim();

            if(desc!='' && lat!='' && lon!='') {
                $('.list_options').append($('<option/>', {
                    value: desc + ': ' + lat + ', ' + lon,
                    text: desc + ': ' + lat + ', ' + lon
                }));
                SaveList();
                $('.latlon_desc').val('');
                $('.latlon_lat').val('');
                $('.latlon_lon').val('');
            }
        });
        $('.utm_container').on('click', '.add_utm', function() {
            console.log("utm");
        });
        $('.text_container').on('click', '.add_text', function() {
            console.log("text");
        });

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