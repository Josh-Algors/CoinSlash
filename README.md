Steps in deploying application

-   clone the repository
-   cd to the application folder root directory
-   run composer command to install all dependencies - composer install
-   create DB with a name of your choice
-   make a copy of .env.example and rename it to .env, open and config accordingly DB, SMTP etc
-   generate application key - php artisan key:generate
-   run artisan command to create table in DB - php artisan migrate
-   run artisan command to run seeds on DB - php artisan db:seed
-   run artisan command to generate client secret and id - php artisan passport:install
-
