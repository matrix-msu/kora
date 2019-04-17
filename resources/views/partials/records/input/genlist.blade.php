@php
    if($editRecord) {
        $listValues = json_decode($record->{$flid});
        $mainValues = [];
        if($listValues != null) {
            foreach($listValues as $val) {
                $mainValues[$val] = $val;
            }
        }
    } else {
        $listValues = $field['default'];
        $mainValues = App\KoraFields\GeneratedListField::getList($field);
    }
@endphp
<div class="form-group mt-xxxl">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
    {!! Form::select($flid.'[]', $mainValues, $listValues, ['class' => 'multi-select modify-select preset-clear-chosen-js', 'multiple',
        'id' => 'list'.$flid, 'data-placeholder' => 'Select Some Options or Type a New Option and Press Enter']) !!}
</div>
