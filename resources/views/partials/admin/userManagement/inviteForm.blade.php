<div class="form-group">
    <label for="emails">Enter Email(s) to Invite Users</label>
    <span class="error-message emails"></span>
    <input type="text" class="text-input" id="emails" name="emails" placeholder="Enter invitee email(s) here. Seperate multiple emails with a space or a comma.">
</div>

<div class="form-group mt-xl">
    <label for="message">Include a Personal Message?</label>
	<span class="error-message"></span>
    <textarea class="text-area" id="message" name="message" placeholder="Provide further details to be sent to invited users. Including a personal message is optional."></textarea>
</div>

<div class="form-group mt-xl">
    {!! Form::submit('Invite User(s)', ['class' => 'btn btn-primary form-control', 'name' => 'sendButton']) !!}
</div>
