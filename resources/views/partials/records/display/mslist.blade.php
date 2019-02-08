@foreach($typedField->processDisplayData($field, $value) as $opt)
    <div>{{ $opt }}</div>
@endforeach
