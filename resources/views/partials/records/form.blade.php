<input type="hidden" name="userId" value="{{\Auth::user()->id}}">

@foreach(\App\Http\Controllers\PageController::getFormLayout($form->fid) as $page)
    <section id="#{{$page["title"]}}" class="page-section-js hidden">
        @foreach($page["fields"] as $field)
            <?php
                if($editRecord) {
                    $typedField = $field->getTypedFieldFromRID($record->rid);
                    $hasData = true;
                    if(is_null($typedField)) {
                        $typedField = $field->getTypedField();
                        $hasData = false;
                    }
                } else {
                    $typedField = $field->getTypedField();
                    $hasData = false;
                }
            ?>
            
            @include($typedField::FIELD_INPUT_VIEW, ['field' => $field, 'hasData' => $hasData, 'editRecord' => $editRecord])
        
            <div class="form-group mt-xs">
                <p class="sub-text">
                    {{$field->desc}}
                </p>
            </div>
        @endforeach
    </section>
@endforeach