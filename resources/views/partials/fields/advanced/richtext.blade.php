{!! Form::hidden('advanced',true) !!}
<div class="form-group mt-xxxl">
    {!! Form::label('default','Default') !!}
    {!! Form::text('default', null, ['class' => 'text-input', 'placeholder' => 'Enter default value here']) !!}
</div>

<script>
    Kora.Fields.Options('Rich Text');
</script>