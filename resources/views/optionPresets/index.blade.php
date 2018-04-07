@extends('app', ['page_title' => 'Field Value Presets', 'page_class' => 'option-presets'])

@section('stylesheets')
    <!-- No Additional Stylesheets Necessary -->
@stop

@section('aside-content')
    @include('partials.sideMenu.dashboard')
@stop

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->pid])
@stop

@section('header')
    <section class="head">
        <a class="rotate" href="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-preset"></i>
                <span>Field Value Presets</span>
            </h1>
            <p class="description">Select a preset below or create a preset to get started.</p>
        </div>
    </section>
@stop

@section('body')
    @include('partials.optionPresets.deletePresetModal')

    <section class="new-object-button center">
        <form action="{{ action('OptionPresetController@newPreset', ['pid' => $project->pid]) }}">
            @if(\Auth::user()->admin)
                <input type="submit" value="Create a New Preset">
            @endif
        </form>
    </section>

    <section class="option-presets-selection center">
        @foreach($all_presets as $key => $presets)
            @foreach($presets as $index => $preset)
                <div class="preset card all {{ $index == 0 ? 'active' : '' }}" id="{{$preset->id}}">
                    <div class="header {{ $index == 0 ? 'active' : '' }}">
                        <div class="left pl-m">
                            <a class="title">
                                <span class="name">{{$preset->name}}</span>
                            </a>
                        </div>

                        <div class="card-toggle-wrap">
                            <a href="#" class="card-toggle preset-toggle-js">
                                <span class="chevron-text">{{$preset->type}}</span>
                                <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
                            </a>
                        </div>
                    </div>

                    <div class="content {{ $index == 0 ? 'active' : '' }}">
                        <div class="id">
                            @if($preset->type == "Text")
                                <span class="attribute">Regex: </span>
                                <span>{{$preset->preset}}</span>
                            @elseif($preset->type == "List")
                                <span class="attribute">Options: </span>
                                <span>{{implode(', ',explode("[!]",$preset->preset))}}</span>
                            @elseif($preset->type == "Schedule")
                                <span class="attribute">Events: </span>
                                @foreach(explode("[!]",$preset->preset) as $event)
                                    <span class="field-preset-list">{{$event}}</span>
                                @endforeach
                            @elseif($preset->type == "Geolocator")
                                <span class="attribute">Locations: </span>
                                @foreach(explode("[!]",$preset->preset) as $event)
                                    <span class="field-preset-list">{{explode("[Desc]",$event)[1]}}: {{explode("[LatLon]",$event)[1]}}</span>
                                @endforeach
                            @endif
                        </div>

                        <div class="footer">
                            @if($key == "Stock" && Auth::user()->admin ==1 )
                                <a class="quick-action trash-container left danger delete-preset-open-js" href="#" preset-id="{{$preset->id}}">
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
                                <a class="quick-action trash-container left danger delete-preset-open-js" href="#" preset-id="{{$preset->id}}">
                                    <i class="icon icon-trash"></i>
                                </a>

                                <a class="quick-action underline-middle-hover" href="{{action('OptionPresetController@edit',['pid'=>$project->pid,'id'=>$preset->id])}}">
                                    <i class="icon icon-edit-little"></i>
                                    <span>Edit Preset</span>
                                </a>
                            @else
                                <a class="quick-action preset-stock">
                                    <span>Shared Preset [{{$preset->pid}}]</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach
    </section>
@stop

@section('javascripts')
    @include('partials.optionPresets.javascripts')

    <script type="text/javascript">
        var CSRFToken = '{{ csrf_token() }}';
        var deletePresetURL = '{{ action('OptionPresetController@delete', ['pid' => $project->pid])}}';
        Kora.OptionPresets.Index();
    </script>
@stop