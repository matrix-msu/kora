{!! Form::hidden('advanced',true) !!}
<div class="form-group mt-xxxl">
    <div class="number-input-container">
        {!! Form::label('default','Default') !!}
        <span class="error-message"></span>
        <input type="number" name="default" class="text-input number-default-js" value="" placeholder="Enter number here" step="1">
    </div>
</div>

<div class="form-group mt-xl">
    <div class="number-input-container">
        {!! Form::label('min','Minimum Value') !!}
        <span class="error-message"></span>
        <input type="number" name="min" class="text-input number-min-js" id="min" value="" placeholder="Enter minimum value here" step="1">
    </div>
</div>

<div class="form-group mt-xl">
    <div class="number-input-container">
        {!! Form::label('max','Max Value') !!}
        <span class="error-message"></span>
        <input type="number" name="max" class="text-input number-max-js" id="max" value="" placeholder="Enter max value here" step="1">
    </div>
</div>

<div class="form-group mt-xl">
    {!! Form::label('unit','Unit of Measurement') !!}
    {!! Form::text('unit', null, ['class' => 'text-input', 'placeholder' => 'Enter unit of measurement here']) !!}
</div>

<script>
    Kora.Fields.Options('Number');
</script>
