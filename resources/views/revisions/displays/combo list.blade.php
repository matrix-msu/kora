<span><b>{{$data['name']}}:</b> </span>

<div style="overflow: auto">
    <span style="float:left;width:50%;margin-bottom:10px"><b>{{$data['data']['name_1']}}</b></span>
    <span style="float:left;width:50%;margin-bottom:10px"><b>{{$data['data']['name_2']}}</b></span>
    <?php $options = $data['data']['options']; ?>
    @for($i = 0; $i < count($options); $i++)
        <span style="float:left;width:50%;margin-bottom:10px">
        @foreach(explode('[!]', explode('[!f1!]', $options[$i])[1]) as $val)
            <div>{{$val}}</div>
        @endforeach
        </span>
        <span style="float:left;width:50%;margin-bottom:10px">
        @foreach(explode('[!]', explode('[!f2!]', $options[$i])[1]) as $val)
            <div>{{$val}}</div>
        @endforeach
        </span>
        <br/>
    @endfor
</div>