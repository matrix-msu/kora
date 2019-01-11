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
            <img src="{{ url('/assets/images/onboarding/newFeatures.png') }}" alt="Kora onboarding animated what's-new picture">
        </div>
        <div class="body onboarding">
            <h3>Great!  Here's what's new in Kora 3.0!</h3>
            <ul>
				<li>A brand new user interface that is simple and easy to use</li>
				<li>A personalized dashboard for getting to what's important to you faster</li>
				<li>Improved global search to get to everything you need quicker</li>
				<li>Revamped breadcrumb menu-bar system, and a new robust, persistent side-bar</li>
				<li>Receive the latest update quickly and easily</li>
				<li>Full backup system to create restore points for Kora3</li>
				<li>Powerful tools to import your Kora 2 data into Kora3</li>
			</ul>
			<p>But wait, there's more!  To read about all of Kora 3.0s new features, feel free to <a href="#">read our post about it here</a>.</p>
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
            <img src="{{ url('/assets/images/onboarding/permissions.png') }}" alt="Kora onboarding animated permissions picture">
        </div>
        <div class="body onboarding">
            <h3>Do you need project permissions?</h3>
            <p><span class="bold">If you're supposed to be apart of a certain project, you should request permissions to it.</span>  You can always request project permissions on the Projects page later on.</p>
			<p><span class="bold">You currently have been given access to the following project(s):</span></p>
			<ul>
				<li>Project Name</li>
				<li>Other Project Name</li>
			</ul>
			<div class="form-group mt-xxl">
                <?php $projects = array('1' => 'project 1', '2' => 'project 2', '3' => 'project 3', '4' => 'project 4', '5' => 'project 5', '6' => 'project 6', '7' => 'project 7', '8' => 'project 8', '9' => 'project 9', '10' => 'project 10', '11' => 'project 11', '12' => 'project 12', '13' => 'project 13'); ?>
                {!! Form::label('projects', 'Select the Project to Request Permissions') !!}
                {!! Form::select('projects[]', $projects, null, [
                    'class' => 'multi-select',
                    'multiple',
                    'data-placeholder' => "Select the project you would like to request permissions to here"
                ]) !!}
            </div>
        </div>
    </section>
</div>