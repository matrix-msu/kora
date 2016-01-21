@extends('fields.show')

@section('fieldOptions')

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateRequired', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('required',trans('fields_options_date.req').': ') !!}
        {!! Form::select('required',['false', 'true'], $field->required, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_date.updatereq'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateDefault', $field->pid, $field->fid, $field->flid], 'class' => 'form-inline']) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('default_month',trans('fields_options_date.month').': ') !!}
        {!! Form::select('default_month',['' => '',
            '1' => '01 - '.trans('fields_options_date.jan'), '2' => '02 - '.trans('fields_options_date.feb'),
            '3' => '03 - '.trans('fields_options_date.mar'), '4' => '04 - '.trans('fields_options_date.apr'),
            '5' => '05 - '.trans('fields_options_date.may'), '6' => '06 - '.trans('fields_options_date.june'),
            '7' => '07 - '.trans('fields_options_date.july'), '8' => '08 - '.trans('fields_options_date.aug'),
            '9' => '09 - '.trans('fields_options_date.sep'), '10' => '10 - '.trans('fields_options_date.oct'),
            '11' => '11 - '.trans('fields_options_date.nov'), '12' => '12 - '.trans('fields_options_date.dec')],
            explode('[M]',$field->default)[1], ['class' => 'form-control']) !!}
        {!! Form::label('default_day',trans('fields_options_date.day').': ') !!}
        <select name="default_day" class="form-control">
            <option value=""></option>
            <?php
                $i = 1;
                while ($i <= 31)
                {
                    if(explode('[D]',$field->default)[1]==$i){
                        echo "<option value=" . $i . " selected>" . $i . "</option>";
                    }else{
                        echo "<option value=" . $i . ">" . $i . "</option>";
                    }
                    $i++;
                }
            ?>
        </select>
        {!! Form::label('default_year',trans('fields_options_date.year').': ') !!}
        <select name="default_year" class="form-control">
            <option value=""></option>
            <?php
            $i = \App\Http\Controllers\FieldController::getFieldOption($field, 'Start');
            $j = \App\Http\Controllers\FieldController::getFieldOption($field, 'End');
            while ($i <= $j)
            {
                if(explode('[Y]',$field->default)[1]==$i){
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
        {!! Form::submit(trans('fields_options_date.updatedef'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','Format') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_date.format').': ') !!}
        {!! Form::select('value', ['MMDDYYYY' => 'MM DD, YYYY','DDMMYYYY' => 'DD MM YYYY','YYYYMMDD' => 'YYYY MM DD'], \App\Http\Controllers\FieldController::getFieldOption($field,'Format'), ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_date.updatefor'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','Start') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_date.startyear').': ') !!}
        {!! Form::input('number', 'value', \App\Http\Controllers\FieldController::getFieldOption($field,'Start'), ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_date.updatestart'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','End') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_date.endyear').': ') !!}
        {!! Form::input('number', 'value', \App\Http\Controllers\FieldController::getFieldOption($field,'End'), ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_date.updateend'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','Circa') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_date.circa').': ') !!}
        {!! Form::select('value', ['No' => trans('fields_options_date.no'),'Yes' => trans('fields_options_date.yes')], \App\Http\Controllers\FieldController::getFieldOption($field,'Era'), ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_date.updatecirca'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','Era') !!}
    <div class="form-group">
        {!! Form::label('value',trans('fields_options_date.era').': ') !!}
        {!! Form::select('value', ['No' => trans('fields_options_date.no'),'Yes' => trans('fields_options_date.yes')], \App\Http\Controllers\FieldController::getFieldOption($field,'Era'), ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit(trans('fields_options_date.updateera'),['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    @include('errors.list')
@stop