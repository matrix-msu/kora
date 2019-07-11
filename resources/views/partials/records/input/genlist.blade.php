@php
    if($editRecord) {
        $listValues = json_decode($record->{$flid});
        $mainValues = [];
        if($listValues != null) {
            foreach($listValues as $val) {
                $mainValues[$val] = $val;
            }
        }
    } else {
        $listValues = $field['default'];
        $mainValues = App\KoraFields\GeneratedListField::getList($field)["Options"];
    }
@endphp
<div class="form-group mt-xxxl specialty-field-group list-input-form-group">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
    <span class="error-message"></span>
    <!-- {!! Form::select($flid.'[]', $mainValues, $listValues, ['class' => 'genlist-js multi-select modify-select preset-clear-chosen-js', 'multiple',
        'id' => 'list'.$flid, 'data-placeholder' => 'Select Some Options or Type a New Option and Press Enter']) !!} -->
    <div class="form-input-container">
        <p class="directions">Add List Options below, and order them via drag & drop or their arrow icons.</p>

        <!-- Cards of list options -->
        <div class="genlist-record-input list-option-card-container list-option-card-container-js">
            @foreach($mainValues as $opt)
                <div id="{{$opt}}" class="card list-option-card list-option-card-js" data-list-value="{{$opt}}">
                    {!! Form::hidden($flid.'[]', $opt) !!}
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
                    <input class="new-list-option new-list-option-js" type="text" placeholder='Type here and hit the enter key or "Add" to add new list options' data-flid='{{$flid}}'>
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
