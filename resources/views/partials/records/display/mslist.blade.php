@foreach(explode('[!]',$typedField->options) as $opt)
    <div>{{ $opt }}</div>
@endforeach