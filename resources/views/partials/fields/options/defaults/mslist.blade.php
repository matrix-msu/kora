@php
    if(isset($seq)) { //Combo List
        $seq = '_' . $seq;
        $title = $cfName;
        $default = null;
        $defClass = 'default-input-js';
    } else {
        $seq = '';
        $title = 'Default';
        $default = $field['default'];
        $defClass = '';
    }
@endphp
<div class="form-group mt-xxxl">
    {!! Form::label('default' . $seq, $title) !!}
    {!! Form::select('default'.$seq.'[]', App\KoraFields\MultiSelectListField::getList($field), $default,
    ['class' => 'multi-select list-default-js '.$defClass, 'multiple', 'data-placeholder' => 'Select the default values here (Values must be added above in order to select)', 'id'=>'default'.$seq]) !!}
</div>