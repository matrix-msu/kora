@php
    if(isset($seq)) { //Combo List
        $fieldLabel = '';
        $fieldDivID = 'default_'.$seq.'_'.$flid;
        $listValues = null;
    } else if($editRecord) {
        $fieldLabel = $flid.'[]';
        $fieldDivID = 'list'.$flid;
        $listValues = json_decode($record->{$flid});
    } else {
        $fieldLabel = $flid.'[]';
        $fieldDivID = 'list'.$flid;
        $listValues = $field['default'];
    }
@endphp
<div class="form-group mt-xxxl">
    <label>@if(!isset($seq) && $field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
    {!! Form::select($fieldLabel, App\KoraFields\MultiSelectListField::getList($field), $listValues,
        ['class' => 'multi-select preset-clear-chosen-js', 'Multiple', 'id' => $fieldDivID]) !!}
</div>
