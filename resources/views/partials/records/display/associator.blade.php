@foreach($typedField->records()->get() as $opt)
    <div>{!! $typedField->getPreviewValues($opt->record) !!}</div>
@endforeach