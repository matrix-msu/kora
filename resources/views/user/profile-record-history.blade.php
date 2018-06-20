@extends('app', ['page_title' => (Auth::user()->id == $user->id ? 'My Profile' : $user->username), 'page_class' => 'user-profile'])

@section('header')
    @include('partials.user.profile.head')
@stop

@section('body')
    @include('partials.revisions.modals.restoreFieldsModal')
    @include('partials.revisions.modals.reactivateRecordModal')
    <section class="center page-section page-section-js active" id="recordHistory">
        <div class="section-filters mt-xxxl">
            <a href="#rm" class="filter-link select-content-section-js underline-middle underline-middle-hover {{$sec == 'rm' ? 'active' : ''}}">Recently Modified</a>
            <a href="#mcr" class="filter-link select-content-section-js underline-middle underline-middle-hover {{$sec == 'mcr' ? 'active' : ''}}">@if (Auth::user()->id == $user->id) My @else {{$user->username}}'s @endif Created Records</a>
        </div>
        <div class="content-section content-section-js {{$sec == 'rm' ? 'active' : ''}}" id="rm">
          <div class="content-sections-scroll">
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
                            <option value="lma" {{app('request')->input('rm-order') === 'lma' ? 'selected' : ''}}>Last Modified Ascending</option>
                            <option value="idd" {{app('request')->input('rm-order') === 'idd' ? 'selected' : ''}}>ID Descending</option>
                            <option value="ida" {{app('request')->input('rm-order') === 'ida' ? 'selected' : ''}}>ID Ascending</option>
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
                    <p>@if (Auth::user()->id == $user->id) You have @else {{$user->first_name}} has @endif recently modified {{$userRevisions->total()}} records...</p>
                </div>
            @endif
          </div>
        </div>

        <div class="content-section content-section-js {{$sec == 'mcr' ? 'active' : ''}}" id="mcr">
          <div class="content-sections-scroll">
            @if (count($userOwnedRevisions) > 0)
                <div class="my-xl">
                    <p>@if (Auth::user()->id == $user->id) You have @else {{$user->first_name}} has @endif recently modified the following {{$userOwnedRevisions->total()}} records...</p>
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
                            <option value="lma" {{app('request')->input('mcr-order') === 'lma' ? 'selected' : ''}}>Last Modified Ascending</option>
                            <option value="idd" {{app('request')->input('mcr-order') === 'idd' ? 'selected' : ''}}>ID Descending</option>
                            <option value="ida" {{app('request')->input('mcr-order') === 'ida' ? 'selected' : ''}}>ID Ascending</option>
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
                    <p>@if (Auth::user()->id == $user->id) You have @else {{$user->first_name}} has @endif recently modified {{$userOwnedRevisions->total()}} records...</p>
                </div>
            @endif
          </div>
        </div>
    </section>
@stop


@section('javascripts')
    @include('partials.user.javascripts')

    <script type="text/javascript">
        Kora.User.Profile();
    </script>
@stop