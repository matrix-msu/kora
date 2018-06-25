<div class="form-group mt-xl">
    {!! Form::label($field->flid.'_input[]',$field->name) !!}
    {!! Form::select( $field->flid . "_input[]", \App\MultiSelectListField::getList($field, false), '', ["class" => "multi-select", "Multiple"]) !!}
</div>