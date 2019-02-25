<?php
    $type_1 = \App\ComboListField::getComboFieldType($field, 'one');
    $type_2 = \App\ComboListField::getComboFieldType($field, 'two');
    $title_1 = \App\ComboListField::getComboFieldName($field, 'one');
    $title_2 = \App\ComboListField::getComboFieldName($field, 'two');
?>

<div class="form-group mt-xl">
    {!! Form::label($field->flid,$field->name) !!}
</div>
<div class="record-data-card">
    @include('partials.records.advanced.combo-sub', ['cftype' => $type_1, 'cftitle' => $title_1, 'cfnum' => 'one'])
    @include('partials.records.advanced.combo-sub', ['cftype' => $type_2, 'cftitle' => $title_2, 'cfnum' => 'two'])
</div>
