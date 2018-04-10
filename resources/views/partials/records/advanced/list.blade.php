<div class="form-group mt-xl">
    {!! Form::label($field->flid.'_input',$field->name.': ') !!}
    {!! Form::select( $field->flid . "_input", \App\ListField::getList($field, true), '', ["class" => "single-select"]) !!}
</div>