@extends('app', ['page_title' => "Record Revisions", 'page_class' => 'record-revisions'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
    @include('partials.menu.static', ['name' => 'Record Revisions'])
@stop

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-clock"></i>
                <span>Record Revisions</span>
            </h1>
            <p class="description">
                Use this page to view and manage record revisions within this form.
                Record revisions allow you to see all of the changes made to all the records within the form,
                and gives you the ability to revert records to a previous revision.
            </p>
        </div>
    </section>
@stop

@section('body')
    <section class="record-select-section center">
        <div class="form-group">
            <label for="record-select">Select Record(s) to Show Revisions For</label>
            <select class="multi-select" id="record-select" name="record"
                data-placeholder="Currently Showing All Records">
                <option></option>
                @foreach ($records as $index=>$record)
                    <option>{{$record}}</option>
                @endforeach
            </select>
        </div>
    </section>
    <section class="filters center">
        <div class="pagination-options pagination-options-js">
            <select class="page-count option-dropdown-js" id="page-count-dropdown">
                <option value="10">10 per page</option>
                <option value="20" {{app('request')->input('page-count') === '20' ? 'selected' : ''}}>20 per page</option>
                <option value="30" {{app('request')->input('page-count') === '30' ? 'selected' : ''}}>30 per page</option>
            </select>
            <select class="order option-dropdown-js" id="order-dropdown">
                <option>Last Modified Descending</option>
                <option>Last Modified Ascending</option>
                <option>ID Descending</option>
                <option>ID Ascending</option>
            </select>
        </div>
        <div class="show-options show-options-js">
            <a href="#" class="expand-fields-js" title="Expand all fields"><i class="icon icon-expand icon-expand-js"></i></a>
            <a href="#" class="collapse-fields-js" title="Collapse all fields"><i class="icon icon-condense icon-condense-js"></i></a>
        </div>
    </section>
    <section class="revisions revisions-js center">
        @foreach ($revisions as $index=>$revision)
            @include('partials.revisions.card')
        @endforeach
    </section>
    <section class="pagination center">
        <div class="previous page {{$revisions->onFirstPage() ? 'disabled' : ''}}">
            <a href="{{$revisions->previousPageUrl()}}">
                <i class="icon icon-chevron left"></i>
                <span class="name underline-middle-hover">Previous</span>
            </a>
        </div>
        <div class="pages">
            @if (!$revisions->onFirstPage())
                <a href="{{$revisions->url(1)}}" class="page-link">1</a>
                @if ($revisions->currentPage() > 2)
                    @if ($revisions->currentPage() > 3)
                        <span class="page-link">...</span>
                    @endif
                    <a href="{{$revisions->previousPageUrl()}}" class="page-link">{{$revisions->currentPage() - 1}}</a>
                @endif
            @endif
            <a href="" class="page-link active">{{$revisions->currentPage()}}</a>
            @if ($revisions->lastPage() !== $revisions->currentPage())
                @if ($revisions->lastPage() - 1 > $revisions->currentPage())
                    <a href="{{$revisions->url($revisions->currentPage()+1)}}" class="page-link">{{$revisions->currentPage() + 1}}</a>
                    @if ($revisions->lastPage() - 2 > $revisions->currentPage())
                        <span class="page-link">...</span>
                    @endif
                @endif
                <a href="{{$revisions->url($revisions->lastPage())}}" class="page-link">{{$revisions->lastPage()}}</a>
            @endif
        </div>
        <div class="next page {{$revisions->hasMorePages() ? '' : 'disabled'}}">
            <a href="{{$revisions->nextPageUrl()}}">
                <i class="icon icon-chevron right"></i>
                <span class="name underline-middle-hover">Next</span>
            </a>
        </div>
    </section>
@stop

@section('javascripts')
    @include('partials.revisions.javascripts')
@stop