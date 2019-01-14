{!! Form::hidden('pid',$pid) !!}
{!! Form::hidden('fid',$fid) !!}
{!! Form::hidden('page_id',$pageIndex) !!}

<div class="form-group">
    {!! Form::label('name', 'Field Name') !!}
    <span class="error-message">{{array_key_exists("name", $errors->messages()) ? $errors->messages()["name"][0] : ''}}</span>
    {!! Form::text('name', null, ['class' => 'text-input' . (array_key_exists("name", $errors->messages()) ? ' error' : ''), 'placeholder' => 'Enter the field name here', 'autofocus']) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('desc', 'Description') !!}
    <span class="error-message">{{array_key_exists("desc", $errors->messages()) ? $errors->messages()["desc"][0] : ''}}</span>
    {!! Form::textarea('desc', null, ['class' => 'text-area' . (array_key_exists("desc", $errors->messages()) ? ' error' : ''), 'placeholder' => "Enter the field's description here (max. 500 characters)"]) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('type','Field Type') !!}
	<a class="underline-middle-hover pb-xxs desc-modal">
    	<span class="name">View Field Type Descriptions</span>
    </a>
    {!! Form::select('type', $validFieldTypes, null,['class' => 'single-select field-types-js']) !!}
</div>

<section class="hidden mt-xl combo-list-form-js">
    <div class="form-group half pr-m">
        {!! Form::label('cftype1','Combo List Field Type 1') !!}
        {!! Form::select('cftype1', $validComboListFieldTypes, null,['class' => 'single-select']) !!}
    </div>

    <div class="form-group half pl-m">
        {!! Form::label('cfname1','Combo List Field Name 1') !!}
        <span class="error-message">{{array_key_exists("cfname1", $errors->messages()) ? $errors->messages()["cfname1"][0] : ''}}</span>
        {!! Form::text('cfname1', null, ['class' => 'text-input'. (array_key_exists("cfname1", $errors->messages()) ? ' error' : ''), 'placeholder' => 'Enter the combo list field name 1 here']) !!}
    </div>

    <section class="mt-xl">
        <div class="form-group half pr-m">
            {!! Form::label('cftype2','Combo List Field Type 2') !!}
            {!! Form::select('cftype2', $validComboListFieldTypes, null,['class' => 'single-select']) !!}
        </div>
        <div class="form-group half pl-m">
            {!! Form::label('cfname2','Combo List Field Name 2') !!}
            <span class="error-message">{{array_key_exists("cfname2", $errors->messages()) ? $errors->messages()["cfname2"][0] : ''}}</span>
            {!! Form::text('cfname2', null, ['class' => 'text-input'. (array_key_exists("cfname2", $errors->messages()) ? ' error' : ''), 'placeholder' => 'Enter the combo list field name 2 here']) !!}
        </div>
    </section>
</section>

{{--TODO::CASTLE--}}
<div class="form-group mt-xxxl">
    <section class="advanced-options-show">
        <a href="#" class="btn half-sub-btn extend advanced-options-btn-js">Show Advanced Field Options</a>
    </section>
    <section class="advanced-options-hide hidden">
        <a href="#" class="btn half-sub-btn extend advanced-options-btn-js">Hide Advanced Field Options</a>
    </section>
</div>

<section class="advance-options-section-js"></section>

<div class="form-group mt-xxxl">
    <div class="spacer"></div>
</div>

<div class="form-group mt-xxxl">
    <label for="required">Required?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="required" />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Set field as required</span>
        <span class="placeholder-alt">Field is set to be required</span>
    </div>

    <p class="sub-text mt-sm">
        Records must contain data in this field
    </p>
</div>

<div class="form-group mt-xl">
    <label for="searchable">Searchable?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="searchable" checked />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Set field as searchable</span>
        <span class="placeholder-alt">Field is searchable</span>
    </div>

    <p class="sub-text mt-sm">
        Data in this field will be analyzed in searches inside of kora
    </p>
</div>

<div class="form-group mt-xl">
    <label for="advsearch">Advanced Searchable?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="advsearch" />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Set field as advanced searchable</span>
        <span class="placeholder-alt">Field is advanced searchable</span>
    </div>

    <p class="sub-text mt-sm">
        Data in this field will be analyzed in advanced searches inside of Kora
    </p>
</div>

<div class="form-group mt-xl">
    <label for="extsearch">Externally Searchable?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="extsearch" checked />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Set field as externally searchable</span>
        <span class="placeholder-alt">Field is externally searchable</span>
    </div>

    <p class="sub-text mt-sm">
        Data in this field will be analyzed in searches from outside of Kora
    </p>
</div>

<div class="form-group mt-xl">
    <label for="viewable">Viewable?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="viewable" checked />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Set field as viewable</span>
        <span class="placeholder-alt">Field is viewable</span>
    </div>

    <p class="sub-text mt-sm">
        Data in this field will be shown in all records belonging to this form
    </p>
</div>

<div class="form-group mt-xl">
    <label for="viewresults">Viewable in Results?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="viewresults" checked />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Set field as viewable in results</span>
        <span class="placeholder-alt">Field is viewable in results</span>
    </div>

    <p class="sub-text mt-sm">
        Data in this field will be shown in all search results within Kora
    </p>
</div>

<div class="form-group mt-xl">
    <label for="extview">Externally Viewable?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="extview" checked />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Set field as externally viewable</span>
        <span class="placeholder-alt">Field is externally viewable</span>
    </div>

    <p class="sub-text mt-sm">
        Data in this field will be shown in all searches, outside of Kora, containing records belonging to this form
    </p>
</div>

<div class="form-group mt-xxxl mb-max">
    {!! Form::submit($submitButtonText,['class' => 'btn validate-field-js']) !!}
</div>