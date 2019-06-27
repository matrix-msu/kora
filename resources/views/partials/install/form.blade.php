{!! csrf_field() !!}

<!-- Section 1 -->
<section class="database-section">
    <div class="section-title">Database Setup</div>

    <div class="section-desc mt-m">
        Information for your database server
    </div>

    <div class="form-group mt-xl">
        <label for="db_host">Host</label>
        <span class="error-message"></span>
        <input class="text-input" placeholder="Enter the host name here" type="text" id="db_host" name="db_host" value="{{old('db_host')}}">
    </div>

    <div class="form-group mt-xl">
        <label for="db_database">DB Name</label>
        <span class="error-message"></span>
        <input class="text-input" placeholder="Enter the database name here" type="text" id="db_database" name="db_database" value="{{old('db_database')}}">
    </div>

    <div class="form-group mt-xl">
        <label for="db_username">Username</label>
        <span class="error-message"></span>
        <input class="text-input" placeholder="Enter the username here" type="text" id="db_username" name="db_username" value="{{old('db_username')}}">
    </div>

    <div class="form-group mt-xl">
        <label for="db_password">Password</label>
        <span class="error-message"></span>
        <input class="text-input" placeholder="Enter the password here" type="password" id="db_password" name="db_password">
    </div>

    <div class="form-group mt-xl">
        <label for="db_prefix">Prefix</label>
        <span class="error-message"></span>
        <input class="text-input" placeholder="Enter the prefix here" type="text" id="db_prefix" name="db_prefix" value="{{'kora_'}}">
    </div>

    <div class="form-group mt-xxl">
        <button id="install_submit" type="button" class="btn btn-primary validate-install-js">Complete kora Initialization</button>
    </div>
</section>