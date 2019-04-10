<div class="form-group mt-xl">
    {!! Form::label($flid.'_input[]',$field['name']) !!}
    {!! Form::select($flid . "_input[]", App\KoraFields\GeneratedListField::getList($field), '', ["class" => "multi-select modify-select", "Multiple",
        'data-placeholder' => 'Select Some Options or Type a New Option and Press Enter']) !!}
</div>
