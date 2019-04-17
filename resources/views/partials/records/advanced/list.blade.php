<div class="form-group mt-xl">
    {!! Form::label($flid.'_input',$field['name']) !!}
    {!! Form::select($flid . "_input", [null=>'']+\App\KoraFields\ListField::getList($field), null, ["class" => "single-select"]) !!}
</div>
