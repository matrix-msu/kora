<input type="hidden" name="_token" value="{{csrf_token()}}">
<input type="hidden" name="_method" value="patch">
<input id="add_projects_modal_id" type="hidden" name="token" value="">

<div class="form-group token-project-select-container token-project-select-container-js">
    <div class="token-project-select-js">
        <label for="token_projects">Select Project(s)</label>
        <select multiple class="multi-select" id="add_token_projects" name="token_projects[]">
        </select>
    </div>
</div>

<div class="form-group mt-xl">
    {!! Form::submit('Add Projects to Token',['class' => 'btn']) !!}
</div>