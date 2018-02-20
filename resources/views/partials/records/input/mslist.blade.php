<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>
    {!! Form::select($field->flid.'[]',\App\MultiSelectListField::getList($field,false), explode('[!]',$field->default),
        ['class' => 'multi-select', 'Multiple', 'id' => 'list'.$field->flid]) !!}
</div>