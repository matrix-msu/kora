<div class="form-group">
    <?php
        if($text==null){
            $value = '';
        }else{
            $value = $text->text;
        }
    ?>
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif
    @if(\App\Http\Controllers\FieldController::getFieldOption($field,'MultiLine')==0)
        {!! Form::text($field->flid, $value, ['class' => 'form-control']) !!}
    @endif
    @if(\App\Http\Controllers\FieldController::getFieldOption($field,'MultiLine')==1)
        {!! Form::textarea($field->flid, $value, ['class' => 'form-control']) !!}
    @endif
</div>