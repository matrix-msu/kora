<input type="hidden" name="userId" value="{{\Auth::user()->id}}">

@foreach(\App\Http\Controllers\PageController::getFormLayout($form->fid) as $page)
    <section id="#{{$page["title"]}}" class="page-section-js hidden">
        @foreach($page["fields"] as $field)
            <?php $typedField = $field->getTypedField(); ?>
            @include($typedField::FIELD_INPUT_VIEW, ['field' => $field])
            <div class="form-group mt-xs">
                <p class="sub-text">
                    {{$field->desc}}
                </p>
            </div>
        @endforeach
    </section>
@endforeach