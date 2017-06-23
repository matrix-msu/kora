@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['OptionController@updateDate', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_date.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('searchable',trans('fields_options_date.search').': ') !!}
        {!! Form::select('searchable',['false', 'true'], $field->searchable, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('extsearch',trans('fields_options_date.extsearch').': ') !!}
        {!! Form::select('extsearch',['false', 'true'], $field->extsearch, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('viewable',trans('fields_options_date.viewable').': ') !!}
        {!! Form::select('viewable',['false', 'true'], $field->viewable, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('viewresults',trans('fields_options_date.viewresults').': ') !!}
        {!! Form::select('viewresults',['false', 'true'], $field->viewresults, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('extview',trans('fields_options_date.extview').': ') !!}
        {!! Form::select('extview',['false', 'true'], $field->extview, ['class' => 'form-control']) !!}
    </div>

    <hr>

    <div class="form-group">
        {!! Form::label('default_month','Default '.trans('fields_options_date.month').': ') !!}
        {!! Form::select('default_month',['' => '', '0' => 'Current Month',
            '1' => '01 - '.trans('fields_options_date.jan'), '2' => '02 - '.trans('fields_options_date.feb'),
            '3' => '03 - '.trans('fields_options_date.mar'), '4' => '04 - '.trans('fields_options_date.apr'),
            '5' => '05 - '.trans('fields_options_date.may'), '6' => '06 - '.trans('fields_options_date.june'),
            '7' => '07 - '.trans('fields_options_date.july'), '8' => '08 - '.trans('fields_options_date.aug'),
            '9' => '09 - '.trans('fields_options_date.sep'), '10' => '10 - '.trans('fields_options_date.oct'),
            '11' => '11 - '.trans('fields_options_date.nov'), '12' => '12 - '.trans('fields_options_date.dec')],
            ($field->default=='' ? null : explode('[M]',$field->default)[1]), ['class' => 'form-control']) !!}
        {!! Form::label('default_day','Default '.trans('fields_options_date.day').': ') !!}
        <select name="default_day" class="form-control">
            <option value=""></option>
            <?php
                if($field->default!='' && explode('[D]',$field->default)[1]=='0'){
                    echo "<option value=" . 0 . " selected>Current Day</option>";
                }else{
                    echo "<option value=" . 0 . ">Current Day</option>";
                }
                $i = 1;
                while ($i <= 31)
                {
                    if($field->default!='' && explode('[D]',$field->default)[1]==$i){
                        echo "<option value=" . $i . " selected>" . $i . "</option>";
                    }else{
                        echo "<option value=" . $i . ">" . $i . "</option>";
                    }
                    $i++;
                }
            ?>
        </select>
        {!! Form::label('default_year','Default '.trans('fields_options_date.year').': ') !!}
        <select name="default_year" class="form-control" id="default_year">
            <option value=""></option>
            <?php
            if($field->default!='' && explode('[Y]',$field->default)[1]=='0'){
                echo "<option value=" . 0 . " selected>Current Year</option>";
            }else{
                echo "<option value=" . 0 . ">Current Year</option>";
            }
            $i = \App\Http\Controllers\FieldController::getFieldOption($field, 'Start');
            $j = \App\Http\Controllers\FieldController::getFieldOption($field, 'End');
            while ($i <= $j)
            {
                if($field->default!='' && explode('[Y]',$field->default)[1]==$i){
                    echo "<option value=" . $i . " selected>" . $i . "</option>";
                }else{
                    echo "<option value=" . $i . ">" . $i . "</option>";
                }
                $i++;
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        {!! Form::label('format',trans('fields_options_date.format').': ') !!}
        {!! Form::select('format', ['MMDDYYYY' => 'MM DD, YYYY','DDMMYYYY' => 'DD MM YYYY','YYYYMMDD' => 'YYYY MM DD'], \App\Http\Controllers\FieldController::getFieldOption($field,'Format'), ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('start',trans('fields_options_date.startyear').': ') !!}
        {!! Form::input('number', 'start', \App\Http\Controllers\FieldController::getFieldOption($field,'Start'), ['class' => 'form-control', 'id' => 'start']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('end',trans('fields_options_date.endyear').': ') !!}
        {!! Form::input('number', 'end', \App\Http\Controllers\FieldController::getFieldOption($field,'End'), ['class' => 'form-control', 'id' => 'end']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('circa',trans('fields_options_date.circa').': ') !!}
        {!! Form::select('circa', ['No' => trans('fields_options_date.no'),'Yes' => trans('fields_options_date.yes')], \App\Http\Controllers\FieldController::getFieldOption($field,'Era'), ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('era',trans('fields_options_date.era').': ') !!}
        {!! Form::select('era', ['No' => trans('fields_options_date.no'),'Yes' => trans('fields_options_date.yes')], \App\Http\Controllers\FieldController::getFieldOption($field,'Era'), ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit(trans('field_options_generic.submit',['field'=>$field->name]),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    @include('errors.list')
@stop

@section('footer')
    <script>
        $('.form-group').on('change', '#start', function(){
            printYears();
        });

        $('.form-group').on('change', '#end', function(){
            printYears();
        });

        function printYears(){
            start = $('#start').val();
            end = $('#end').val();

            if(start==''){
                start = 0;
            }
            if(end ==''){
                end = 9999;
            }
            select = $('#default_year');

            val = '<option></option>';

            console.log(start);
            console.log(end);

            for(var i=start;i<+end+1;i++){
                val += "<option value=" + i + ">" + i + "</option>";
                console.log(i);
            }

            select.html(val);
        }
    </script>
@stop