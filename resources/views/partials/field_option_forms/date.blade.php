<hr>
{!! Form::hidden('advance','true') !!}

<div class="form-group">
    {!! Form::label('default_month','Default '.trans('fields_options_date.month').': ') !!}
    {!! Form::select('default_month',['' => '',
        '1' => '01 - '.trans('fields_options_date.jan'), '2' => '02 - '.trans('fields_options_date.feb'),
        '3' => '03 - '.trans('fields_options_date.mar'), '4' => '04 - '.trans('fields_options_date.apr'),
        '5' => '05 - '.trans('fields_options_date.may'), '6' => '06 - '.trans('fields_options_date.june'),
        '7' => '07 - '.trans('fields_options_date.july'), '8' => '08 - '.trans('fields_options_date.aug'),
        '9' => '09 - '.trans('fields_options_date.sep'), '10' => '10 - '.trans('fields_options_date.oct'),
        '11' => '11 - '.trans('fields_options_date.nov'), '12' => '12 - '.trans('fields_options_date.dec')],
        '', ['class' => 'form-control']) !!}
    {!! Form::label('default_day','Default '.trans('fields_options_date.day').': ') !!}
    <select name="default_day" class="form-control">
        <option value=""></option>
        <?php
        $i = 1;
        while ($i <= 31)
        {
            echo "<option value=" . $i . ">" . $i . "</option>";
            $i++;
        }
        ?>
    </select>
    {!! Form::label('default_year','Default '.trans('fields_options_date.year').': ') !!}
    <select name="default_year" class="form-control" id="default_year">
        <option value=""></option>
        <?php
        $i = 0;
        $j = 9999;
        while ($i <= $j)
        {
            echo "<option value=" . $i . ">" . $i . "</option>";
            $i++;
        }
        ?>
    </select>
</div>

<div class="form-group">
    {!! Form::label('format',trans('fields_options_date.format').': ') !!}
    {!! Form::select('format', ['MMDDYYYY' => 'MM DD, YYYY','DDMMYYYY' => 'DD MM YYYY','YYYYMMDD' => 'YYYY MM DD'],'MMDDYYYY', ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('start',trans('fields_options_date.startyear').': ') !!}
    {!! Form::input('number', 'start', 0, ['class' => 'form-control', 'id' => 'start']) !!}
</div>

<div class="form-group">
    {!! Form::label('end',trans('fields_options_date.endyear').': ') !!}
    {!! Form::input('number', 'end', 9999, ['class' => 'form-control', 'id' => 'end']) !!}
</div>

<div class="form-group">
    {!! Form::label('circa',trans('fields_options_date.circa').': ') !!}
    {!! Form::select('circa', ['No' => trans('fields_options_date.no'),'Yes' => trans('fields_options_date.yes')], 'No', ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('era',trans('fields_options_date.era').': ') !!}
    {!! Form::select('era', ['No' => trans('fields_options_date.no'),'Yes' => trans('fields_options_date.yes')], 'No', ['class' => 'form-control']) !!}
</div>

<script>
    $('.form-group').on('change', '#start', function(){
        printYears();
    });

    $('.form-group').on('change', '#end', function(){
        printYears();
    });

    function printYears(){
        start = $('#start').val();
        end = $('#end').val();

        if(start==''){
            start = 0;
        }
        if(end ==''){
            end = 9999;
        }
        select = $('#default_year');

        val = '<option></option>';

        console.log(start);
        console.log(end);

        for(var i=start;i<+end+1;i++){
            val += "<option value=" + i + ">" + i + "</option>";
            console.log(i);
        }

        select.html(val);
    }
</script>