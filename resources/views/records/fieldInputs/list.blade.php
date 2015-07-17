<div class="form-group">
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif
    {!! Form::select($field->flid,\App\Http\Controllers\FieldController::getList($field,true), $field->default,['class' => 'form-control', 'id' => 'default']) !!}
</div>