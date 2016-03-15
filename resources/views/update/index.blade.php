@extends('app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{trans('update_index.update')}}
                    </div>

                    <div style="" class="panel-body">
                        <div style="" id="update">
                            @if($update)
                                <p>{{trans('update_index.updaterequired')}} <a href="http://matrix-msu.github.io/Kora3/">Kora 3 Info.</a></p>
                                <form action="{{action("UpdateController@runScripts")}}">
                                    <button onclick="showProgress()" type="submit" class="btn btn-primary form-control">{{trans('update_index.runscripts')}}</button>
                                </form>
                            @else
                                {{trans('update_index.none')}}!
                            @endif
                        </div>
                        <div style="display:none; margin-top: 1em;" id="progress" class="progress">
                            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
                                {{trans('update_index.loading')}}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    <script>
        /**
         * Displays the loading bar in the "progress" div.
         */
        function showProgress() {
            $("#update").css("display", "none");
            $("#progress").css("display", "");
        }
    </script>
@stop