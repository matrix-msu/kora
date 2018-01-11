<input type="hidden" name="_token" value="{{csrf_token()}}">
<input type="hidden" name="_method" value="delete">
<input id="token_delete_modal_id" type="hidden" name="token" value="">

<div class="form-group">
    Are you sure you want to delete this Token?
</div>

<div class="form-group mt-xl">
    {!! Form::submit('Delete Token',['class' => 'btn warning']) !!}
</div>