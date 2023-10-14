# Web-atrio technical test - Web application by Julien MARFELLA (@TheJulienM)

### How to install this project :

1. Clone the project :
    - `git clone git@github.com:TheJulienM/webatrio-test-technique.git`
2. Create the files needed by Symfony and install the dependencies needed for this project  :
    - `composer install`
3. Create the .env.local file from the .env file
    - On Windows : `copy .env .env.local`
    - On Linux / macOS : `cp .env .env.local`
4. Modify the .env.local file with the configuration you need. For the database, in particular by specifying the database ID and name.
5. Create the database
    - `php bin/console d:d:c`
6. Update the database schema :
    - `php bin/console doctrine:schema:update --complete`
7. Start the Symfony application :
    - `symfony server:start`
8. Now, the Symfony web server should be ready. Go to http://localhost:8000/ (the port may be different)

*Requirements : Symfony 5.4.x, Composer 2.x, PHP 8.x and a MySQL database*