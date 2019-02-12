<div class="form-group mt-xl">
    {!! Form::label($flid.'_input',$field['name']) !!}
    {!! Form::select($flid . "_input", $field['options'], '', ["class" => "single-select"]) !!}
</div>
