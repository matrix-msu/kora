@php
    $type_1 = $field['one']['type'];
    $type_2 = $field['two']['type'];
    $title_1 = $field['one']['name'];
    $title_2 = $field['two']['name'];

    $advInputOne = $form->getFieldModel($type_1)::FIELD_ADV_INPUT_VIEW;
    $advInputTwo = $form->getFieldModel($type_2)::FIELD_ADV_INPUT_VIEW;
@endphp

<div class="form-group mt-xl">
    {!! Form::label($flid,$field['alt_name']!='' ? $field['name'].' ('.$field['alt_name'].')' : $field['name']) !!}
</div>
<div class="record-data-card">
    @include($advInputOne, ['cTitle' => $title_1, 'seq' => 'one'])
    @include($advInputTwo, ['cTitle' => $title_2, 'seq' => 'two'])
</div>
