@php
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
@endphp
<div class="form-group mt-xxxl specialty-field-group list-input-form-group">
    <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}</label>
    <span class="error-message"></span>
    {!! Form::select($field->flid.'[]',$listOpts, $selected, ['class' => 'genlist-js multi-select modify-select preset-clear-chosen-js', 'multiple',
        'id' => 'list'.$field->flid, 'data-placeholder' => 'Select Some Options or Type a New Option and Press Enter']) !!}

    <div class="form-input-container mt-m">
        <p class="directions">Add List Options below, and order them via drag & drop or their arrow icons.</p>

        <!-- Cards of list options -->
        <div class="list-option-card-container list-option-card-container-js">
            @foreach($listOpts as $opt)
                <div class="card list-option-card list-option-card-js" data-list-value="{{$opt}}">
                    <div class="header">
                        <div class="left">
                            <div class="move-actions">
                                <a class="action move-action-js up-js"><i class="icon icon-arrow-up"></i></a>
                                <a class="action move-action-js down-js"><i class="icon icon-arrow-down"></i></a>
                            </div>
                            <span class="title">{{$opt}}</span>
                        </div>
                        <div class="card-toggle-wrap">
                            <a class="list-option-delete list-option-delete-js tooltip" tooltip="Delete List Option"><i class="icon icon-trash"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

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
    </div>
</div>
<script>
    window.onload = function () { Kora.Fields.Options('Generated List'); }
</script>