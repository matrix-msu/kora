# Kora 3 
***

### Software Requirements
1) `PHP` >= 7.1.3
2) `MySQL` >= 5.5.43 `recommended`

### Installation
1) Clone the repository:
    
       git clone https://github.com/matrix-msu/Kora3.git

2) Temporarily give **WRITE** access to the web user for Kora3 and **ALL** sub-folders.

3) Create `.htaccess` from the example in `Kora3/public`:

       cp Kora3/public/.htaccess.example Kora3/public/.htaccess

4) Configure the `RewriteBase` rule in the newly created `.htaccess` if the installation is **NOT** located at the root of your url:

       i.e. if the URL is: http://www.example.com/digitalRepo/Kora3/public
       then the .htaccess rule is: RewriteBase /digitalRepo/Kora3/public
       
5) Configure the `php_value` rules in the newly created `.htaccess` if the installation supports variable overwriting in htaccess:

       i.e. if you plan on uploading larger files

6) Navigate to your Kora3 url and click install. **REMEMBER** to return here once you complete the installation.
    
    ***NOTE:*** Alternatively you can run the `php artisan install:finish` command, with the appropriate variables, via CLI if you do not wish to use the Kora3 interface.

7) Give **READ** access to the web user for Kora3 and **ALL** sub-folders.
    
8) Give **WRITE** access to the web user for the following directories and **ALL** their sub-folders:

       Kora3/bootstrap/cache/
       Kora3/storage/
       Kora3/public/assets/javascripts/production/

## Contributing

Thank you for considering contributing to Kora3! The contribution guide can be found in the [Kora3 documentation](http://kora.com).

### License

Kora is open-sourced software licensed under the [GPU GPL-3.0 license](https://opensource.org/licenses/GPL-3.0)
