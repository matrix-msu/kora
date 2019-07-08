<div class="form-group mt-xl">
    {!! Form::label($flid.'_input',(array_key_exists('alt_name', $field) && $field['alt_name']!='') ? $field['name'].' ('.$field['alt_name'].')' : $field['name']) !!}
    {!! Form::text($flid.'_input', null, ['class' => 'text-input', 'placeholder' => 'Enter search text']) !!}
</div>
