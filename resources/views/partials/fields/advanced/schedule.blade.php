<div class="form-group mt-xl">
    {!! Form::label('default','Default Value: ') !!}
    <select multiple class="multi-select default-event-js" name="default[]"
        data-placeholder="Add Events Below"></select>
</div>

<div class="form-group mt-xl">
    <a href="#" class="btn half-sub-btn extend add-new-default-event-js">Create New Default Event</a>
</div>

<div class="form-group mt-xl">
    {!! Form::label('start','Start Year: ') !!}
    {!! Form::input('number', 'start', 1900, ['class' => 'text-input', 'min' => 0, 'max' => 9999]) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('end','End Year: ') !!}
    {!! Form::input('number', 'end', 2020, ['class' => 'text-input', 'min' => 0, 'max' => 9999]) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('cal','Calendar Display: ') !!}
    {!! Form::select('cal', ['No' => 'No','Yes' => 'Yes'], 'No', ['class' => 'single-select']) !!}
</div>

<script>
    jQuery('.event-start-time-js').datetimepicker({
        format:'m/d/Y g:i A', inline:true, lang:'en', step: 15,
        minDate:'1900/01/01',
        maxDate:'2020/12/31'
    });

    jQuery('.event-end-time-js').datetimepicker({
        format:'m/d/Y g:i A', inline:true, lang:'en', step: 15,
        minDate:'1900/01/01',
        maxDate:'2020/12/31'
    });

    Kora.Fields.Options('Schedule');
</script>