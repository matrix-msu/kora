@extends('app', ['page_title' => "Record Revisions", 'page_class' => 'record-revisions'])

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
    @if(isset($rid) && !is_null($record))
        @include('partials.menu.record', ['pid' => $form->pid, 'fid' => $form->fid, 'rid' => $rid])
    @endif
    @include('partials.menu.static', ['name' => 'Record Revisions'])
@stop

@section('aside-content')
  @if (isset($rid))
      @include('partials.sideMenu.form', ['pid' => $form->pid, 'fid' => $form->fid])
      @include('partials.sideMenu.record', ['pid' => $form->pid, 'fid' => $form->fid, 'rid' => $rid, 'openDrawer' => true])
  @else
    @include('partials.sideMenu.form', ['pid' => $form->pid, 'fid' => $form->fid, 'openDrawer' => true])
  @endif
@stop

@section('header')
    <section class="head">
        <a class="back" href=""><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-clock"></i>
                <span>Record Revisions{{isset($rid) ? ': ' . $form->pid . '-' . $form->fid . '-' . $rid : ''}}</span>
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
    @include('partials.revisions.modals.restoreFieldsModal')
    @include('partials.revisions.modals.reactivateRecordModal')
    @include('partials.projects.notification')
    @if (!isset($rid) || Request::get('revisions'))
      @if (count($revisions) > 0)
        <section class="record-select-section center">
            <div class="form-group">
                <label for="record-select">Select Record to Show Revisions For</label>
                <select class="single-select" id="record-select" name="record"
                    data-placeholder="Currently Showing All Records">
                    <option></option>
                    @if (isset($rid))
                        <option>View All Records</option>
                    @endif
                    @foreach ($records as $index=>$record)
                        <option {{isset($rid) && explode('-', $record)[2] === $rid ? 'selected' : ''}}>{{$record}}</option>
                    @endforeach
                </select>
            </div>
        </section>
      @endif
    @endif
    @if (count($revisions) > 0 )
    <section class="filters center">
        <div class="pagination-options pagination-options-js">
            <select class="page-count option-dropdown-js" id="page-count-dropdown">
                <option value="10">10 per page</option>
                <option value="20" {{app('request')->input('page-count') === '20' ? 'selected' : ''}}>20 per page</option>
                <option value="30" {{app('request')->input('page-count') === '30' ? 'selected' : ''}}>30 per page</option>
            </select>
            <select class="order option-dropdown-js" id="order-dropdown">
                <option value="lmd">Last Modified Descending</option>
                <option value="lma" {{app('request')->input('order') === 'lma' ? 'selected' : ''}}>Last Modified Ascending</option>
                <option value="idd" {{app('request')->input('order') === 'idd' ? 'selected' : ''}}>ID Descending</option>
                <option value="ida" {{app('request')->input('order') === 'ida' ? 'selected' : ''}}>ID Ascending</option>
            </select>
        </div>
        <div class="show-options show-options-js">
            <a href="#" class="tooltip expand-fields-js" tooltip="Expand all fields"><i class="icon icon-expand icon-expand-js"></i></a>
            <a href="#" class="tooltip collapse-fields-js" tooltip="Collapse all fields"><i class="icon icon-condense icon-condense-js"></i></a>
        </div>
    </section>
      <section class="revisions revisions-js center">
          @foreach ($revisions as $index=>$revision)
              @include('partials.revisions.card')
          @endforeach
      </section>
      @include('partials.revisions.pagination')
    @else
      @include('partials.revisions.no-revisions')
    @endif
@stop

@section('javascripts')
    @include('partials.revisions.javascripts')
@stop
