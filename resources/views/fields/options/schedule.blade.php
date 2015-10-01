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
        <div class="form-inline" style="position:relative">
            {!! Form::label('eventname','Event Title: ') !!}
            <input type="text" class="form-control" id="eventname" />
            {!! Form::label('startdatetime','Start: ') !!}
            <input type='text' class="form-control" id='startdatetime' />
            {!! Form::label('enddatetime','End: ') !!}
            <input type='text' class="form-control" id='enddatetime' />
            {!! Form::label('allday','All Day: ') !!}
            <input type='checkbox' class="form-control" id='allday' />
            <button class="btn btn-primary add_option">Add</button>
        </div>
    </div>

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','Start') !!}
    <div class="form-group">
        {!! Form::label('value','Start Year: ') !!}
        {!! Form::input('number', 'value', \App\Http\Controllers\FieldController::getFieldOption($field,'Start'), ['class' => 'form-control', 'min' => 0, 'max' => 9999]) !!}
    </div>
    <div class="form-group">
        {!! Form::submit("Update Start Year",['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','End') !!}
    <div class="form-group">
        {!! Form::label('value','End Year: ') !!}
        {!! Form::input('number', 'value', \App\Http\Controllers\FieldController::getFieldOption($field,'End'), ['class' => 'form-control', 'min' => 0, 'max' => 9999]) !!}
    </div>
    <div class="form-group">
        {!! Form::submit("Update End Year",['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','Calendar') !!}
    <div class="form-group">
        {!! Form::label('value','Calendar View: ') !!}
        {!! Form::select('value', ['No' => 'No','Yes' => 'Yes'], \App\Http\Controllers\FieldController::getFieldOption($field,'Calendar'), ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit("Update Calendar View",['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    @include('errors.list')

    @include('partials.option_preset')
@stop

@section('footer')
    <script>
        $('#startdatetime').datetimepicker({
            minDate:'{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'Start') }}',
            maxDate:'{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'End') }}'
        });
        $('#enddatetime').datetimepicker({
            minDate:'{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'Start') }}',
            maxDate:'{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'End') }}'
        });

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
        $('.list_option_form').on('click', '.add_option', function() {
            name = $('#eventname').val().trim();
            sTime = $('#startdatetime').val().trim();
            eTime = $('#enddatetime').val().trim();

            $('#eventname').css({ "border": ''});
            $('#startdatetime').css({ "border": ''});
            $('#enddatetime').css({ "border": ''});

            if(name==''|sTime==''|eTime==''){
                //show error
                if(name=='')
                    $('#eventname').css({ "border": '#FF0000 1px solid'});
                if(sTime=='')
                    $('#startdatetime').css({ "border": '#FF0000 1px solid'});
                if(eTime=='')
                    $('#enddatetime').css({ "border": '#FF0000 1px solid'});
            }else{
                if($('#allday').is(":checked")){
                    sTime = sTime.split(" ")[0];
                    eTime = eTime.split(" ")[0];
                }

                if(sTime>eTime){
                    $('#startdatetime').css({ "border": '#FF0000 1px solid'});
                    $('#enddatetime').css({ "border": '#FF0000 1px solid'});
                }else {

                    val = name + ': ' + sTime + ' - ' + eTime;

                    if (val != '') {
                        $('.list_options').append($("<option/>", {
                            value: val,
                            text: val
                        }));
                        $('#eventname').val('');
                        $('#startdatetime').val('');
                        $('#enddatetime').val('');
                        SaveList();
                    }
                }
            }
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