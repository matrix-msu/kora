<div class="modal modal-js modal-mask create-test-records-js">
    <div class="content">
        <div class="header">
            <span class="title title-js">Create Test Records?</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            {!! Form::open([
              'method' => 'POST',
              'action' => ['RecordController@createTest', 'pid' => $form->pid, 'fid' => $form->fid]
            ]) !!}

            <div class="description">
                Create test records in this form to simulate large data operations within Kora 3 or its API.
            </div>

            <div class="form-group mt-xxl">
                {!! Form::label('test_records_num', 'Test Record Create Amount') !!}
                {!! Form::number('test_records_num', null, ['class' => 'text-input', 'min' => '1', 'max' => '1000', 'step' => '1',
                    'placeholder' => "Select the number of test records you would like to create here (Max. 1000)"]) !!}
            </div>

            <div class="form-group mt-xxl">
                {!! Form::submit('Create Test Records',['class' => 'btn']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>