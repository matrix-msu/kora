@include('forms.layout.logic',['form' => $form, 'fieldview' => 'records.layout.createfield'])

    <input type="hidden" name="userId" value="{{\Auth::user()->id}}">

<div class="form-group">
    {!! Form::submit($submitButtonText,['class' => 'btn btn-primary form-control']) !!}
</div>