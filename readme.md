# Kora 3.1

#### The easiest way to manage and publish your data.

Open-source, database-driven, online digital repository application for complex multimedia objects (text, images, audio, 
video).

Kora stores, manages, and delivers digital objects with corresponding metadata that enhances the research and 
educational value of the objects. 

***

### Software Requirements
1) `PHP` >= 7.1.3
2) `MySQL` >= 5.7.20

### Installation

NOTE: If you are using a previous version of Kora 3 (3.0.*), you will not be able to upgrade through Kora. Create a 
fresh Kora 3 install and use the provided database conversion tool [COMING SOON]

1) Clone the repository:
    
       git clone https://github.com/matrix-msu/Kora3.git

2) Create `.htaccess` from the example in `Kora3/public`:

       cp Kora3/public/.htaccess.example Kora3/public/.htaccess

3) Configure the `RewriteBase` rule in the newly created `.htaccess` if the installation is **NOT** located at the root 
of your url:

       i.e. if the URL is: http://www.example.com/digitalRepo/Kora3/public
       then the .htaccess rule is: RewriteBase /digitalRepo/Kora3/public
       
4) Configure the `php_value` rules in the newly created `.htaccess` if the installation supports variable overwriting in 
htaccess:

       i.e. if you plan on uploading larger files

5) Create `.env` from the example in `Kora3`:

       cp Kora3/.env.example Kora3/.env
       
6) Configure any settings in the `.env` file as needed. If you wish to use the defaults, please configure your database 
with the expected defaults.
        
       in mysql:
       GRANT ALL PRIVILEGES ON *.* TO 'kora3'@'localhost' IDENTIFIED BY 'kora3';
       CREATE DATABASE kora3;

7) Run the following command in the Kora 3 root directory to complete the installation:

       php artisan kora3:install

8) Give **READ** access to the web user for Kora3 and **ALL** sub-folders.
    
9) Give **WRITE** access to the web user for the following directories and **ALL** their sub-folders:

       Kora3/bootstrap/cache/
       Kora3/storage/
       Kora3/public/assets/javascripts/production/

## Contributing

Thank you for considering contributing to Kora3! The contribution guide can be found in the 
[Coming soon...]()

### License

Kora is an open-sourced software licensed under the [GPU GPL-3.0 license](https://opensource.org/licenses/GPL-3.0)
