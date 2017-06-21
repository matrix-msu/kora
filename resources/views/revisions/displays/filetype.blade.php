<span><b>{{$data['name']}}:</b>
    @foreach(explode('[!]', $data['data']) as $file)
        @if($file != '')
            <div>{{explode('[Name]', $file)[1]}}</div>
        @else
            <br/>
        @endif
    @endforeach
</span>