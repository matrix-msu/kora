@php
    if(isset($seq)) {
        $jseq = $seq . '-';
        $seq = '_' . $seq;
    } else
        $seq = $jseq = '';
@endphp
<div class="form-group specialty-field-group list-input-form-group {{$seq ? 'list-input-form-group-combo mt-xxl' : ''}}">
    {!! Form::label('options','List Options') !!}

    <div class="form-input-container">
        <p class="directions">Add List Options below, and order them via drag & drop or their arrow icons.</p>

        <!-- Cards of list options -->
        <div class="list-option-card-container list-option-card-container-{{$jseq}}js">
            @foreach($field['options']['Options'] as $option)
                <div class="card list-option-card list-option-card-js" data-list-value="{{ $option }}">
                    <input type="hidden" class="list-option-js" name="options{{$seq}}[]" value="{{ $option }}">

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

                            <span class="title">{{ $option }}</span>
                        </div>

                        <div class="card-toggle-wrap">
                            <a class="list-option-delete list-option-delete-js tooltip" href="" tooltip="Delete List Option"><i class="icon icon-trash"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Card to add list options -->
        <div class="card new-list-option-card new-list-option-card-{{$jseq}}js">
            <div class="header">
                <div class="left">
                    <input class="new-list-option new-list-option-{{$jseq}}js" type="text" placeholder='Type here and hit the enter key or "Add" to add new list options'>
                </div>

                <div class="card-toggle-wrap">
                    <a class="list-option-add list-option-add-{{$jseq}}js" href=""><span>Add</span></a>
                </div>
            </div>
        </div>
    </div>

    @if($seq)
        <div><a href="#" class="field-preset-link open-regex-modal-js">Use a Value Preset for these List Options</a></div>

        <div class="pb-xl"></div>
    @else
        <div><a href="#" class="field-preset-link open-list-modal-js">Use a Value Preset for these List Options</a></div>
        <div class="open-create-regex"><a href="#" class="field-preset-link open-create-list-modal-js right
            @if(empty($field['options']['Options'])) disabled tooltip @endif" tooltip="You must submit or update the field before creating a New Value Preset">
                Create a New Value Preset from these List Options</a></div>
    @endif
</div>

<div class="form-group mt-xl">
    {!! Form::label('regex' . $seq,'Regex') !!}
    {!! Form::text('regex' . $seq, $field['options']['Regex'], ['class' => 'text-input', 'placeholder' => 'Enter regular expression pattern here']) !!}
</div>
