@php
    if(isset($seq)) { //Combo List
        $fieldLabel = 'default_'.$seq;
        $fieldDivID = 'default_'.$seq.'_'.$flid;
        $textValue = null;
    } else if($editRecord) {
        $fieldLabel = $flid;
        $fieldDivID = $flid;
        $textValue = $record->{$flid};
    } else {
        $fieldLabel = $flid;
        $fieldDivID = $flid;
        $textValue = $field['default'];
    }
@endphp
<div class="form-group mt-xxxl">
    <label>@if(!isset($seq) && $field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
	
    @if(!$field['options']['MultiLine'])
        {!! Form::text($fieldLabel, $textValue, ['class' => 'text-input preset-clear-text-js', 'id' => $fieldDivID, 'placeholder' => 'Enter text here']) !!}
    @elseif($field['options']['MultiLine'])
        @php
            $newLineCnt = substr_count($textValue, "\n");
            $taHeight = $newLineCnt*15;
            if($taHeight < 100)
                $taHeight = 100;
        @endphp
        {!! Form::textarea($fieldLabel, $textValue, ['class' => 'text-area preset-clear-text-js', 'style' => 'height:'.$taHeight.'px', 'id' => $fieldDivID, 'placeholder' => 'Enter text here']) !!}
    @endif
</div>