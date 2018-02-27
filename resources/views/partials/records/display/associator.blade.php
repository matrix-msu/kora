@foreach($af->records()->get() as $opt)
    <div>{!! $af->getPreviewValues($opt->record) !!}</div>
@endforeach