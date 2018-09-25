<div class="form-group">
    <h2 class="sub-title">Use Dashboard?</h2>
    <p class="description">You can select to turn the dashboard off entirely.</p>

    <div class="check-box-half">
        <input type="checkbox" {{ ($preference->use_dashboard ? 'checked' : '') }} value="true" name="useDashboard" class="check-box-input check-box-input-js" />
        <span class="check"></span>
        <span class="placeholder">Use Dashboard</span>
    </div>
    <div class="check-box-half">
        <input type="checkbox" {{ (!$preference->use_dashboard ? 'checked' : '') }} value="false" name="useDashboard" class="check-box-input check-box-input-js" />
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
    @foreach ($logoTargetOptions as $key => $name)
        <div class="check-box-half">
            <input type="checkbox" {{ ($preference->logo_target == $key ? 'checked' : '') }} value="{{ $key }}" name="logoTarget" class="check-box-input check-box-input-js" />
            <span class="check"></span>
            <span class="placeholder">{{ $name }}</span>
        </div>
    @endforeach

</div>

<div class="form-group mt-xxxl">
    <div class="spacer"></div>
</div>

<div class="form-group mt-xxxl">
    <h2 class="sub-title">Projects Page Tab Selection</h2>
    <p class="description">Select which tab you wish to be displayed when coming to the Projects page.</p>
    @foreach ($projPageTabSelOptions as $key => $name)
        <div class="check-box-half">
            <input type="checkbox" {{ ($preference->proj_page_tab_selection == $key ? 'checked' : '') }} value="{{ $key }}" name="projPageTabSel" class="check-box-input check-box-input-js" />
            <span class="check"></span>
            <span class="placeholder">{{ $name }}</span>
        </div>
    @endforeach
</div>

<div class="form-group mt-xxxl">
    <div class="spacer"></div>
</div>

<div class="form-group mt-xxxl">
    <h2 class="sub-title">Single Project Page Tab Selection</h2>
    <p class="description">Select which tab you wish to be displayed when coming to the a single project main page.</p>
    @foreach ($singleProjTabSelOptions as $key => $name)
        <div class="check-box-half">
            <input type="checkbox" {{ ($preference->single_proj_page_tab_selection == $key ? 'checked' : '') }} value="{{ $key }}" name="singleProjPageTabSel" class="check-box-input check-box-input-js" />
            <span class="check"></span>
            <span class="placeholder">{{ $name }}</span>
        </div>
    @endforeach
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