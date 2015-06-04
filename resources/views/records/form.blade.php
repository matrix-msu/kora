@foreach($form->fields as $field)
    @if($field->type == 'Text')
        @include('records.fieldInputs.text')
    @endif
@endforeach
<div class="form-group">
    {!! Form::submit($submitButtonText,['class' => 'btn btn-primary form-control']) !!}
</div>