<?php
    if($editRecord && $hasData) {
        $selected = explode('[!]',$typedField->options);
        $listOpts = array();
        foreach($selected as $op) {
            $listOpts[$op] = $op;
        }
    } else if($editRecord) {
        $selected = null;
        $listOpts = array();
    } else {
        $selected = explode('[!]',$field->default);
        $listOpts = \App\GeneratedListField::getList($field,false);
    }
    //dd($typedField, $field, $listOpts, $selected);
?>
<div class="form-group mt-xxxl">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}</label>
    <span class="error-message"></span>
    {!! Form::select($field->flid.'[]',$listOpts, $selected, ['class' => 'multi-select modify-select preset-clear-chosen-js', 'multiple',
        'id' => 'list'.$field->flid, 'data-placeholder' => 'Select Some Options or Type a New Option and Press Enter']) !!}
</div>
<div class="form-group specialty-field-group list-input-form-group mt-xxxl">
    <div class="form-input-container">
        <p class="directions">Add List Options below, and order them via drag & drop or their arrow icons.</p>

        <!-- Cards of list options -->
        <div class="list-option-card-container list-option-card-container-js"></div>

        <!-- Card to add list options -->
        <div class="card new-list-option-card new-list-option-card-js">
            <div class="header">
                <div class="left">
                    <input class="new-list-option new-genList-option-js" type="text" placeholder='Type here and hit the enter key or "Add" to add new list options'>
                </div>

                <div class="card-toggle-wrap">
                    <a class="list-option-add list-option-add-js" href=""><span>Add</span></a>
                </div>
            </div>
        </div>
    </div>
</div>