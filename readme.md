# ![kora Logo](https://matrix-msu.github.io/kora/images/logo_green_text_dark.svg) 

v3.0.0

#### The easiest way to manage and publish your data.

Open-source, database-driven, online digital repository application for complex multimedia objects (text, images, audio, 
video).

kora stores, manages, and delivers digital objects with corresponding metadata that enhances the research and 
educational value of the objects. 

***

### Software Requirements
1) Linux Operating System
2) Apache >= 2.0
3) MySQL >= 5.7.20
4) PHP >= 8.1

### Installation Summary

0) Install a LAMP stack, according to [these configuration instructions](https://chi-initiative.github.io/kora-documentation/getting-started/system_requirements/)

1) Clone the repository into your home or data directories -- **DO NOT** install it into your Document Root.

2) Installer composer dependencies

       composer install

3) Create `.htaccess` from the example in `kora/public`:

       cp kora/public/.htaccess.example kora/public/.htaccess
       
    a) Configure the `RewriteBase` rule if the installation is **NOT** located at the root of your url.
    
    i.e if url is http://www.example.com/digitalRepo/kora, then the rule is:
       
       RewriteBase /digitalRepo/kora
       
    b) Configure the `php_value` rules in the newly created `.htaccess` if the installation supports variable 
       overwriting in htaccess (i.e. if you plan on uploading larger files).

4) Create `.env` from the example in `kora`:

       cp kora/.env.example kora/.env
       
    a) If you wish to use the defaults, please configure your database with the expected defaults. 
    
    i.e. in mysql:
       
       GRANT ALL PRIVILEGES ON *.* TO 'kora'@'localhost' IDENTIFIED BY 'kora';
       CREATE DATABASE kora;

   b) Otherwise, modify the ENV file to connect an existing empty database

5) Run the following command in the kora root directory to complete the installation:

       php artisan kora:install

6) After installation is complete:

    a) Give **READ** access to the web user for kora and **ALL** sub-folders.
    
    b) Give **WRITE** access to the web user for the following directories and **ALL** their sub-folders:
       
       kora/bootstrap/cache/
       kora/storage/
       kora/public/assets/javascripts/production/
       
    c) **COPY THE ADMIN USER PASSWORD YOU ARE GIVEN!!!**

7) Add the `public` directory as a [subdomain or subdirectory](https://chi-initiative.github.io/kora-documentation/getting-started/installing_kora_domains/#create-kora-installation-urls). It is very important that only this directory is served by your webserver.

## Contributing

Thank you for considering contributing to kora! The contribution guide can be found 
[here](https://github.com/matrix-msu/kora/blob/master/CONTRIBUTING.md)

### License

kora is an open-sourced software licensed under the [GPU GPL-3.0 license](https://opensource.org/licenses/GPL-3.0)
