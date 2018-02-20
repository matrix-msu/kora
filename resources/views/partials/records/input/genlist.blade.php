<div class="form-group mt-xl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>
    {!! Form::select($field->flid.'[]',\App\GeneratedListField::getList($field,false), explode('[!]',$field->default),
        ['class' => 'multi-select modify-select', 'multiple', 'id' => 'list'.$field->flid]) !!}
</div>