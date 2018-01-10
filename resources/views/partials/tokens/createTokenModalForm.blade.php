<input type="hidden" name="_token" value="{{csrf_token()}}">

<div class="form-group mt-xl">
    <label for="token_name">Token Name</label>
    <input class="text-input" placeholder="Enter the name of the new here" type="text" id="token_name" name="token_name" value="">
</div>

<div class="form-group mt-xl">
    <label for="token_search">Select Token Type(s)</label>
</div>
<div class="actions">
    <div class="form-group action">
        <div class="check-box-half check-box-rectangle">
            <input type="checkbox"
                   value="0"
                   class="check-box-input"
                   name="token_search" />
            <span class="check"></span>
            <span class="placeholder">Search</span>
        </div>
    </div>

    <div class="form-group action">
        <div class="check-box-half check-box-rectangle">
            <input type="checkbox"
                   value="0"
                   class="check-box-input"
                   name="token_create" />
            <span class="check"></span>
            <span class="placeholder">Create</span>
        </div>
    </div>

    <div class="form-group action">
        <div class="check-box-half check-box-rectangle">
            <input type="checkbox"
                   value="0"
                   class="check-box-input"
                   name="token_edit" />
            <span class="check"></span>
            <span class="placeholder">Edit</span>
        </div>
    </div>

    <div class="form-group action">
        <div class="check-box-half check-box-rectangle">
            <input type="checkbox"
                   value="0"
                   class="check-box-input"
                   name="token_delete" />
            <span class="check"></span>
            <span class="placeholder">Delete</span>
        </div>
    </div>
</div>

<div class="form-group token-project-select-container token-project-select-container-js">
    <div class="token-project-select-js">
        <label for="token_projects">Select Project(s)</label>
        <select class="multi-select" id="token_projects" name="token_projects">
            @foreach ($all_projects as $project)
                <option value="{{$project->pid}}">{{$project->name}}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group mt-xl">
    {!! Form::submit('Create Token',['class' => 'btn']) !!}
</div>