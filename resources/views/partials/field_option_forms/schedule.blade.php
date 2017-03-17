<hr>
{!! Form::hidden('advance','true') !!}

<div class="list_option_form">
    <div>
        {!! Form::label('default',trans('fields_options_schedule.def').': ') !!}
        <select multiple class="form-control list_options" name="default[]" style="overflow:auto">
        </select>
        <button type="button" class="btn btn-primary remove_option">{{trans('fields_options_schedule.delete')}}</button>
        <button type="button" class="btn btn-primary move_option_up">{{trans('fields_options_schedule.up')}}</button>
        <button type="button" class="btn btn-primary move_option_down">{{trans('fields_options_schedule.down')}}</button>
    </div>
    <div class="form-inline" style="position:relative">
        {!! Form::label('eventname',trans('fields_options_schedule.event').': ') !!}
        <input type="text" class="form-control" id="eventname" maxlength="24"/>
        {!! Form::label('startdatetime',trans('fields_options_schedule.start').': ') !!}
        <input type='text' class="form-control" id='startdatetime' />
        {!! Form::label('enddatetime',trans('fields_options_schedule.end').': ') !!}
        <input type='text' class="form-control" id='enddatetime' />
        {!! Form::label('allday',trans('fields_options_schedule.allday').': ') !!}
        <input type='checkbox' class="form-control" id='allday' />
        <button type="button" class="btn btn-primary add_option">Add</button>
    </div>
</div>

{!! Form::hidden('option','Start') !!}
<div class="form-group">
    {!! Form::label('start',trans('fields_options_schedule.startyear').': ') !!}
    {!! Form::input('number', 'start', 0, ['class' => 'form-control', 'min' => 0, 'max' => 9999]) !!}
</div>

{!! Form::hidden('option','End') !!}
<div class="form-group">
    {!! Form::label('end',trans('fields_options_schedule.endyear').': ') !!}
    {!! Form::input('number', 'end', 9999, ['class' => 'form-control', 'min' => 0, 'max' => 9999]) !!}
</div>

{!! Form::hidden('option','Calendar') !!}
<div class="form-group">
    {!! Form::label('cal',trans('fields_options_schedule.calendar').': ') !!}
    {!! Form::select('cal', ['No' => trans('fields_options_schedule.no'),'Yes' => trans('fields_options_schedule.yes')], 'No', ['class' => 'form-control']) !!}
</div>

<script>
    $('#startdatetime').datetimepicker({
        minDate:'0',
        maxDate:'9999'
    });
    $('#enddatetime').datetimepicker({
        minDate:'0',
        maxDate:'9999'
    });

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
                }
            }
        }
    });

    function selectAll(){
        selectBox = $('.list_options > option').each(function(){
            $(this).attr('selected', 'selected');
        });
    }
</script>