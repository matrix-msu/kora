@foreach($typedField->records()->get() as $opt)
    <div class="associator card">
        {!! $typedField->getPreviewValues($opt->record) !!}
    </div>
@endforeach