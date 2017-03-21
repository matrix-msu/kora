@include('forms.layout.logic',['form' => $form, 'fieldview' => 'records.layout.editfield'])
<div class="form-group">
    {!! Form::submit($submitButtonText,['class' => 'btn btn-primary form-control']) !!}
</div>