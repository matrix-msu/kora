<div class="form-group mt-xxxl">
    {!! Form::label('default','Default: ') !!}
    {!! Form::text('default', null, ['class' => 'text-input', 'placeholder' => 'Enter default value here']) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('regex','Regex: ') !!}
    {!! Form::text('regex', null, ['class' => 'text-input', 'placeholder' => 'Enter regular expression pattern here']) !!}
</div>

<div class="form-group mt-xl">
    <label for="multi">Multilined?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="multi" />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Select to set the field as multilined</span>
        <span class="placeholder-alt">Field is set to be multilined</span>
    </div>
</div>

<script>
    Kora.Fields.Options('Text');
</script>