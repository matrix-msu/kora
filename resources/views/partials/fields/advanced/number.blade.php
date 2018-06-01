{!! Form::hidden('advanced',true) !!}
<div class="form-group mt-xxxl">
    {!! Form::label('default','Default') !!}
    <span class="error-message"></span>
    <input type="number" name="default" class="text-input number-default-js" value="" placeholder="Enter number here">
</div>

<div class="form-group mt-xl">
    {!! Form::label('min','Minimum Value') !!}
    <span class="error-message"></span>
    <input type="number" name="min" class="text-input number-min-js" step="any" id="min" value="" placeholder="Enter min file size (kb) here">
</div>

<div class="form-group mt-xl">
    {!! Form::label('max','Max Value') !!}
    <span class="error-message"></span>
    <input type="number" name="max" class="text-input number-max-js" step="any" id="max" value="" placeholder="Enter max file size (kb) here">
</div>

<div class="form-group mt-xl">
    {!! Form::label('inc','Value Increment') !!}
    <span class="error-message"></span>
    <input type="number" name="inc" class="text-input number-step-js" step="any" id="inc" value="" placeholder="Enter value increment here">
</div>

<div class="form-group mt-xl">
    {!! Form::label('unit','Unit of Measurement') !!}
    {!! Form::text('unit', null, ['class' => 'text-input', 'placeholder' => 'Enter unit of measurement here']) !!}
</div>

<script>
    Kora.Fields.Options('Number');
</script>