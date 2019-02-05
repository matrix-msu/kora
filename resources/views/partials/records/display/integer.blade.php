@php
$options = $typedField->processDisplayData($field, $value)
@endphp

{{ $options }}

@foreach($options as $opt)
    @if($opt['number'] != '')
    {{ $opt['Unit'] }}
    @endif
@endforeach
