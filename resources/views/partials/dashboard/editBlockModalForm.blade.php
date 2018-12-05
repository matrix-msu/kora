{!! csrf_field() !!}
<input type="hidden" name="_method" value="PATCH">
<input type="hidden" name="selected_id" value="">

<div class="form-group mt-xxs">
    <label for="block_type">Block Type</label>
    <span class="error-message"></span>
    <select class="single-select block-type-selected-js edit-block-type-selected-js" id="block_type" name="block_type">
        <option></option>
        <option value="Project">Project</option>
        <option value="Form">Form</option>
        <option value="Record">Record</option>
        <option value="Quote">Quote</option>
        <option value="Twitter">Kora Twitter</option>
        <option value="Note">Note</option>
    </select>
</div>

<section class="project-block-fields-js hidden">
    <div class="form-group mt-xl">
        <label for="block_project">Select Project</label>
        <span class="error-message"></span>
        <select class="single-select edit-block-project-js" id="block_project" name="block_project">
            <option></option>
            @foreach($userProjects as $proj)
                <option value="{{$proj->pid}}">{{$proj->name}}</option>
            @endforeach
        </select>
    </div>
</section>

<section class="form-block-fields-js hidden">
    <div class="form-group mt-xl">
        <label for="block_form">Select Form</label>
        <span class="error-message"></span>
        <select class="single-select edit-block-form-js" id="block_form" name="block_form">
            <option></option>
            @foreach($userForms as $form)
                <option value="{{$form->fid}}">{{$form->name}}</option>
            @endforeach
        </select>
    </div>
</section>

<section class="record-block-fields-js hidden">
    <div class="form-group mt-xl">
        <label for="block_record">Select Record</label>
        <span class="error-message"></span>
        <select class="single-select edit-block-record-js" id="block_record" name="block_record">
            <option></option>
            @foreach($userRecords as $rec)
                <option value="{{$rec}}">{{$rec}}</option>
            @endforeach
        </select>
    </div>
</section>

<section class="note-block-fields-js hidden">
    <div class="form-group mt-xl">
        <label for="block_note_title">Note Title</label>
        <span class="error-message"></span>
        <input class="text-input edit-note-title-js" placeholder="Enter note title here (max 40 characters)" type="text" id="block_note_title" name="block_note_title" value="" maxlength="40">
    </div>

    <div class="form-group mt-xl">
        <label for="block_note_content">Note Content</label>
        <span class="error-message"></span>
        <textarea class="text-area edit-note-desc-js" placeholder="Enter note content here" id="block_note_content" name="block_note_content"></textarea>
    </div>
</section>

<div class="form-group mt-xl">
    <label for="section_to_add">Select Section</label>
    <span class="error-message"></span>
    <select class="single-select section-to-add-js edit-section-to-add-js" id="section_to_add" name="section_to_add" disabled>
        <option></option>
        @foreach($sections as $section)
            <option value="{{$section['id']}}">{{$section['title']}}</option>
        @endforeach
    </select>
</div>

<div class="form-group mt-xxl">
    {!! Form::submit('Update Block',['class' => 'btn disabled edit-block-submit-js']) !!}
</div>