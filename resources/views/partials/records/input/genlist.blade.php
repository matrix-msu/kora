@php
    $jsFiller = '';
    if(isset($seq)) { //Combo List
        $jsFiller = "list-option-card-container-$seq-js";
        $fieldLabel = 'default_'.$seq;
        $fieldDivID = $listInputLabel = 'default_'.$seq.'_'.$flid;
        $listValues = null;
        $mainValues = array();
    } else if($editRecord) {
        $fieldLabel = $listInputLabel = $flid.'[]';
        $fieldDivID = 'list'.$flid;
        $listValues = json_decode($record->{$flid});
        $mainValues = [];
        if($listValues != null) {
            foreach($listValues as $val) {
                $mainValues[$val] = $val;
            }
        }
    } else {
        $fieldLabel = $listInputLabel = $flid.'[]';
        $fieldDivID = 'list'.$flid;
        $listValues = $field['default'];
        $mainValues = App\KoraFields\GeneratedListField::getList($field)["Options"];
    }
@endphp
<div class="form-group mt-xxxl specialty-field-group list-input-form-group">
    <label>@if(!isset($seq) && $field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
    <div class="form-input-container">
        <p class="directions">Add List Options below, and order them via drag & drop or their arrow icons.</p>

        <!-- Cards of list options -->
        <div class="genlist-record-input list-option-card-container list-option-card-container-js {{$jsFiller}}">
            @foreach($mainValues as $opt)
                <div id="{{$opt}}" class="card list-option-card list-option-card-js" data-list-value="{{$opt}}">
                    {!! Form::hidden($fieldLabel, $opt) !!}
                    <div class="header">
                        <div class="left">
                            <div class="move-actions">
                                <a class="action move-action-js up-js" href="">
                                    <i class="icon icon-arrow-up"></i>
                                </a>
                                <a class="action move-action-js down-js" href="">
                                    <i class="icon icon-arrow-down"></i>
                                </a>
                            </div>
                            <span class="title">{{$opt}}</span>
                        </div>

                        <div class="card-toggle-wrap">
                            <a class="list-option-delete list-option-delete-js tooltip" tooltip="Delete List Option" href=""><i class="icon icon-trash"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Card to add list options -->
        <div class="card new-list-option-card new-list-option-card-js">
            <div class="header">
                <div class="left">
                    <input class="new-list-option new-list-option-js" type="text" placeholder='Type here and hit the enter key or "Add" to add new list options' data-flid='{{$listInputLabel}}'>
                </div>

                <div class="card-toggle-wrap">
                    <a class="list-option-add list-option-add-js" href=""><span>Add</span></a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    window.onload = function () { Kora.Fields.Options('Generated List Record'); }
</script>
