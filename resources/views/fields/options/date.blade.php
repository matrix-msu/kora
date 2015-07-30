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

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateDefault', $field->pid, $field->fid, $field->flid], 'class' => 'form-inline']) !!}
    @include('fields.options.hiddens')
    <div class="form-group">
        {!! Form::label('default_month','Month: ') !!}
        {!! Form::select('default_month',['' => '','1' => '01 - January', '2' => '02 - February',
            '3' => '03 - March', '4' => '04 - April', '5' => '05 - May', '6' => '06 - June',
            '7' => '07 - July', '8' => '08 - August', '9' => '09 - September',
            '10' => '10 - October', '11' => '11 - November', '12' => '12 - December'],
            explode('[M]',$field->default)[1], ['class' => 'form-control']) !!}
        {!! Form::label('default_day','Day: ') !!}
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
        {!! Form::label('default_year','Year: ') !!}
        <select name="default_year" class="form-control">
            <option value=""></option>
            <?php
            $i = \App\Http\Controllers\FieldController::getFieldOption($field, 'Start');
            $j = \App\Http\Controllers\FieldController::getFieldOption($field, 'End');
            while ($i <= $j+1)
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
        {!! Form::submit("Update Default",['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','Format') !!}
    <div class="form-group">
        {!! Form::label('value','Format: ') !!}
        {!! Form::select('value', ['MMDDYYYY' => 'MM DD, YYYY','DDMMYYYY' => 'DD MM YYYY','YYYYMMDD' => 'YYYY MM DD'], \App\Http\Controllers\FieldController::getFieldOption($field,'Format'), ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit("Update Format",['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','Start') !!}
    <div class="form-group">
        {!! Form::label('value','Start Year: ') !!}
        {!! Form::input('number', 'value', \App\Http\Controllers\FieldController::getFieldOption($field,'Start'), ['class' => 'form-control']) !!}
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
        {!! Form::input('number', 'value', \App\Http\Controllers\FieldController::getFieldOption($field,'End'), ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit("Update End Year",['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','Circa') !!}
    <div class="form-group">
        {!! Form::label('value','Allow Circa Approximations: ') !!}
        {!! Form::select('value', ['No' => 'No','Yes' => 'Yes'], \App\Http\Controllers\FieldController::getFieldOption($field,'Era'), ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit("Update Circa",['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    {!! Form::model($field,  ['method' => 'PATCH', 'action' => ['FieldController@updateOptions', $field->pid, $field->fid, $field->flid]]) !!}
    @include('fields.options.hiddens')
    {!! Form::hidden('option','Era') !!}
    <div class="form-group">
        {!! Form::label('value','Show Era: ') !!}
        {!! Form::select('value', ['No' => 'No','Yes' => 'Yes'], \App\Http\Controllers\FieldController::getFieldOption($field,'Era'), ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::submit("Update Format",['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}

    @include('errors.list')
@stop