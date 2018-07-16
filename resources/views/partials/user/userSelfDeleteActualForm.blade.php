<span class="description">
    Type DELETE below, just so we know for sure that you're intentionally deleting your account.
</span>

<div class="form-group mt-m">
    <span class="error-message error-message-js"></span>
    <input type="text" class="text-input delete-validation-js" name="delete-validation" id="delete-validation" placeholder='Type "DELETE" here'>
</div>

<div class="form-group user-self-delete-2-submit user-self-delete-2-submit-js">
    {!! Form::button('Delete My Account',['class' => 'btn warning']) !!}
</div>