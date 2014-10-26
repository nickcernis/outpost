#!/bin/sh

# Link the wp-content folder to the synced vagrant folder
if [ ! -f /var/www/html/wp-content/.gitkeep ]
then
  ln -s /vagrant/wp/wp-content/ /var/www/html/wp-content
fi


# Update the WP CLI WordPress command line tools
echo
echo '~~~ Installing WP CLI ~~~'
sudo curl --silent -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp

# Download WordPress
if [ ! -f /var/www/html/wp-config-sample.php ]
then
  echo
  echo '~~~ Downloading WordPress ~~~'
  cd /var/www/html
  wp core download --allow-root
fi

if [ ! -f /var/www/html/wp-config.php ]
then
  # Set up wp-config.php
  echo
  echo '~~~ Adding wp.config ~~~'
  cd /var/www/html
  wp core config --dbname=wordpress --dbuser=outpost --dbpass=outpost --allow-root --extra-php <<PHP
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false ); // just log silently to /wp-content/debug.log
define( 'SAVEQUERIES', true );
define( 'WP_HOME', 'http://my.outpost.rocks' );
define( 'WP_SITEURL', 'http://my.outpost.rocks' );
define( 'JETPACK_DEV_DEBUG', true);
xdebug_disable(); // disable visual stack traces; use xdebug for interactive debugging sessions only
PHP
fi

# Import existing tables or create a fresh installation
if [ -f /vagrant/wp/wp-data/outpost.sql ]
then
  echo
  echo '~~~ Importing existing database ~~~'
  mysql -u outpost -poutpost wordpress < /vagrant/wp/wp-data/outpost.sql
  echo 'Done'
else
  echo
  echo '~~~ Installing WordPress ~~~'
  cd /var/www/html
  wp core install --url=http://my.outpost.rocks --title=Outpost --admin_name=outpost --admin_password=outpost --admin_email=null@example.com --allow-root
  wp rewrite structure "/%postname%/" --allow-root
fi


# Plugins
# Separate install and activate commands so that plugins activate if already installed.
echo
echo '~~~ Installing plugins ~~~'
cd /var/www/html
wp plugin install debug-bar --allow-root
wp plugin activate debug-bar --allow-root

# Remove Apache's index.html
if [ -f /var/www/html/index.html ]
then
  rm index.html
fi
#sudo chown -R www-data .
#sudo chmod -R g+w .

echo
echo '~~~ Setting permissions ~~~'
sudo chown -R vagrant:www-data .
sudo chmod -R g+w .

echo
echo '~~~ Starting services ~~~'
#Start Apache
sudo service apache2 start

# Start cron
if [ -f /etc/init/cron.conf.disabled ]
then
  sudo mv /etc/init/cron.conf.disabled /etc/init/cron.conf
fi
sudo service cron start



echo
echo '~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~'
echo 'Done! Visit http://my.outpost.rocks'
echo '~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~'