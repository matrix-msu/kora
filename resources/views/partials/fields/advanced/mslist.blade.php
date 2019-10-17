{!! Form::hidden('advanced',true) !!}
<div class="form-group specialty-field-group list-input-form-group mt-xxxl">
    {!! Form::label('options','List Options') !!}

    <div class="form-input-container">
        <p class="directions">Add List Options below, and order them via drag & drop or their arrow icons.</p>

        <!-- Cards of list options -->
        <div class="list-option-card-container list-option-card-container-js"></div>

        <!-- Card to add list options -->
        <div class="card new-list-option-card new-list-option-card-js">
            <div class="header">
                <div class="left">
                    <input class="new-list-option new-list-option-js" type="text" placeholder='Type here and hit the enter key or "Add" to add new list options'>
                </div>

                <div class="card-toggle-wrap">
                    <a class="list-option-add list-option-add-js" href=""><span>Add</span></a>
                </div>
            </div>
        </div>

        <div class="list-option-mass-opts mt-xl mb-xs">
            <div class="list-option-mass-link list-option-mass-copy">
                <i class="icon icon-duplicate-little"></i>
                <a href="#" class="list-option-mass-copy-js">Copy All List Options</a>
            </div>
            <div class="list-option-mass-link list-option-mass-delete right">
                <i class="icon icon-trash"></i>
                <a href="#" class="list-option-mass-delete-js">Delete All List Options</a>
            </div>
        </div>
    </div>
</div>

<div class="form-group mt-xl">
    {!! Form::label('default','Default') !!}
    {!! Form::select('default[]', [], null, ['class' => 'multi-select list-default-js', 'multiple', 'data-placeholder' => 'Select the default values here (Values must be added above in order to select)']) !!}
</div>

<script>
    Kora.Fields.Options('Multi-Select List');
</script>
