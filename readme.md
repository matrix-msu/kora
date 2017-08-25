# Kora 3 
***

### Installation
Clone the repository
    
    git clone git@github.com:matrix-msu/Kora3.git

Temporarily give write access to the directory:
    
    chmod -R 0775 -R Kora3/

Create `.htaccess` from the example in `Kora3/public`

    cp Kora3/public/.htaccess.example Kora3/public/.htaccess

Configure the `RewriteBase` rule in the newly created `.htaccess` if the installation is **not** located at the root of your url similar to the example below.

    Ex: http://www.example.com/digitalRepo/Kora3/
    RewriteBase /digitalRepo/Kora3/public

Navigate to your Kora3  url and click install

    URL: http://www.example.com/digitalRepo/Kora3/public

Change directory permissions to read access:
    
    chmod -R 0755 -R Kora3/
    
Give write access to the following directories:

    chmod -R 0775 -R Kora3/bootstrap/cache/
    chmod -R 0775 -R Kora3/python/
    chmod -R 0775 -R Kora3/storage/
    chmod -R 0775 -R Kora3/public/assets/javascripts/production/

## Contributing

Thank you for considering contributing to Kora3! The contribution guide can be found in the [Kora3 documentation](http://kora.com).

### License

Kora is open-sourced software licensed under the [GPU GPL-3.0 license](https://opensource.org/licenses/GPL-3.0)
