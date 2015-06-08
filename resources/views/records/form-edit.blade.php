@foreach($form->fields as $field)
    @if($field->type == 'Text')
        @include('records.fieldInputs.text-edit', ['text' => \App\TextField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first()])
    @endif
@endforeach
<div class="form-group">
    {!! Form::submit($submitButtonText,['class' => 'btn btn-primary form-control']) !!}
</div>