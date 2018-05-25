@extends('app', ['page_title' => 'My Profile', 'page_class' => 'user-profile'])

@section('header')
    <section class="head">
        <div class="inner-wrap center">
            <h1 class="title">
                @if ($user->profile)
                    <img class="profile-pic" src="{{ $user->getProfilePicUrl() }}" alt="Profile Pic">
                @else
                    <i class="icon icon-user"></i>
                @endif
                <span class="ml-m">{{$user->first_name}} {{$user->last_name}}</span>
                @if(\Auth::user()->admin | \Auth::user()->id==$user->id)
                    <a href="{{ action('Auth\UserController@editProfile',['uid' => $user->id]) }}" class="head-button">
                        <i class="icon icon-edit right"></i>
                    </a>
                @endif
            </h1>
            <div class="content-sections">
                <a href="{{url('user', ['uid' => $user->id])}}" class="section select-section-js underline-middle underline-middle-hover">Profile</a>
                <a href="{{url('user', ['uid' => $user->id, 'section' => 'permissions'])}}" class="section select-section-js underline-middle underline-middle-hover">Permissions</a>
                <a href="{{url('user', ['uid' => $user->id, 'section' => 'history'])}}" class="section select-section-js underline-middle underline-middle-hover active">Record History</a>
            </div>
        </div>
    </section>
@stop

@section('body')
        <section class="center page-section page-section-js active" id="recordHistory">
        <div class="section-filters mt-xxxl">
            <a href="#recentlyModified" class="filter-link select-content-section-js underline-middle underline-middle-hover">Recently Modified</a>
            <a href="#myCreatedRecords" class="filter-link select-content-section-js underline-middle underline-middle-hover">My Created Records</a>
        </div>
        <div class="content-section content-section-js" id="recentlyModified">
            @if (count($userRevisions) > 0)
                <div class="my-xl">
                    <p>@if (Auth::user()->id == $user->id) You have @else {{$user->first_name}} has @endif recently modified the following {{count($userRevisions)}} records...</p>
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
                    <p>@if (Auth::user()->id == $user->id) You have @else {{$user->first_name}} has @endif recently modified the following {{count($userRevisions)}} records...</p>
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