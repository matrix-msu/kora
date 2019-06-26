<div class="form-group mt-xl">
    {!! Form::label($flid.'_input[]',$field['alt_name']!='' ? $field['name'].' ('.$field['alt_name'].')' : $field['name']) !!}
    {!! Form::select($flid . "_input[]", App\KoraFields\MultiSelectListField::getList($field), '', ["class" => "multi-select", "Multiple"]) !!}
</div>
