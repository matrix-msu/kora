<div class="form-group mt-xl">
    {!! Form::label('default','Default: ') !!}
    {!! Form::select('default',[], null, ['class' => 'single-select list-default-js']) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('options','List Options: ') !!}
    <select multiple class="multi-select modify-select list-options-js" name="options[]"
        data-placeholder="Select or Add Some Options"></select>
</div>

<script>
    Kora.Fields.Options('List');
</script>