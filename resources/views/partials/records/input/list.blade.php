@php
    if(isset($seq)) { //Combo List
        $fieldLabel = '';
        $fieldDivID = 'default_'.$seq.'_'.$flid;
        $listValue = null;
    } else if($editRecord) {
        $fieldLabel = $flid;
        $fieldDivID = 'list'.$flid;
        $listValue = $record->{$flid};
    } else {
        $fieldLabel = $flid;
        $fieldDivID = 'list'.$flid;
        $listValue = $field['default'];
    }
@endphp
<div class="form-group mt-xxxl">
    <label>@if(!isset($seq) && $field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
    {!! Form::select($fieldLabel, [null=>'']+\App\KoraFields\ListField::getList($field), $listValue,
        ['class' => 'single-select preset-clear-chosen-js', 'id' => $fieldDivID]) !!}
</div>
