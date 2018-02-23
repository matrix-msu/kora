@extends('app', ['page_title' => 'Form Records', 'page_class' => 'record-index'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
    @include('partials.menu.static', ['name' => 'Form Records'])
@stop

@section('stylesheets')

@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-form-record-search mr-sm"></i>
                <span>Search Form Records</span>
            </h1>
            <p class="description">Enter keywords to search below. A keyword is required in order to search project
                records. You can also search by a specific form, and filter by “Or”, “And”, or “Exact” keyword results. </p>
        </div>
    </section>
@stop

@section('body')
    <section class="view-records center">
        <section class="search-records">
            <form method="GET" action="{{action('FormSearchController@keywordSearch',['pid' => $form->pid, 'fid' => $form->fid])}}" >
                <div class="form-group search-input mt-xl">
                    {!! Form::label('keywords','Search Via Keyword(s) or KID : ') !!}
                    {!! Form::select('keywords[]',[], null, ['class' => 'multi-select modify-select', 'multiple']) !!}
                </div>
                <div class="form-group search-input mt-xl">
                    {!! Form::label('method','or / and / exact') !!}
                    {!! Form::select('method',[0 => 'or',1 => 'and',2 => 'exact'], 0, ['class' => 'single-select']) !!}
                </div>

                <div class="form-group mt-xxxl">
                    <a href="#" class="btn half-sub-btn pr-m" data-unsp-sanitized="clean">View Advanced Search Options</a>
                    <a href="#" class="btn half-btn pl-m" data-unsp-sanitized="clean">Search</a>
                </div>
            </form>

            <div class="form-group mt-xxxl">
                <div class="spacer"></div>
            </div>
        </section>

        <section class="display-records">
            <div class="form-group records-title mt-xxxl">
                Showing all Records for Now!
            </div>

            @foreach($records as $index => $record)
                @include('partials.records.card')
            @endforeach
        </section>
    </section>
@stop

@section('footer')

@stop

@section('javascripts')
    @include('partials.records.javascripts')

    <script type="text/javascript">
        Kora.Records.Index();
    </script>
@stop