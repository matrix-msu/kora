@php
    if(isset($seq)) {
        $seq = '_' . $seq;
    } else {
        $seq = '';
    }
    $assocLayout = [];
    $associations = \App\Http\Controllers\AssociationController::getAvailableAssociations($form->id);
    $firstConfig = true;
@endphp
<div class="form-group mt-xxxl">Association Search Configuration</div>

<div class="form-group associator-input {{count($associations) == 0 ? 'search-config-empty-state' : ''}}">
    @foreach($associations as $a)
        @php
            $f = \App\Http\Controllers\FormController::getForm($a->data_form);
            $formFieldsData = $f->layout['fields'];
            $formFields = array();
            foreach($formFieldsData as $aflid => $data) {
                $formFields[$aflid] = $data['name'];
            }

            // building an array about the association permissions
            $options = $field['options']['SearchForms'];
            foreach ($options as $opt) {
                $assocLayout[$opt['form_id']] = ['flids' => $opt['flids']];
            }

            // get layout info for this form
            $f_check = false;
            $f_flids = null;

            if(array_key_exists($f->id,$assocLayout)){
                $f_check = true;
                $f_flids = $assocLayout[$f->id]['flids'];
            }
        @endphp
        <div class="form-group
            @if(!$firstConfig)
               mt-xl
            @else
                @php $firstConfig = false; @endphp
            @endif
            ">
            <div class="check-box-half">
                <input type="checkbox" value="1" id="active" class="check-box-input association-check-js" name="checkbox_{{$f->id}}{{$seq}}"
                @if($f_check)
                    checked
                @endif
                />
                <span class="check"></span>
                <span class="placeholder">Search through {{$f->name}}?</span>
            </div>
        </div>

        <div class="form-group mt-m
        @if(!$f_check)
            hidden
        @endif
        ">
            {!! Form::label('preview_' . $f->id . $seq . '[]', 'Preview Value') !!}
            {!! Form::select('preview_' . $f->id . $seq . '[]', $formFields, $f_flids, ['class' => 'multi-select assoc-preview-js', 'multiple', 'data-placeholder' => 'Select field preview value']) !!}
        </div>
    @endforeach
    @if(count($associations) == 0) No Forms Associated @endif
</div>

<input name="flids{{$seq}}" type="hidden" value="">

<div class="form-group mt-sm">
    <p class="sub-text">
        If no forms are available, have a Form Admin request permission to forms by using the Association Permissions page.
    </p>
</div>
