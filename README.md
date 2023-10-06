# php-passwordmanager
 A simple (and probably insecure) password safe

# Prerequisites
- [ ] Apache/Nginx webserver running PHP > 7, MySQL

# Installation

## Stable (coming)
Download the latest release from this repo and put the files on your webserver.

* Clone this repo into your webserver
````bash
cd /var/www/html
git clone https://github.com/Darknetzz/php-passwordmanager.git
````


# Configuration
* Choose a secure master password (with an optional appended salt), and hash it with SHA512. Protip: [](https://roste.org/rand/#hash)

It is very important you remember your master password.
The passwords you create will be encrypted with this, and is essentially unrecoverable if the master password is not known. 

## Automatic configuration (recommended)
* Open `https://<YOUR_SERVER>/php-passwordmanager` in your browser and configure your instance.
* Sign in with your master password.

## Manual configuration
* Paste the hashed password, and configure your database connection settings in `config_example.php` and rename the file to `config.php`
* Open `https://<YOUR_SERVER>/php-passwordmanager` in your browser and sign in with your master password.


# Troubleshooting
If you are unable to sign in to your instance, try deleting the `config.php` file inside the `includes` folder.
You will then be prompted to reconfigure your instance.