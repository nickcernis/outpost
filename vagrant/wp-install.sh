#!/bin/sh

# Link the wp-content folder to the synced vagrant folder
ln -s /vagrant/wp/wp-content/ /var/www/html/wp-content

# Update the WP CLI WordPress command line tools
echo
echo '~~~ Installing WP CLI ~~~'
sudo curl --silent -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp

# Download WordPress
echo
echo '~~~ Installing WordPress ~~~'
cd /var/www/html
wp core download --allow-root

# Set up wp-config.php
wp core config --dbname=wordpress --dbuser=outpost --dbpass=outpost --allow-root --extra-php <<PHP
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false ); // just log silently to /wp-content/debug.log
define( 'SAVEQUERIES', true );
define( 'WP_HOME', 'http://my.outpost.rocks' );
define( 'WP_SITEURL', 'http://my.outpost.rocks' );
xdebug_disable(); // disable visual stack traces; use xdebug for interactive debugging sessions only
PHP

# Import existing tables or create a fresh installation
if [ -f /vagrant/wp/wp-data/outpost.sql ]
then
  echo
  echo '~~~ Importing existing database ~~~'
  mysql -u outpost -poutpost wordpress < /vagrant/wp/wp-data/outpost.sql
else
  echo
  echo '~~~ Installing fresh database ~~~'
  wp core install --url=http://my.outpost.rocks --title=Outpost --admin_name=outpost --admin_password=outpost --admin_email=null@example.com --allow-root
  wp rewrite structure "/%postname%/" --allow-root
fi


# Plugins
# Separate install and activate commands so that plugins activate if already installed.
echo
echo '~~~ Installing plugins ~~~'
wp plugin install debug-bar --allow-root
wp plugin activate debug-bar --allow-root

# Remove Apache's index.html
rm index.html
#sudo chown -R www-data .
#sudo chmod -R g+w .

echo
echo '~~~ Setting permissions ~~~'
sudo chown -R vagrant:www-data .
sudo chmod -R g+w .

# Set permissions to allow plugin installation and deletion
#sudo chown -R vagrant:www-data .

echo
echo '~~~ Starting services ~~~'
#Start Apache
sudo service apache2 start

# Start cron
sudo mv /etc/init/cron.conf.disabled /etc/init/cron.conf
sudo service cron start



echo
echo '~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~'
echo 'Done! Visit http://my.outpost.rocks'
echo '~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~'