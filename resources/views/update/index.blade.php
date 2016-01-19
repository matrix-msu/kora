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
                            @if ($git)
                                <button formaction="{{action('UpdateController@gitUpdate')}}" class="btn btn-primary form-control">{{trans('update_index.update')}}</button>
                            @else
                                <button formaction="{{action('UpdateController@independentUpdate')}}" class="btn btn-primary form-control">{{trans('update_index.update')}}</button>
                            @endif
                        @else
                            {{trans('update_index.none')}}!
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>


@stop