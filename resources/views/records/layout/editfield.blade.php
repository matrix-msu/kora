@if($field->type == 'Text')
    @include('records.fieldInputs.text-edit', ['text' => \App\TextField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first()])
@elseif($field->type == 'Rich Text')
    @include('records.fieldInputs.richtext-edit', ['richtext' => \App\RichTextField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first()])
@endif