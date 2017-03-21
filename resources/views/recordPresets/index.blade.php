@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <span><h3>{{trans('recordPresets_index.preset')}}</h3></span>
                    </div>

                    <div class="panel-body">

                        @foreach($presets as $preset)
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <span id="name{{$preset->id}}">{{$preset->name}}</span>
                                </div>
                                <div class="collapseTest" style="display: none">
                                    <div>{{trans('recordPresets_index.record')}}
                                        @if(App\Http\Controllers\RecordController::exists($preset->rid))
                                            KID: <a href="{{action('RecordController@show', ['pid' => $form->pid, 'fid' => $form->fid, 'rid' => $preset->rid])}}">
                                                {{$form->pid}}-{{$form->fid}}-{{$preset->rid}}
                                            </a>
                                        @else
                                            <p>{{$form->pid}}-{{$form->fid}}-{{$preset->rid}} ({{trans('recordPresets_index.recordDeleted')}})</p>
                                        @endif
                                    </div>
                                    <div>
                                        <input name="presetname{{$preset->id}}" id="presetname{{$preset->id}}" placeholder="{{$preset->name}}">
                                        <button onclick="changePresetName({{$preset->id}})">{{trans('recordPresets_index.change')}}</button>
                                    </div>
                                    <a href="javascript:void(0)" onclick="deletePreset({{$preset->id}})">[{{trans('recordPresets_index.remove')}}]</a>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    <script>

        /**
         * Changes a particular preset's name.
         *
         * @param id {int} The id of the preset we need to change.
         */
        function changePresetName(id) {
            var field = $('#presetname'+id);
            var name = field.val();

            field.attr('placeholder', name);
            document.getElementById('name'+id).innerHTML = name;

            $.ajax({
                url: '{{action('RecordPresetController@changePresetName')}}',
                type: 'PATCH',
                data: {
                    '_token': '{{csrf_token()}}',
                    'id': id,
                    'name': name
                }
            });
        }

        /**
         * The Ajax to delete a preset.
         *
         * @param id {int} The id of the preset to be deleted.
         */
        function deletePreset(id) {
            $.ajax({
                url: '{{action('RecordPresetController@deletePreset')}}',
                type: 'DELETE',
                data: {
                    '_token': '{{csrf_token()}}',
                    'id': id
                },
                success: function() {
                    location.reload();
                }
            });
        }

        /**
         * The collapsing display jQuery.
         */
        $( ".panel-heading" ).on( "click", function() {
            if ($(this).siblings('.collapseTest').css('display') == 'none' ){
                $(this).siblings('.collapseTest').slideDown();
            }else {
                $(this).siblings('.collapseTest').slideUp();
            }
        });
    </script>
@stop