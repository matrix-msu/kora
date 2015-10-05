@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $project->pid])
@stop

@section('content')
    <h1>Field Option Presets</h1>
    <hr/>

    @foreach($all_presets as $key =>$presets)
        @foreach($presets as $preset)
                <div class="panel panel-default">
                    <div class="panel-heading" style="font-size: 1.5em;">
                        <a href="#">{{ $preset->name }}</a> <span>[{{$preset->project()->first()->name or 'Stock' }}]</span>
                        @if($preset->shared)
                            (Shared)
                        @endif
                        <span class="pull-right">{{$preset->type}}</span>
                    </div>
                    <div class="collapseTest" style="display:none">
                        @if($preset->type == "Text")
                            <div class="panel-body"><strong>Regex:</strong> {{ $preset->preset }}</div>
                        @elseif($preset->type == "List")
                            <div class="panel-body">
                                <strong>Options:</strong>
                                {{implode(', ',explode("[!]",$preset->preset))}}
                            </div>
                        @elseif($preset->type == "Schedule")
                            <div class="panel-body">
                                <strong>Events:</strong>
                                <ul style="list-style: none;">
                                    @foreach(explode("[!]",$preset->preset) as $event)
                                        <li>{{$event}}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @elseif($preset->type == "Geolocator")
                            <div class="panel-body">
                                <strong>Locations:</strong>
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
                                    <a onclick="deletePreset({{$preset->id}})" href="#">[Delete]</a>
                                </span>
                            @elseif($key=="Project")
                                <span>
                                    <a href="{{action('OptionPresetController@edit',['pid'=>$project->pid,'id'=>$preset->id])}}">[Edit]</a>
                                </span>

                                <span>
                                    <a onclick="deletePreset({{$preset->id}})" href="#">[Delete]</a>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
        @endforeach
    @endforeach

    @if(\Auth::user()->canCreateForms($project))
        <form action="{{ action('OptionPresetController@create', ['pid' => $project->pid]) }}">
            <input type="submit" value="Create New Preset" class="btn btn-primary form-control">
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
            var response = confirm("Are you sure you want to delete this preset?");
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