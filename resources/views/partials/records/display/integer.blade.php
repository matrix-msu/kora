@php
$number = $typedField->processDisplayData($field, $value);
@endphp

{{ $number }}

@if($number != '')
    {{ $field['options']['Unit'] }}
@endif
