<div class="form-group mt-xl">
    {!! Form::label($field->flid.'_input',$field->name.': ') !!}
    {!! Form::text($field->flid.'_input', null, ['class' => 'text-input', 'placeholder' => 'Enter search text']) !!}
</div>