<!-- Section 1 -->
<section class="database-section">
    <div class="section-title">Database Setup</div>

    <div class="section-desc mt-m">
        Information for your database server
    </div>

    <div class="form-group install-select-container install-select-container-js">
        <div class="install-select-js mt-xl">
            <label for="db_driver">Driver</label>
            <select class="single-select" id="db_driver" name="db_driver">
                <option value="mysql">MySQL</option>
                <option value="pgsql">PostgreSQL</option>
                <option value="sqlite">SQLite</option>
                <option value="sqlsrv">SQL Server</option>
            </select>
        </div>
    </div>

    <div class="form-group mt-xl">
        <label for="db_host">Host</label>
        <input class="text-input" placeholder="Enter the host name here" type="text" id="db_host" name="db_host" value="{{old('db_host')}}">
    </div>

    <div class="form-group mt-xl">
        <label for="db_database">DB Name</label>
        <input class="text-input" placeholder="Enter the database name here" type="text" id="db_database" name="db_database" value="{{old('db_database')}}">
    </div>

    <div class="form-group mt-xl">
        <label for="db_username">Username</label>
        <input class="text-input" placeholder="Enter the username here" type="text" id="db_username" name="db_username" value="{{old('db_username')}}">
    </div>

    <div class="form-group mt-xl">
        <label for="db_password">Password</label>
        <input class="text-input" placeholder="Enter the password here" type="password" id="db_password" name="db_password">
    </div>

    <div class="form-group mt-xl">
        <label for="db_prefix">Prefix</label>
        <input class="text-input" placeholder="Enter the prefix here" type="text" id="db_prefix" name="db_prefix" value="{{'kora3_'}}">
    </div>
</section>

<!-- Section 2 -->
<section class="admin-section hidden">
    <div class="section-title">Admin User Setup</div>

    <div class="section-desc mt-m">
        Information on the main admin user for managing this Kora Installation
    </div>

    <div class="form-group half mt-xl pr-m">
        <label for="user_firstname">First Name</label>
        <input class="text-input" placeholder="Enter admin's first name here" type="text" id="user_firstname" name="user_firstname" value="{{old('user_firstname')}}">
    </div>

    <div class="form-group half mt-xl pl-m">
        <label for="user_lastname">Last Name</label>
        <input class="text-input" placeholder="Enter admin's last name here" type="text" id="user_lastname" name="user_lastname" value="{{old('user_lastname')}}">
    </div>

    <div class="form-group mt-xl ">
        <label for="user_username">Username</label>
        <input class="text-input" placeholder="Enter admin's username here" type="text" id="user_username" name="user_username" value="{{old('user_username')}}">
    </div>

    <div class="form-group mt-xl">
        <label for="user_email">Email</label>
        <input class="text-input" placeholder="Enter admin's email here" type="text" id="user_email" name="user_email" value="{{old('user_email')}}">
    </div>

    <div class="form-group mt-xl">
        <label for="user_password">Password</label>
        <input class="text-input" placeholder="Enter admin's password here" type="password" id="user_password" name="user_password">
    </div>

    <div class="form-group mt-xl">
        <label for="user_confirmpassword">Confirm Password</label>
        <input class="text-input" placeholder="Confirm admin's password here" type="password" id="user_confirmpassword" name="user_confirmpassword">
    </div>

    <div class="form-group mt-xl">
        <label>Profile Image</label>
        <input type="file" accept="image/*" name="user_profile" id="user_profile" class="profile-input" />
        <label for="user_profile" class="profile-label">
            <img src="{{ config('app.url') }}assets/images/blank_profile.jpg" height="80px" width="80px" alt="Profile">
            <p class="filename">Add a photo to help others identify you</p>
            <p class="instruction mb-0">Drag and Drop or Select a Photo here</p>
        </label>
    </div>

    <div class="form-group mt-xl">
        <label for="user_organization">Organization</label>
        <input class="text-input" placeholder="Enter admin's organization here" type="text" id="user_organization" name="user_organization" value="{{old('user_organization')}}">
    </div>

    <div class="form-group install-select-container install-select-container-js">
        <div class="install-select-js mt-xl">
            <label for="user_language">Language</label>
            <select class="single-select" id="user_language" name="user_language">
                @foreach(getLangs()->keys() as $lang)
                    <option value='{{getLangs()->get($lang)[0]}}'>{{getLangs()->get($lang)[1]}} </option>
                @endforeach
            </select>
        </div>
    </div>
