<input type="hidden" name="_token" value="{{csrf_token()}}">
<input id="token_edit_modal_id" type="hidden" name="token" value="">

<div class="form-group mt-xl">
    <label for="token_name">Token Name</label>
    <input class="text-input" placeholder="Enter the name of the new here" type="text" id="token_edit_modal_name" name="token_name" value="">
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

<div class="form-group mt-xl">
    {!! Form::submit('Edit Token',['class' => 'btn']) !!}
</div>