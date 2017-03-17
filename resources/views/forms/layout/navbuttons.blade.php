@if($layout['up'])
    <button onclick="moveFieldUp({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-15.png" alt="Move Up"></button>
@endif
@if($layout['down'])
    <button onclick="moveFieldDown({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-14.png" alt="Move Down"></button>
@endif
@if($layout['upIn'])
    <button onclick="moveFieldUpIn({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-12.png" alt="Move Up and In"></button>
@endif
@if($layout['downIn'])
    <button onclick="moveFieldDownIn({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-13.png" alt="Move Down and In"></button>
@endif
@if($layout['upOut'])
    <button onclick="moveFieldUpOut({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-10.png" alt="Move Up and Out"></button>
@endif
@if($layout['downOut'])
    <button onclick="moveFieldDownOut({{ $field->flid }})"><img src="{{ url() }}/arrows/KoraIII-Logo-11.png" alt="Move Down and Out"></button>
@endif