@php
    $type_1 = $field['one']['type'];
    $type_2 = $field['two']['type'];
    $title_1 = $field['one']['name'];
    $title_2 = $field['two']['name'];
@endphp

<div class="form-group mt-xl">
    {!! Form::label($flid,$field['alt_name']!='' ? $field['name'].' ('.$field['alt_name'].')' : $field['name']) !!}
</div>
<div class="record-data-card">
    @include('partials.records.advanced.combo-sub', ['cftype' => $type_1, 'cftitle' => $title_1, 'cfnum' => 'one'])
    @include('partials.records.advanced.combo-sub', ['cftype' => $type_2, 'cftitle' => $title_2, 'cfnum' => 'two'])
</div>
