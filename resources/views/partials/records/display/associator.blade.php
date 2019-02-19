@foreach($typedField->processDisplayData($field, $value) as $kid)
    <div class="associator card">
        {!! $typedField->getPreviewValues($field,$kid) !!}
    </div>
@endforeach