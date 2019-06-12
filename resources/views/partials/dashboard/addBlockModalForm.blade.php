{!! csrf_field() !!}

<div class="form-group mt-xxs add-block-type-js">
    <label for="block_type">Dashboard Block Type</label>
    <span class="error-message"></span>
    <select class="single-select block-type-selected-js" id="block_type" name="block_type">
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
        <select class="single-select" id="block_project" name="block_project" data-placeholder="Select a Project">
            <option></option>
            @foreach($userProjects as $proj)
                <option value="{{$proj->id}}">{{$proj->name}}</option>
            @endforeach
        </select>
    </div>
</section>

<section class="form-block-fields-js hidden">
    <div class="form-group mt-xl">
        <label for="block_form">Select Form</label>
        <span class="error-message"></span>
        <select class="single-select" id="block_form" name="block_form" data-placeholder="Select a Form">
            <option></option>
            @foreach($userForms as $form)
                <option value="{{$form->id}}">{{$form->name}}</option>
            @endforeach
        </select>
    </div>
</section>

<section class="record-block-fields-js hidden">
    <div class="form-group mt-xl">
        <label for="block_record">Select Record</label>
        <span class="error-message"></span>
        <input class="text-input" placeholder="Enter Record KID" type="text" id="block_record" name="block_record" value="" maxlength="20">
    </div>
</section>

<section class="note-block-fields-js hidden">
    <div class="form-group mt-xl">
        <label for="block_note_title">Note Title</label>
        <span class="error-message"></span>
        <input class="text-input" placeholder="Enter note title here (max 30 characters)" type="text" id="block_note_title" name="block_note_title" value="" maxlength="30">
    </div>

    <div class="form-group mt-xl">
        <label for="block_note_content">Note Content</label>
        <span class="error-message"></span>
        <textarea class="text-area" placeholder="Enter note content here (max 300 characters)" id="block_note_content" name="block_note_content" maxlength="300"></textarea>
    </div>
</section>

<div class="form-group mt-xl add-block-section-js">
    <label for="section_to_add">Add to Section</label>
    <span class="error-message"></span>
    <select class="single-select section-to-add-js" id="section_to_add" name="section_to_add" disabled>
        <option></option>
        @foreach($sections as $section)
            <option value="{{$section['id']}}">{{$section['title']}}</option>
        @endforeach
    </select>
</div>

<div class="form-group mt-xxl">
    {!! Form::submit('Add New Dashboard Block',['class' => 'btn disabled add-block-submit-js']) !!}
</div>