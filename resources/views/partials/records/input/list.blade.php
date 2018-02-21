<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>
    {!! Form::select($field->flid,\App\ListField::getList($field,true), $field->default,
        ['class' => 'single-select', 'id' => 'list'.$field->flid]) !!}
</div>