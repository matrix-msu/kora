@extends('app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{trans('update_index.update')}}
                    </div>

                    <div class="panel-body">
                        @if($update)
                            <p>{{trans('update_index.updaterequired')}} <a href="http://matrix-msu.github.io/Kora3/">Kora 3 Info.</a></p>
                            <form action="{{action("UpdateController@runScripts")}}">
                                <button type="submit" class="btn btn-primary form-control">{{trans('update_index.runscripts')}}</button>
                            </form>
                        @else
                            {{trans('update_index.none')}}!
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>


@stop