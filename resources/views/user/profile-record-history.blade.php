@extends('app', ['page_title' => 'My Profile', 'page_class' => 'user-profile'])

@section('header')
    @include('partials.user.profile.head')
@stop

@section('body')
    @include('partials.revisions.modals.restoreFieldsModal')
    @include('partials.revisions.modals.reactivateRecordModal')
    <section class="center page-section page-section-js active" id="recordHistory">
        <div class="section-filters mt-xxxl">
            <a href="#recentlyModified" class="filter-link select-content-section-js underline-middle underline-middle-hover">Recently Modified</a>
            <a href="#myCreatedRecords" class="filter-link select-content-section-js underline-middle underline-middle-hover">@if (Auth::user()->id == $user->id) My @else {{$user->username}}'s @endif Created Records</a>
        </div>
        <div class="content-section content-section-js" id="recentlyModified">
            @if (count($userRevisions) > 0)
                <div class="my-xl">
                    <p>@if (Auth::user()->id == $user->id) You have @else {{$user->first_name}} has @endif recently modified the following {{$userRevisions->total()}} records...</p>
                </div>

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
                        <a href="#" class="expand-fields-js" title="Expand all fields"><i class="icon icon-expand icon-expand-js"></i></a>
                        <a href="#" class="collapse-fields-js" title="Collapse all fields"><i class="icon icon-condense icon-condense-js"></i></a>
                    </div>
                </section>

                @foreach ($userRevisions as $index=>$revision)
                    @include('partials.user.profile.userRevision')
                @endforeach

                @include('partials.user.profile.pagination', ['revisions' => $userRevisions])
            @else
                <div class="my-xl">
                    <p>@if (Auth::user()->id == $user->id) You have @else {{$user->first_name}} has @endif recently modified {{count($userRevisions)}} records...</p>
                </div>
            @endif
        </div>
        <div class="content-section content-section-js" id="myCreatedRecords">
            @if (count($userOwnedRevisions) > 0)
                <div class="my-xl">
                    <p>@if (Auth::user()->id == $user->id) You have @else {{$user->first_name}} has @endif recently modified the following {{$userRevisions->total()}} records...</p>
                </div>

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
                        <a href="#" class="expand-fields-js" title="Expand all fields"><i class="icon icon-expand icon-expand-js"></i></a>
                        <a href="#" class="collapse-fields-js" title="Collapse all fields"><i class="icon icon-condense icon-condense-js"></i></a>
                    </div>
                </section>

                @foreach ($userOwnedRevisions as $index=>$revision)
                    @include('partials.user.profile.userOwnedRevision')
                @endforeach

                @include('partials.user.profile.pagination', ['revisions' => $userOwnedRevisions])
            @else
                <div class="my-xl">
                    <p>@if (Auth::user()->id == $user->id) You have @else {{$user->first_name}} has @endif recently modified {{count($userRevisions)}} records...</p>
                </div>
            @endif
        </div>
    </section>
@stop


@section('javascripts')
    @include('partials.user.javascripts')

    <script type="text/javascript">
        Kora.User.Profile();
    </script>
@stop