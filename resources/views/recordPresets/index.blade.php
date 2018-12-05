@extends('app', ['page_title' => 'Record Presets', 'page_class' => 'record-preset'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
    @include('partials.menu.static', ['name' => 'Record Presets'])
@stop

@section('aside-content')
  @include('partials.sideMenu.form', ['pid' => $form->pid, 'fid' => $form->fid, 'openDrawer' => true])
@stop

@section('stylesheets')

@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-preset"></i>
                <span>Record Presets</span>
            </h1>
            <p class="description">Use this page to view and manage record presets within this form. Record presets allow you to create an instance of a record to be reused again. To create a new record preset, visit the single record you wish to turn into a preset. There you’ll find the option to turn the record into a preset. For more information on Record Presets, refer to the <a href="#">Record Presets - Kora Documentation.</a></p>
        </div>
    </section>
@stop

@section('body')
    @include('partials.recordPresets.modals.changeRecordPresetNameModal')
    @include('partials.recordPresets.modals.deleteRecordPresetModal')

    <section class="manage-presets center">
        @if (count($presets) > 0)
          @foreach($presets as $index => $preset)
              @include('partials.recordPresets.card')
          @endforeach
        @else
            @include('partials.recordPresets.no-presets')
        @endif
    </section>
@stop

@section('footer')
    @include('partials.recordPresets.javascripts')

    <script>
        changePresetNameUrl = '{{action('RecordPresetController@changePresetName')}}';
        deletePresetUrl = '{{action('RecordPresetController@deletePreset')}}';
        csrfToken = '{{csrf_token()}}';

        Kora.RecordPresets.Index();
    </script>
@stop
