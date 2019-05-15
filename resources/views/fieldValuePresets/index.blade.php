@extends('app', ['page_title' => 'Field Value Presets', 'page_class' => 'option-presets'])

@section('stylesheets')
    <!-- No Additional Stylesheets Necessary -->
@stop

@section('aside-content')
  @include('partials.sideMenu.project', ['pid' => $project->id, 'openDrawer' => true])
@stop

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->id])
    @include('partials.menu.fieldValPresets')
@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-preset"></i>
                <span>Field Value Presets</span>
            </h1>
            <p class="description">Field Value Presets allow you to create predefined field options to be used repeatedly across all projects and forms. For more information on Field Value Presets, refer to the <a href="#">Field Value Presets - Kora Documentation.</a> You may Create a New Preset, or edit an existing preset here.</p>
        </div>
    </section>
@stop

@section('body')
    @include('partials.fieldValuePresets.deletePresetModal')
    @include('partials.projects.notification')

    @if(count($all_presets["Project"]) > 0 | count($all_presets["Shared"]) > 0 | count($all_presets["Stock"]) > 0)
      <section class="filters center">
          <div class="underline-middle search search-js">
              <i class="icon icon-search"></i>
              <input type="text" placeholder="Find a Preset">
              <i class="icon icon-cancel icon-cancel-js"></i>
          </div>
          <div class="sort-options sort-options-js">
              <a href="#all" class="option underline-middle underline-middle-hover active">All</a>
              <a href="#project" class="option underline-middle underline-middle-hover">Project</a>
              <a href="#shared" class="option underline-middle underline-middle-hover">Shared</a>
              <a href="#stock" class="option underline-middle underline-middle-hover">Stock</a>
          </div>
      </section>
    @endif

    <section class="new-object-button center">
        @if(\Auth::user()->admin)
          <form action="{{ action('FieldValuePresetController@newPreset', ['pid' => $project->id]) }}">
            <input type="submit" value="Create a New Preset">
          </form>
        @endif
    </section>

    <section class="option-presets-selection center">
        @if(count($all_presets["Project"]) > 0 | count($all_presets["Shared"]) > 0 | count($all_presets["Stock"]) > 0)
            @foreach($all_presets as $key => $presets)
                @foreach($presets as $index => $preset)
                    <div class="preset card all {{ $index == 0 ? 'active' : '' }} {{ $key=='Stock' ? 'stock' : '' }} {{ $key=='Project' ? 'project' : '' }} {{ $key=='Shared' ? 'shared' : '' }}" id="{{$preset->id}}">
                        <div class="header {{ $index == 0 ? 'active' : '' }}">
                            <div class="left pl-m">
                                <a class="title">
                                    <span class="name">{{$preset->preset['name']}}</span>
                                </a>
                            </div>

                            <div class="card-toggle-wrap">
                                <a href="#" class="card-toggle preset-toggle-js">
                                    <span class="chevron-text">{{$preset->preset['type']}}</span>
                                    <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
                                </a>
                            </div>
                        </div>

                        <div class="content {{ $index == 0 ? 'active' : '' }}">
                            <div class="id">
                                @if($preset->preset['type'] == "Text")
                                    <span class="attribute">Regex: </span>
                                    <span>{{$preset->preset['preset']}}</span>
                                @elseif($preset->preset['type'] == "List")
                                    <span class="attribute">Options: </span>
                                    <span>{{implode(', ',$preset->preset['preset'])}}</span>
                                @elseif($preset->preset['type'] == "Geolocator")
                                    <span class="attribute">Locations: </span>
                                    @foreach($preset->preset['preset'] as $event)
                                        <span class="field-preset-list">{{$event['description']}}: {{$event['geometry']['location']['lat'].', '.$event['geometry']['location']['lng']}}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="footer">
                                @if($key == "Stock" && Auth::user()->admin == 1 )
                                    <a class="quick-action trash-container left danger delete-preset-open-js tooltip" href="#" preset-id="{{$preset->id}}" tooltip="Delete Preset">
                                        <i class="icon icon-trash"></i>
                                    </a>

                                    <a class="quick-action preset-stock">
                                        <span>Stock Preset</span>
                                    </a>
                                @elseif($key=="Stock")
                                    <a class="quick-action preset-stock">
                                        <span>Stock Preset</span>
                                    </a>
                                @elseif($key=="Project")
                                    <a class="quick-action trash-container left danger delete-preset-open-js tooltip" href="#" preset-id="{{$preset->id}}" tooltip="Delete Preset">
                                        <i class="icon icon-trash"></i>
                                    </a>

                                    <a class="quick-action underline-middle-hover" href="{{action('FieldValuePresetController@edit',['pid'=>$project->id,'id'=>$preset->id])}}">
                                        <i class="icon icon-edit-little"></i>
                                        <span>Edit Preset</span>
                                    </a>
                                @else
                                    <a class="quick-action preset-stock">
                                        <span>Shared Preset [PID: {{$preset->project_id}}]</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        @else
            @include('partials.fieldValuePresets.no-presets')
        @endif
    </section>
@stop

@section('javascripts')
    @include('partials.fieldValuePresets.javascripts')

    <script type="text/javascript">
        var CSRFToken = '{{ csrf_token() }}';
        var deletePresetURL = '{{ action('FieldValuePresetController@delete', ['pid' => $project->id])}}';
        Kora.FieldValuePresets.Index();
    </script>
@stop
