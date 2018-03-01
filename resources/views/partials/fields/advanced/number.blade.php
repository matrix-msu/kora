{!! Form::hidden('advanced',true) !!}
<div class="form-group mt-xxxl">
    {!! Form::label('default','Default: ') !!}
    <input type="number" name="default" class="text-input" value="">
</div>

<div class="form-group mt-xl">
    {!! Form::label('min','Minimum Value: ') !!}
    <input type="number" name="min" class="text-input" step="any" id="min" value="">
</div>

<div class="form-group mt-xl">
    {!! Form::label('max','Max Value: ') !!}
    <input type="number" name="max" class="text-input" step="any" id="max" value="">
</div>

<div class="form-group mt-xl">
    {!! Form::label('inc','Value Increment: ') !!}
    <input type="number" name="inc" class="text-input" step="any" id="inc" value="1">
</div>

<div class="form-group mt-xl">
    {!! Form::label('unit','Unit of Measurement: ') !!}
    {!! Form::text('unit', null, ['class' => 'text-input']) !!}
</div>

<script>
    Kora.Fields.Options('Number');
</script>