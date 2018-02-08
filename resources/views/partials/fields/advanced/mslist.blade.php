<div class="form-group mt-xl">
    {!! Form::label('default','Default : ') !!}
    {!! Form::select('default[]', [], null, ['class' => 'multi-select mslist-default-js', 'multiple']) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('options','List Options: ') !!}
    <select multiple class="multi-select modify-select mslist-options-js" name="options[]"
        data-placeholder="Select or Add Some Options"></select>
</div>

<script>
    Kora.Fields.Options('Multi-Select List');
</script>