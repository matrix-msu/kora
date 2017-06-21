<span><b>{{$data['name']}}:</b></span>
@foreach(explode('[!]', $data['data']) as $opt)
    <div>
        {{$opt}}
    </div>
@endforeach