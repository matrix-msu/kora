<div class="form-group">
    <label for="emails">Enter Email(s) to Create Users</label>
    <span class="error-message emails"></span>
    <input type="text" class="text-input" id="emails" name="emails" placeholder="Enter user email(s) here. Separate multiple emails with a space or a comma.">
</div>

<div class="form-group mt-xl">
    {!! Form::submit('Create User(s)', ['class' => 'btn btn-primary form-control', 'name' => 'sendButton']) !!}
</div>
