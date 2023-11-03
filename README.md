# php-passwordmanager
 A simple (and probably insecure) password safe

# Prerequisites
- [x] Apache/Nginx webserver
- [x] PHP > 7
- [x] MySQL

# Installation

## Stable
Download the [latest release](https://github.com/Darknetzz/php_passwordmanager/releases/latest) from this repo and put the files on your webserver.

Alternatively:

* Clone this repo into your webserver
````bash
cd /var/www/html
git clone https://github.com/Darknetzz/php-passwordmanager.git php-passwordmanager
chown -R www-data php-passwordmanager
````
# Current (unstable)
* Clone the dev branch to your webserver
````bash
cd /var/www/html
git clone --branch dev https://github.com/Darknetzz/php-passwordmanager.git php-passwordmanager
chown -R www-data php-passwordmanager
````

> [!IMPORTANT]  
> It is crucial that you remember your master password.
> The passwords you create will be encrypted with this, and is essentially unrecoverable if the master password is not known. 

## Automatic configuration (recommended)
* Open `https://<YOUR_SERVER>/php-passwordmanager` in your browser and configure your instance.
* Sign in with your master password.

## Manual configuration
* Choose a secure master password (with an optional appended salt), and hash it with SHA512. Protip: [](https://roste.org/rand/#hash)
* Paste the hashed password, and configure your database connection settings in `config_example.php` and rename the file to `config.php`
* Open `https://<YOUR_SERVER>/php-passwordmanager` in your browser and sign in with your master password.


# Troubleshooting
If you are unable to sign in to your instance, try deleting the `config.php` file inside the `includes` folder.
You will then be prompted to reconfigure your instance.
