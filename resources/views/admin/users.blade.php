@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">

                        @include('admin.form')

                    </div>
                </div>
            </div>
        </div>
    </div>


@stop