@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->pid])
@stop

@section('content')
    <h1>{{trans('optionPresets_index.preset')}}</h1>
    <hr/>

    @foreach($all_presets as $key =>$presets)
        @foreach($presets as $preset)
                <div class="panel panel-default">
                    <div class="panel-heading" style="font-size: 1.5em;">
                        <a href="#">{{ $preset->name }}</a> <span>[{{$preset->project()->first()->name or trans('optionPresets_index.stock') }}]</span>
                        @if($preset->shared)
                            ({{trans('optionPresets_index.shared')}})
                        @endif
                        <span class="pull-right">{{$preset->type}}</span>
                    </div>
                    <div class="collapseTest" style="display:none">
                        @if($preset->type == "Text")
                            <div class="panel-body"><strong>{{trans('optionPresets_index.regex')}}:</strong> {{ $preset->preset }}</div>
                        @elseif($preset->type == "List")
                            <div class="panel-body">
                                <strong>{{trans('optionPresets_index.options')}}:</strong>
                                {{implode(', ',explode("[!]",$preset->preset))}}
                            </div>
                        @elseif($preset->type == "Schedule")
                            <div class="panel-body">
                                <strong>{{trans('optionPresets_index.events')}}:</strong>
                                <ul style="list-style: none;">
                                    @foreach(explode("[!]",$preset->preset) as $event)
                                        <li>{{$event}}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @elseif($preset->type == "Geolocator")
                            <div class="panel-body">
                                <strong>{{trans('optionPresets_index.loc')}}:</strong>
                                <ul style="list-style: none;">
                                    @foreach(explode("[!]",$preset->preset) as $event)
                                        <li>{{$event}}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="panel-footer">
                            @if($key == "Stock" && Auth::user()->admin ==1 )
                                <span>
                                    <a onclick="deletePreset({{$preset->id}})" href="#">[{{trans('optionPresets_index.delete')}}]</a>
                                </span>
                            @elseif($key=="Project")
                                <span>
                                    <a href="{{action('OptionPresetController@edit',['pid'=>$project->pid,'id'=>$preset->id])}}">[{{trans('optionPresets_index.edit')}}]</a>
                                </span>

                                <span>
                                    <a onclick="deletePreset({{$preset->id}})" href="#">[{{trans('optionPresets_index.delete')}}]</a>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
        @endforeach
    @endforeach

    @if(\Auth::user()->canCreateForms($project))
        <form action="{{ action('OptionPresetController@create', ['pid' => $project->pid]) }}">
            <input type="submit" value="{{trans('optionPresets_index.create')}}" class="btn btn-primary form-control">
        </form>
    @endif
@stop

@section('footer')
    <script>
        $( ".panel-heading" ).on( "click", function() {
            if ($(this).siblings('.collapseTest').css('display') == 'none' ){
                $(this).siblings('.collapseTest').slideDown();
            }else {
                $(this).siblings('.collapseTest').slideUp();
            }
        });

        function deletePreset(presetId) {
            var encode = $('<div/>').html("{{ trans('optionPresets_index.areyousure') }}").text();
            var response = confirm(encode + "?");
            if (response) {
                $.ajax({
                    url: '{{ action('OptionPresetController@delete')}}',
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "presetId": presetId
                    },
                    success: function (result) {
                        location.reload();
                    },
                    error: function(result){
                        location.reload();
                    }
                });
            }
        }
    </script>
@stop