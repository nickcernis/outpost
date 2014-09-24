#!/bin/bash

# Update WP CLI
sudo curl -O --silent https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp

cd /var/www/html

# Import existing tables
if [ -f /var/wp-data/outpost.sql ]
then
    echo
    echo '~~~ Importing existing database ~~~'
    mysql -u outpost -poutpost wordpress < /var/wp-data/outpost.sql
    echo 'Database imported'
#else
#    echo
#    echo '~~~ Installing fresh database ~~~'
#    wp core install --url=http://my.outpost.rocks --title=Outpost --admin_name=outpost --admin_password=outpost --admin_email=test@example.com --allow-root
#    wp rewrite structure "/%postname%/"
fi

# Update WordPress
echo
echo '~~~ Updating WordPress ~~~'
sudo wp core update --allow-root
sudo wp core update-db --allow-root

wp plugin install debug-bar --allow-root
wp plugin activate debug-bar --allow-root

echo
echo '~~~ Starting services ~~~'
# Start Apache
sudo service apache2 start

# Start cron
sudo mv /etc/init/cron.conf.disabled /etc/init/cron.conf
sudo service cron start

echo
echo '~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~'
echo 'Done! Visit http://my.outpost.rocks'
echo '~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~'
