@php
    $oneName = $field['one']['name'];
    $twoName = $field['two']['name'];
    $oneType = $field['one']['type'];
    $twoType = $field['two']['type'];
    $oneFlid = $field['one']['flid'];
    $twoFlid = $field['two']['flid'];

    $items = $typedField->retrieve($flid, $form->id, $value);

    $modelOne = $form->getFieldModel($oneType);
    $modelTwo = $form->getFieldModel($twoType);
    $recDisplayOne = $modelOne::FIELD_DISPLAY_VIEW;
    $recDisplayTwo = $modelTwo::FIELD_DISPLAY_VIEW;
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
            @if($i!=0) <span class="combo-border-small"> </span> @endif

            <span class="combo-column">@include($recDisplayOne, ['field'=>$field['one'], 'value'=>$items[$i]->{$oneFlid}, 'typedField' => $modelOne])</span>
            <span class="combo-column">@include($recDisplayTwo, ['field'=>$field['two'], 'value'=>$items[$i]->{$twoFlid}, 'typedField' => $modelTwo])</span>
        </div>
    @endfor
</div>
