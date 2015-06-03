@foreach($form->fields as $field)
    <div>{{ $field->name }}</div>
@endforeach
<div class="form-group">
    {!! Form::submit($submitButtonText,['class' => 'btn btn-primary form-control']) !!}
</div>