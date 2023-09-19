# php-passwordmanager
 A simple (and probably insecure) password safe

# Installation

* Clone this repo into your webserver
````bash
cd /var/www/html
git clone https://github.com/Darknetzz/php-passwordmanager.git
````

* Import `php_passwordmanager.sql` into your SQL server.

* Choose a secure master password, and hash it with SHA512. Protip: [](https://roste.org/rand/#hash)

* Paste the hashed password, and configure your database connection settings in `config_example.php` and rename the file to `config.php`

* Open `https://<YOUR_SERVER>/php-passwordmanager` in your browser and sign in with your master password.
