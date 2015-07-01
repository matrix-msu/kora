@if($layout['up'])
    <button onclick="moveFieldUp({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-15.png" alt="Move Up"></button>
@elseif($layout['down'])
    <button onclick="moveFieldDown({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-14.png" alt="Move Down"></button>
@elseif($layout['upIn'])
    <button onclick="moveFieldUpIn({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-12.png" alt="Move Up and In"></button>
@elseif($layout['downIn'])
    <button onclick="moveFieldDownIn({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-13.png" alt="Move Down and In"></button>
@elseif($layout['upOut'])
    <button onclick="moveFieldUpOut({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-10.png" alt="Move Up and Out"></button>
@elseif($layout['downOut'])
    <button onclick="moveFieldDownOut({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-11.png" alt="Move Down and Out"></button>
@endif