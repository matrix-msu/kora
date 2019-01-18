<input type="hidden" name="_token" value="{{csrf_token()}}">

<div class="form-group mt-xxs">
    <label for="token_name">Token Name</label>
	<p id="token-name-warning" class="token-warning"></p>
    <input class="text-input" placeholder="Enter the name of the new token here" type="text" id="token_name" name="token_name" value="">
</div>

<div class="form-group mt-xl">
    <label for="token_search">Select Token Type(s)</label>
	<p id="token-checkbox-warning" class="token-warning">
		At least one Token Type must be selected
	</p>
</div>
<div class="actions">
    <div class="form-group action mt-xs">
        <div class="check-box-half check-box-rectangle">
            <input type="checkbox"
                   value="0"
                   class="check-box-input search-token-create-js"
                   name="token_search" />
            <span class="check"></span>
            <span class="placeholder">Search</span>
        </div>
    </div>

    <div class="form-group action">
        <div class="check-box-half check-box-rectangle">
            <input type="checkbox"
                   value="0"
                   class="check-box-input create-token-create-js"
                   name="token_create" />
            <span class="check"></span>
            <span class="placeholder">Create</span>
        </div>
    </div>

    <div class="form-group action">
        <div class="check-box-half check-box-rectangle">
            <input type="checkbox"
                   value="0"
                   class="check-box-input edit-token-create-js"
                   name="token_edit" />
            <span class="check"></span>
            <span class="placeholder">Edit</span>
        </div>
    </div>

    <div class="form-group action">
        <div class="check-box-half check-box-rectangle">
            <input type="checkbox"
                   value="0"
                   class="check-box-input delete-token-create-js"
                   name="token_delete" />
            <span class="check"></span>
            <span class="placeholder">Delete</span>
        </div>
    </div>
</div>

<div class="form-group token-project-select-container token-project-select-container-js">
    <div class="token-project-select-js">
        <label for="token_projects">Select Project(s)</label>
        <select multiple class="multi-select" id="token_projects" name="token_projects[]">
            @foreach ($all_projects as $project)
                <option value="{{$project->id}}">{{$project->name}}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group mt-xxl">
    {!! Form::submit('Create Token',['class' => 'btn validate-token-js']) !!}
</div>