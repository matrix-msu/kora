@php
    if($editRecord)
        $textValue = $record->{$flid};
    else
        $textValue = $field['default'];
@endphp
<div class="form-group mt-xxxl">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
	
    @if(!$field['options']['MultiLine'])
        {!! Form::text($flid, $textValue, ['class' => 'text-input preset-clear-text-js', 'id' => $flid, 'placeholder' => 'Enter text here']) !!}
    @elseif($field['options']['MultiLine'])
        @php
            $newLineCnt = substr_count($textValue, "\n");
            $taHeight = $newLineCnt*15;
            if($taHeight < 100)
                $taHeight = 100;
        @endphp
        {!! Form::textarea($flid, $textValue, ['class' => 'text-area preset-clear-text-js', 'style' => 'height:'.$taHeight.'px', 'id' => $flid, 'placeholder' => 'Enter text here']) !!}
    @endif
</div>