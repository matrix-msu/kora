<div class="hidden familiar-with-kora familiar-with-kora-slides-js">
    <section class="hidden" id="WhatsNew">
        <div class="header onboarding">
            <span class="title">
                <span class="skip">
                    <span>SKIP INTRO</span>
                    <a href="#" class="modal-toggle modal-toggle-js">
                        <i class="icon icon-cancel"></i>
                    </a>
                </span>
            </span>
            <img src="{{ url('/assets/images/onboarding/newFeatures.png') }}" alt="kora onboarding animated what's-new picture">
        </div>
        <div class="body onboarding">
            <h3>Great!  Here's what's new in kora v3!</h3>
            <ul>
				<li>A brand new user interface that is simple and easy to use</li>
				<li>A personalized dashboard for getting to what's important to you faster</li>
				<li>Improved global search to get to everything you need quicker</li>
				<li>Revamped breadcrumb menu-bar system, and a new robust, persistent side-bar</li>
				<li>Receive the latest update quickly and easily</li>
				<li>Powerful tools to import your kora 2 data into kora v3</li>
			</ul>
			<p>But wait, there's more!  To read about all of kora's new features, feel free to <a href="#">read our post about it here</a>.</p>
        </div>
    </section>
    <section class="hidden" id="Permissions">
        <div class="header onboarding">
            <span class="title">
                <span class="skip">
                    <span>FINISH INTRO</span>
                    <a href="#" class="modal-toggle modal-toggle-js">
                        <i class="icon icon-cancel"></i>
                    </a>
                </span>
            </span>
            <img src="{{ url('/assets/images/onboarding/permissions.png') }}" alt="kora onboarding animated permissions picture">
        </div>
        <div class="body onboarding">
            <h3>Do you need project permissions?</h3>
            <p><span class="bold">If you're supposed to be apart of a certain project, you should request permissions to it.</span> Please contact your system administrator.</p>
			<p><span class="bold">You currently have been given access to the following project(s):</span></p>
            @php $projects = \App\Http\Controllers\Auth\UserController::getOnboardingProjects(\Auth::user()); @endphp
			<ul>
                @foreach ($projects[0] as $index=>$project)
                    <li>{{ $project }}</li>
                @endforeach
			</ul>
			<div class="form-group mt-xxl">
            </div>
        </div>
    </section>
</div>
