<div class="form-group">
    <?php
    if($schedule==null){
        $value = '';
        $value2 = \App\Http\Controllers\FieldController::getDateList($field);
    }else{
        $value = explode('[!]',$schedule->events);
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
        <div class="form-inline" style="position:relative">
            {!! Form::label('eventname'.$field->flid,'Event Title: ') !!}
            <input type="text" class="form-control" id="eventname{{$field->flid}}" />
            {!! Form::label('startdatetime'.$field->flid,'Start: ') !!}
            <input type='text' class="form-control" id='startdatetime{{$field->flid}}' />
            {!! Form::label('enddatetime'.$field->flid,'End: ') !!}
            <input type='text' class="form-control" id='enddatetime{{$field->flid}}' />
            <button type="button" class="btn btn-primary add_option{{$field->flid}}">Add</button>
        </div>
    </div>
</div>

@section('footer')
    <script>
        $('#startdatetime{{$field->flid}}').datetimepicker({
            minDate:'{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'Start') }}',
            maxDate:'{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'End') }}'
        });
        $('#enddatetime{{$field->flid}}').datetimepicker({
            minDate:'{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'Start') }}',
            maxDate:'{{ \App\Http\Controllers\FieldController::getFieldOption($field, 'End') }}'
        });

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
        $('.list_option_form{{$field->flid}}').on('click', '.add_option{{$field->flid}}', function() {
            name = $('#eventname{{$field->flid}}').val().trim();
            sTime = $('#startdatetime{{$field->flid}}').val().trim();
            eTime = $('#enddatetime{{$field->flid}}').val().trim();

            val = name+': '+sTime+' - '+eTime;

            if(val != ''){
                $('#list{{$field->flid}}').append($("<option/>", {
                    value: val,
                    text: val,
                    selected: 'selected'
                }));
                $('#eventname{{$field->flid}}').val('');
                $('#startdatetime{{$field->flid}}').val('');
                $('#enddatetime{{$field->flid}}').val('');
            }
        });
    </script>
@stop