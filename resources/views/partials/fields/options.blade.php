{!! Form::hidden('pid',$field->pid) !!}
{!! Form::hidden('fid',$field->fid) !!}
{!! Form::hidden('type',$field->type) !!}
{!! Form::hidden('name',$field->name) !!}
{!! Form::hidden('desc',$field->desc) !!}

<div class="form-group">
    {!! Form::label('name', 'Field Name') !!}
    {!! Form::text('name', $field->name, ['class' => 'text-input', 'placeholder' => 'Enter the field name here', 'autofocus']) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('slug', 'Unique Form Identifier') !!}
    {!! Form::text('slug', $field->slug, ['class' => 'text-input', 'placeholder' => "Enter the field's unique ID here (no spaces, alpha-numeric values only)"]) !!}
</div>

<div class="form-group mt-xl">
    {!! Form::label('desc', 'Description') !!}
    {!! Form::textarea('desc', $field->desc, ['class' => 'text-area', 'placeholder' => "Enter the field's description here (max. 500 characters)"]) !!}

    <div class="spacer"></div>
</div>

@yield('fieldOptions')

<div class="form-group mt-xxxl">
    <div class="spacer"></div>

    <label for="required">Required?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="required" {{$field->required ? 'checked': ''}} />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Set field as required</span>
        <span class="placeholder-alt">Field is required</span>
    </div>

    <p class="sub-text mt-sm">
        Records must contain data in this field
    </p>
</div>

<div class="form-group mt-xl">
    <label for="searchable">Searchable?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="searchable" {{$field->searchable ? 'checked': ''}} />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Set field as searchable</span>
        <span class="placeholder-alt">Field is searchable</span>
    </div>

    <p class="sub-text mt-sm">
        Data in this field will be analyzed in searches from outside of kora
    </p>
</div>

<div class="form-group mt-xl">
    <label for="extsearch">Externally Searchable?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="extsearch" {{$field->extsearch ? 'checked': ''}} />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Set field as externally searchable</span>
        <span class="placeholder-alt">Field is externally searchable</span>
    </div>

    <p class="sub-text mt-sm">
        Data in this field will be analysed in searches from outside of Kora
    </p>
</div>

<div class="form-group mt-xl">
    <label for="viewable">Viewable?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="viewable" {{$field->viewable ? 'checked': ''}} />
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
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="viewresults" {{$field->viewresults ? 'checked': ''}} />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Set field as viewable in results</span>
        <span class="placeholder-alt">Field is viewable in results</span>
    </div>

    <p class="sub-text mt-sm">
        Data in this field will be shown in all records belonging to this form
    </p>
</div>

<div class="form-group mt-xl">
    <label for="extview">Externally Viewable?</label>
    <div class="check-box">
        <input type="checkbox" value="1" id="preset" class="check-box-input" name="extview" {{$field->extview ? 'checked': ''}} />
        <div class="check-box-background"></div>
        <span class="check"></span>
        <span class="placeholder">Set field as externally viewable</span>
        <span class="placeholder-alt">Field is externally viewable</span>
    </div>

    <p class="sub-text mt-sm">
        Data in this field will be shown in all searches, outside of Kora, containing records belonging to this form
    </p>

    <div class="spacer"></div>
</div>

<div class="form-group field-update-button">
    {!! Form::submit('Update Field',['class' => 'btn edit-btn update-field-submit pre-fixed-js']) !!}
</div>

<div class="form-group">
    <div class="field-cleanup">
        <a class="btn dot-btn trash warning field-trash-js" data-title="Delete Field?" href="#">
            <i class="icon icon-trash"></i>
        </a>
    </div>
</div>