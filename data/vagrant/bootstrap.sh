#!/usr/bin/env bash

export ROOTDIR="/vagrant"
export DEBIAN_FRONTEND=noninteractive
export COMPOSER_ALLOW_SUPERUSER=1

MYSQL_PASSWORD=default

##########################
# Install dependencies
##########################

curl -sL https://deb.nodesource.com/setup_6.x | bash -

debconf-set-selections <<< 'mysql-server mysql-server/root_password password default'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password default'

apt-get install -y \
    apache2 gettext perl libxml2 \
    php php-intl php-gettext php-zip php-curl php-xml php-mysql php-sqlite3 php-mbstring php-xdebug libapache2-mod-php \
    mysql-server \
    nodejs


##########################
# Enable PHP Debugger
##########################
echo "xdebug.remote_enable = On" >> /etc/php/7.0/mods-available/xdebug.ini
echo "xdebug.remote_connect_back = On" >> /etc/php/7.0/mods-available/xdebug.ini

##########################
# Bootstrap WebServer part
##########################

cp ${ROOTDIR}/data/vagrant/runalyze.conf /etc/apache2/sites-available/

a2enmod rewrite
a2dissite 000-default
a2ensite runalyze

service apache2 restart


##########################
# Bootstrap DB-Server part
##########################

cp ${ROOTDIR}/data/vagrant/mysql-runalyze.cnf /etc/mysql/mysql.conf.d/
sed -i "s/^bind-address/#bind-address/" /etc/mysql/mysql.conf.d/mysqld.cnf
service mysql restart


##########################
# Bootstrap misc
##########################

locale-gen de_DE.UTF-8


##########################
# Bootstrap Runalyze
##########################

if [ ! -f ${ROOTDIR}/data/config.yml ]; then
    echo "copying default config file to data folder"
    cp ${ROOTDIR}/app/config/default_config.yml ${ROOTDIR}/data/config.yml
    sed -i "s/database_password:$/database_password: default/" ${ROOTDIR}/data/config.yml
fi

# check if we can access the database
mysql -uroot -p${MYSQL_PASSWORD} -e "SELECT @@VERSION;" > /dev/null 2>&1
if [ $? -eq 0 ]; then
    # we have access, so we can check if we need to create a database
    mysql -uroot -p${MYSQL_PASSWORD} -e "USE runalyze;" > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        mysql -uroot -p${MYSQL_PASSWORD} -e "CREATE DATABASE runalyze;" > /dev/null 2>&1
        if [ $? -eq 0 ]; then
            echo "Runalyze database created successfully"
        else
            echo "Error Creating the Database"
            exit 1
        fi
    else
        echo "Runalyze database already exists."
    fi
else
    echo "Could not access the database server, no database will be created"
    exit 1
fi

# perform composer install
# download latest composer.phar if necessary
if [ ! -f ${ROOTDIR}/composer.phar ]; then
    wget -q -O ${ROOTDIR}/composer.phar https://getcomposer.org/composer.phar
else
    php ${ROOTDIR}/composer.phar self-update
fi

php ${ROOTDIR}/composer.phar --no-progress --no-interaction -o -d=${ROOTDIR} install

#install nodejs and dependencies
npm install -g bower
npm install -g gulp-cli
usermod -a -G www-data ubuntu
sudo -H -u ubuntu npm --prefix=${ROOTDIR} --no-bin-links install
sudo -H -u ubuntu gulp --cwd=${ROOTDIR}

# install Runalyze, clear caches, just to be sure
rm -rf \
    ${ROOTDIR}/var/cache/* \
    ${ROOTDIR}/data/cache/*

php ${ROOTDIR}/bin/console runalyze:install
