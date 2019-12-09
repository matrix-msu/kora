@php
$oneName = $field['one']['name'];
$twoName = $field['two']['name'];
$oneType = $field['one']['type'];
$twoType = $field['two']['type'];
$oneFlid = $field['one']['flid'];
$twoFlid = $field['two']['flid'];

$items = $typedField->retrieve($flid, $form->id, $value);
@endphp
<div class="combo-list-display">
    <div>
        <span class="combo-column combo-title">{{$oneName}}</span>
        <span class="combo-column combo-title">{{$twoName}}</span>
    </div>
    <div>
        <span class="combo-border-large"> </span>
    </div>
    @for($i=0;$i<sizeof($items);$i++)
        <div>
            @php
                $valueOne = $items[$i]->{$oneFlid};
                $valueTwo = $items[$i]->{$twoFlid};
            @endphp

            @if($i!=0)
                <span class="combo-border-small"> </span>
            @endif

            @if($oneType=='Text' | $oneType=='Date' | $oneType=='List' | $oneType=='Boolean')
                @php
                    if($oneType=='Boolean') {
                        if($valueOne == 1)
                            $valueOne = 'true';
                        else if($valueOne == 0)
                            $valueOne = 'false';
                    }
                @endphp
                <span class="combo-column">{{ $valueOne }}</span>
            @elseif($oneType=='Historical Date')
                @php
                    $valueOne = json_decode($valueOne, true);
                    $date = implode(
                        '-',
                        array_filter([$valueOne['year'], $valueOne['month'], $valueOne['day']])
                    );
                @endphp
                <span class="combo-column">{{ $date }}</span>
            @elseif($oneType=='Integer' | $oneType=='Float')
                @php
                $unit = App\KoraFields\ComboListField::getComboFieldOption($field,'Unit','one');
                if($unit!=null && $unit!='')
                    $valueOne .= ' '.$unit;
                @endphp
                <span class="combo-column">{{$valueOne}}</span>
            @elseif($oneType=='Multi-Select List' | $oneType=='Generated List')
                <span class="combo-column">
                    @foreach(json_decode($valueOne) as $val)
                        <div>{{$val}}</div>
                    @endforeach
                </span>
            @elseif($oneType=='Associator')
                <span class="combo-column">
                    @foreach(json_decode($valueOne) as $val)
                        <div class="associator card">
                            {!! \App\KoraFields\AssociatorField::getPreviewValues($field['one'],$val) !!}
                        </div>
                    @endforeach
                </span>
            @endif

            @if($twoType=='Text' | $twoType=='Date' | $twoType=='List' | $twoType=='Boolean')
                @php
                    if($twoType=='Boolean') {
                        if($valueTwo == 1)
                            $valueTwo = 'true';
                        else if($valueTwo == 0)
                            $valueTwo = 'false';
                    }
                @endphp
                <span class="combo-column">{{ $valueTwo }}</span>
            @elseif($twoType=='Historical Date')
                @php
                    $valueTwo = json_decode($valueTwo, true);
                    $date = implode(
                        '-',
                        array_filter([$valueTwo['year'], $valueTwo['month'], $valueTwo['day']])
                    );
                @endphp
                <span class="combo-column">{{ $date }}</span>
            @elseif($twoType=='Integer' | $twoType=='Float')
                @php
                $unit = App\KoraFields\ComboListField::getComboFieldOption($field,'Unit','two');
                if($unit!=null && $unit!=''){
                    $valueTwo .= ' '.$unit;
                }
                @endphp
                <span class="combo-column">{{$valueTwo}}</span>
            @elseif($twoType=='Multi-Select List' | $twoType=='Generated List')
                <span class="combo-column">
                    @foreach(json_decode($valueTwo) as $val)
                        <div>{{$val}}</div>
                    @endforeach
                </span>
            @elseif($twoType=='Associator')
                <span class="combo-column">
                    @foreach(json_decode($valueTwo) as $val)
                        <div class="associator card">
                            {!! \App\KoraFields\AssociatorField::getPreviewValues($field['two'],$val) !!}
                        </div>
                    @endforeach
                </span>
            @endif
        </div>
    @endfor
</div>
