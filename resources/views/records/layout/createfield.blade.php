@if($field->type == 'Text')
    @include('records.fieldInputs.text')
@elseif($field->type == 'Rich Text')
    @include('records.fieldInputs.richtext')
@elseif($field->type == 'Number')
    @include('records.fieldInputs.number')
@endif