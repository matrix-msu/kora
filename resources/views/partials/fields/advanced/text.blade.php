{!! Form::hidden('advanced',true) !!}
<div class="form-group mt-xxxl single-line-js">
    {!! Form::label('default','Default') !!}
    <span class="error-message single-line"></span>
    {!! Form::text('default', null, ['class' => 'text-input text-default-js', 'placeholder' => 'Enter default value here']) !!}
</div>

<div class="form-group mt-xxxl multi-line-js hidden">
    {!! Form::label('default','Default') !!}
    <span class="error-message multi-line"></span>
    {!! Form::textarea('default', null, ['class' => 'text-area text-area-default text-area-default-js', 'placeholder' => "Enter default value here", 'disabled' => 'disabled']) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('regex','Regex') !!}
    <span class="error-message"></span>
    {!! Form::text('regex', null, ['class' => 'text-input text-regex-js', 'placeholder' => 'Enter regular expression pattern here']) !!}
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