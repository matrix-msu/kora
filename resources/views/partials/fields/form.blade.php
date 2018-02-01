{!! Form::hidden('pid',$pid) !!}
{!! Form::hidden('fid',$fid) !!}
{!! Form::hidden('page_id',$rootPage) !!}

<div class="form-group">
    {!! Form::label('name', 'Field Name') !!}
    {!! Form::text('name', null, ['class' => 'text-input', 'placeholder' => 'Enter the field name here', 'autofocus']) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('slug', 'Unique Form Identifier') !!}
    {!! Form::text('slug', null, ['class' => 'text-input', 'placeholder' => "Enter the field's unique ID here (no spaces, alpha-numeric values only)"]) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('desc', 'Description') !!}
    {!! Form::textarea('desc', null, ['class' => 'text-area', 'placeholder' => "Enter the field's description here (max. 500 characters)"]) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('type','Field Type: ') !!}
    {!! Form::select('type', $validFieldTypes, null,['class' => 'single-select field-types-js']) !!}
</div>

<section class="hidden mt-xl combo-list-form-js">
    <div class="form-group half pr-m">
        {!! Form::label('cftype1','Combo List Field Type 1: ') !!}
        {!! Form::select('cftype1', $validComboListFieldTypes, null,['class' => 'single-select']) !!}
    </div>

    <div class="form-group half pl-m">
        {!! Form::label('cfname1','Combo List Field Name 1: ') !!}
        {!! Form::text('cfname1', null, ['class' => 'text-input', 'placeholder' => 'Enter the combo list field name 1 here']) !!}
    </div>

    <section class="mt-xl">
        <div class="form-group half pr-m">
            {!! Form::label('cftype2','Combo List Field Type 2: ') !!}
            {!! Form::select('cftype2', $validComboListFieldTypes, null,['class' => 'single-select']) !!}
        </div>
        <div class="form-group half pl-m">
            {!! Form::label('cfname2','Combo List Field Name 2: ') !!}
            {!! Form::text('cfname2', null, ['class' => 'text-input', 'placeholder' => 'Enter the combo list field name 2 here']) !!}
        </div>
    </section>
</section>

<div id="advance_options_div">
    <div class="form-group mt-xxxl">
        <button type="button" id="adv_opt" class="btn form-control">Show Advanced Field Options</button>
    </div>
</div>

<div class="form-group mt-xxxl">
    <label for="required">Required?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="required" />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Select to set the field as required</span>
        <span class="placeholder-alt">Field is set to be required</span>
    </div>

    <p class="sub-text mt-sm">
        Records must contain data in this field
    </p>
</div>

<div class="form-group mt-xl">
    <label for="searchable">Searchable?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="searchable" />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Select to set the field as searchable</span>
        <span class="placeholder-alt">Field is set to be searchable</span>
    </div>

    <p class="sub-text mt-sm">
        Data in this field will be analyzed in searches from outside of kora
    </p>
</div>

<div class="form-group mt-xl">
    <label for="extsearch">Externally Searchable?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="extsearch" />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Select to set the field as externally searchable</span>
        <span class="placeholder-alt">Field is set to be externally searchable</span>
    </div>

    <p class="sub-text mt-sm">
        Data in this field will be analysed in searches from outside of Kora
    </p>
</div>

<div class="form-group mt-xl">
    <label for="viewable">Viewable?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="viewable" />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Select to set the field as viewable</span>
        <span class="placeholder-alt">Field is set to be viewable</span>
    </div>

    <p class="sub-text mt-sm">
        Data in this field will be shown in all records belonging to this form
    </p>
</div>

<div class="form-group mt-xl">
    <label for="viewresults">Viewable in Results?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="viewresults" />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Select to set the field as viewable in results</span>
        <span class="placeholder-alt">Field is set to be viewable in results</span>
    </div>

    <p class="sub-text mt-sm">
        Data in this field will be shown in all records belonging to this form
    </p>
</div>

<div class="form-group mt-xl">
    <label for="extview">Externally Viewable?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="extview" />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Select to set the field as externally viewable</span>
        <span class="placeholder-alt">Field is set to be externally viewable</span>
    </div>

    <p class="sub-text mt-sm">
        Data in this field will be shown in all searches, outside of Kora, containing records belonging to this form
    </p>
</div>

<div class="form-group mt-xxxl mb-max">
    {!! Form::submit($submitButtonText,['class' => 'btn']) !!}
</div>

<script>


</script>