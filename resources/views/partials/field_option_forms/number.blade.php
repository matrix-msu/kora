<hr>
{!! Form::hidden('advance','true') !!}

<div class="form-group">
    {!! Form::label('default',trans('fields_options_number.def').': ') !!}
    <input
            type="number" name="default" class="form-control" value="1" id="default"
            step="1"
            min="1"
            max="10">
</div>

<div class="form-group">
    {!! Form::label('min',trans('fields_options_number.min').': ') !!}
    <input
            type="number" name="min" class="form-control" step="any" id="min"
            value="1"
            max="10">
</div>

<div class="form-group">
    {!! Form::label('max',trans('fields_options_number.max').': ') !!}
    <input
            type="number" name="max" class="form-control" step="any" id="max"
            value="10"
            min="1">
</div>

<div class="form-group">
    {!! Form::label('inc',trans('fields_options_number.inc').': ') !!}
    <input
            type="number" name="inc" class="form-control" step="any" id="inc"
            value="1">
</div>

<div class="form-group">
    {!! Form::label('unit',trans('fields_options_number.unit').': ') !!}
    {!! Form::text('unit', '', ['class' => 'form-control']) !!}
</div>

<script>
    $('.form-group').on('change', '#min', function(){
        $('#default').attr('min',$(this).val());
        $('#max').attr('min',$(this).val());
    });

    $('.form-group').on('change', '#max', function(){
        $('#default').attr('max',$(this).val());
        $('#min').attr('max',$(this).val());
    });

    $('.form-group').on('change', '#inc', function(){
        $('#default').attr('step',$(this).val());
    });
</script>