<span><b>{{$data['name']}}:</b>
    @foreach(is_null($data['data']) ? [] : $data['data'] as $event)
        <div>{{$event}}</div>
    @endforeach
</span><br/>