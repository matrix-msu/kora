@if($typedField->circa==1 && \App\Http\Controllers\FieldController::getFieldOption($field,'Circa')=='Yes')
    {{'circa '}}
@endif
@if($typedField->month==0 && $typedField->day==0)
    {{$typedField->year}}
@elseif($typedField->day==0)
    {{ DateTime::createFromFormat('m', $typedField->month)->format('F').', '.$typedField->year }}
@elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'Format')=='MMDDYYYY')
    {{$typedField->month.'-'.$typedField->day.'-'.$typedField->year}}
@elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'Format')=='DDMMYYYY')
    {{$typedField->day.'-'.$typedField->month.'-'.$typedField->year}}
@elseif(\App\Http\Controllers\FieldController::getFieldOption($field,'Format')=='YYYYMMDD')
    {{$typedField->year.'-'.$typedField->month.'-'.$typedField->day}}
@endif
@if(\App\Http\Controllers\FieldController::getFieldOption($field,'Era')=='Yes')
    {{' '.$typedField->era}}
@endif