</section>

<!-- Section 3 -->
<section class="mail-section hidden">
    <div class="section-title">Mail Server Setup</div>

    <div class="section-desc mt-m">
        Information on the email server that will deliver email notifications from Kora
    </div>

    <div class="form-group mt-xl">
        <label for="mail_host">Host</label>
        <input class="text-input" placeholder="Enter the mail server host here" type="text" id="mail_host" name="mail_host" value="{{old('mail_host')}}">
    </div>

    <div class="form-group mt-xl">
        <label for="mail_from_address">From Address</label>
        <input class="text-input" placeholder="Enter the from address here" type="text" id="mail_from_address" name="mail_from_address" value="{{old('mail_from_address')}}">
    </div>

    <div class="form-group mt-xl">
        <label for="mail_from_name">From Name</label>
        <input class="text-input" placeholder="Enter the from name here" type="text" id="mail_from_name" name="mail_from_name" value="{{old('mail_from_name')}}">
    </div>

    <div class="form-group mt-xl">
        <label for="mail_username">Username</label>
        <input class="text-input" placeholder="Enter the mail server username here" type="text" id="mail_username" name="mail_username" value="{{old('mail_username')}}">
    </div>

    <div class="form-group mt-xl">
        <label for="mail_password">Password</label>
        <input class="text-input" placeholder="Enter the mail server password here" type="password" id="mail_password" name="mail_password">
    </div>
</section>

<!-- Section 4 -->
<section class="recaptcha-section hidden">
    <div class="section-title">Recaptcha Setup</div>

    <div class="section-desc mt-m">
        Information on Recaptcha to be used for account creation filtering
    </div>

    <div class="form-group mt-xl">
        <label for="recaptcha_public_key">Public Key</label>
        <input class="text-input" placeholder="Enter the recaptcha public key here" type="text" id="recaptcha_public_key" name="recaptcha_public_key" value="{{old('recaptcha_public_key')}}">
    </div>

    <div class="form-group mt-xl">
        <label for="recaptcha_private_key">Private Key</label>
        <input class="text-input" placeholder="Enter the recaptcha private key here" type="text" id="recaptcha_private_key" name="recaptcha_private_key" value="{{old('recaptcha_private_key')}}">
    </div>
</section>

<!-- Section 5 -->
<section class="base-section hidden">
    <div class="section-title">Base Setup</div>

    <div class="section-desc mt-m">
        Information on the paths used for accessing system resources. See ENV.example for example URIs
    </div>

    <div class="form-group mt-xl">
        <label for="baseurl_url">URL</label>
        <input class="text-input" placeholder="Enter the base URL here (i.e. https://www.MyKora3.com/)" type="text" id="baseurl_url" name="baseurl_url" value="{{old('baseurl_url')}}">
        <p class="sub-text  mt-xxs">
            URL that points to the installation. Kora 3 requires your URL to point to /{path to project root folder}/public/
        </p>
    </div>

    <div class="form-group mt-xl">
        <label for="basepath">Path</label>
        <input class="text-input" placeholder="Enter the base path here (i.e. /system/path/to/Kora3/)" type="text" id="basepath" name="basepath" value="{{old('basepath')}}">
        <p class="sub-text  mt-xxs">
            Actual system path from / to {ProjectRoot}/
        </p>
    </div>

    <div class="form-group mt-xxl">
        <button id="install_submit" type="button" class="btn btn-primary">Complete Kora Initialization</button>
    </div>
</section>