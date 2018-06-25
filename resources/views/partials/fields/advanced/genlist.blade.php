{!! Form::hidden('advanced',true) !!}
<div class="form-group mt-xxxl">
    {!! Form::label('options','List Options') !!}
    <span class="error-message"></span>
    <select multiple class="multi-select modify-select genlist-options-js" name="options[]"
            data-placeholder="Select or Add Some Options"></select>
</div>

<div class="form-group mt-xl">
    {!! Form::label('default','Default') !!}
    {!! Form::select('default[]', [], null, ['class' => 'multi-select genlist-default-js', 'multiple']) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('regex','Regex') !!}
    {!! Form::text('regex', null, ['class' => 'text-input']) !!}
</div>

<script>
    Kora.Fields.Options('Generated List');
</script>