<input type="hidden" name="_token" value="{{csrf_token()}}">
<input type="hidden" name="_method" value="patch">
<input id="token_delete_project_modal_id" type="hidden" name="token" value="">
<input id="token_delete_project_modal_pid" type="hidden" name="pid" value="">

<div class="form-group" id="token_delete_project_modal_name">
    Are you sure you want to remove project access from this Token?
</div>

<div class="form-group">
    {!! Form::submit('Remove Token Project',['class' => 'btn warning']) !!}
</div>