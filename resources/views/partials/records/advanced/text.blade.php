<div class="form-group mt-xl">
    {!! Form::label($flid.'_input',$field['alt_name']!='' ? $field['name'].' ('.$field['alt_name'].')' : $field['name']) !!}
    {!! Form::text($flid.'_input', null, ['class' => 'text-input', 'placeholder' => 'Enter search text']) !!}
</div>