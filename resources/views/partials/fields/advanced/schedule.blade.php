{!! Form::hidden('advanced',true) !!}
<div class="form-group schedule-form-group schedule-form-group-js mt-xxxl">
    {!! Form::label('default','Default Events') !!}
    <div class="form-input-container">
        <p class="directions">Add Default Events below, and order them via drag & drop or their arrow icons.</p>

        <div class="schedule-card-container schedule-card-container-js mb-xxl"></div>

        <section class="new-object-button">
            <input class="add-new-default-event-js" type="button" value="Create New Default Event">
        </section>
    </div>
</div>

<div class="form-group half mt-xl pr-sm">
    {!! Form::label('start','Start Year') !!}
    <span class="error-message"></span>
    <div class="number-input-container number-input-container-js">
        {!! Form::input('number', 'start', null, ['class' => 'text-input start-year-js', 'placeholder' => 'Enter start year here', 'min' => 0, 'max' => 9999]) !!}
    </div>
</div>

<div class="form-group half mt-xl pl-sm">
    {!! Form::label('end','End Year') !!}
    <span class="error-message"></span>
    <div class="number-input-container number-input-container-js">
        {!! Form::input('number', 'end', null, ['class' => 'text-input end-year-js', 'placeholder' => 'Enter end year here', 'min' => 0, 'max' => 9999]) !!}
    </div>
</div>

<div class="form-group mt-xl">
    {!! Form::label('cal','Calendar Display') !!}
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