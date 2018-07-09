@extends('app', ['page_title' => 'My Preferences', 'page_class' => 'user-preferences'])

@section('aside-content')
    @include('partials.sideMenu.dashboard', ['openDashboardDrawer' => false, 'openProjectDrawer' => false])
@stop

@section('header')
    <section class="head">
        <a class="back" href="{{ URL::previous() }}"><i class="icon icon-chevron"></i></a>
        <div class="inner-wrap center">
            <h1 class="title">
                <i class="icon icon-check-circle"></i>
                <span class="name">My Preferences</span>
            </h1>
            <p class="description">Use the switches below to modify your kora preferences.</p>
        </div>
    </section>
@stop

@section('body')
    <section class="edit-preferences center">
        {!! Form::open(['method' => 'PATCH', 'url' => action('Auth\UserController@updatePreferences', ['uid' => $user->id]), 'enctype' => 'multipart/form-data', 'class' => ['edit-preferences-form']]) !!}
            <div class="form-group">
                <h2 class="sub-title">Use Dashboard?</h2>
                <p class="description">You can select to turn the dashboard off entirely.</p>
                <div class="check-box-half">
                    <input type="checkbox" checked value="true" name="useDashboard" class="check-box-input check-box-input-js" />
                    <span class="check"></span>
                    <span class="placeholder">Use Dashboard</span>
                </div>
                <div class="check-box-half">
                    <input type="checkbox" value="false" name="useDashboard" class="check-box-input check-box-input-js" />
                    <span class="check"></span>
                    <span class="placeholder">Do Not Use Dashboard</span>
                </div>
            </div>

            <div class="form-group mt-xxxl">
                <div class="spacer"></div>
            </div>

            <div class="form-group mt-xxxl">
                <h2 class="sub-title">Kora Logo Target</h2>
                <p class="description">When selecting the Kora logo in the top left corner of the dashboard,
                    where would you like to be taken?</p>
                <div class="check-box-half">
                    <input type="checkbox" checked value="dashboard" name="logoTarget" class="check-box-input check-box-input-js" />
                    <span class="check"></span>
                    <span class="placeholder">Dashboard</span>
                </div>
                <div class="check-box-half">
                    <input type="checkbox" value="projects" name="logoTarget" class="check-box-input check-box-input-js" />
                    <span class="check"></span>
                    <span class="placeholder">Projects</span>
                </div>
            </div>

            <div class="form-group mt-xxxl">
                <div class="spacer"></div>
            </div>

            <div class="form-group mt-xxxl">
                <h2 class="sub-title">Projects Page Tab Selection</h2>
                <p class="description">Select which tab you wish to be displayed when coming to the  Projects page.</p>
                <div class="check-box-half">
                    <input type="checkbox" checked value="recentlyModified" name="projPageTabSel" class="check-box-input check-box-input-js" />
                    <span class="check"></span>
                    <span class="placeholder">Recently Modified</span>
                </div>
                <div class="check-box-half">
                    <input type="checkbox" value="custom" name="projPageTabSel" class="check-box-input check-box-input-js" />
                    <span class="check"></span>
                    <span class="placeholder">Custom</span>
                </div>
                <div class="check-box-half">
                    <input type="checkbox" value="alphabetical" name="projPageTabSel" class="check-box-input check-box-input-js" />
                    <span class="check"></span>
                    <span class="placeholder">Alphabetical</span>
                </div>
            </div>

            <div class="form-group mt-xxxl">
                <div class="spacer"></div>
            </div>

            <div class="form-group mt-xxxl">
                <h2 class="sub-title">Keep Side Menu Open on Wider Screens?</h2>
                <p class="description">On wider screens, you can set to have the slide in side menu to remain open,
                    even after clicking off the side menu, and when navigating to different pages.
                    You can also use the Lock symbol found at the bottom of the side menu to keep it open
                    or let is close automatically.</p>
                <div class="check-box-half">
                    <input type="checkbox" checked value="true" name="sideMenuOpen" class="check-box-input check-box-input-js" />
                    <span class="check"></span>
                    <span class="placeholder">Keep Side Menu Open</span>
                </div>
                <div class="check-box-half">
                    <input type="checkbox" value="false" name="sideMenuOpen" class="check-box-input check-box-input-js" />
                    <span class="check"></span>
                    <span class="placeholder">Let Side Menu Close Automatically</span>
                </div>
            </div>

            <div class="form-group mt-xxxl">
                <div class="spacer"></div>
            </div>

            <div class="form-group my-xxxl">
                <h2 class="sub-title">Replay Kora Introduction?</h2>
                <p class="description">On wider screens, you can set to have the slide in side menu to remain open,
                    even after clicking off the side menu, and when navigating to different pages.
                    You can also use the Lock symbol found at the bottom of the side menu to keep it open
                    or let is close automatically.</p>
                <p><a href="#" class="text underline-middle-hover">Replay Kora Introduction</a></p>
            </div>

            <div class="form-group preferences-update-button">
                {!! Form::submit('Update Preferences',['class' => 'btn edit-btn update-preferences-submit pre-fixed-js']) !!}
            </div>
        {!! Form::close() !!}
    </section>
@stop

@section('javascripts')
    @include('partials.user.javascripts')

    <script type="text/javascript">
        Kora.User.Preferences();
    </script>
@stop