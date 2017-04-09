#!/usr/bin/env bash

export ROOTDIR="/vagrant"
export DEBIAN_FRONTEND=noninteractive
export COMPOSER_ALLOW_SUPERUSER=1

##########################
# Install dependencies
##########################

curl -sL https://deb.nodesource.com/setup_6.x | bash -

debconf-set-selections <<< 'mysql-server mysql-server/root_password password default'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password default'

apt-get install -y \
    apache2 gettext perl libxml2 \
    php php-intl php-gettext php-zip php-curl php-xml php-mysql php-sqlite3 php-mbstring libapache2-mod-php \
    mysql-server \
    nodejs


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

mysql -uroot -pdefault -e "SET PASSWORD FOR root@localhost=PASSWORD('');"
cp ${ROOTDIR}/data/vagrant/mysql-runalyze.cnf /etc/mysql/mysql.conf.d/
service mysql restart


##########################
# Bootstrap Runalyze
##########################

if [ ! -f ${ROOTDIR}/data/config.yml ]; then
    echo "copying default config file to data folder"
    cp ${ROOTDIR}/app/config/default_config.yml ${ROOTDIR}/data/config.yml
fi

# check if we can access the database
mysql -uroot -e "SELECT @@VERSION;" > /dev/null 2>&1
if [ $? -eq 0 ]; then
    # we have access, so we can check if we need to create a database
    mysql -uroot -e "USE runalyze;" > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        mysql -uroot -e "CREATE DATABASE runalyze;" > /dev/null 2>&1
        if [ $? -eq 0 ]; then
            echo "Runalyze database created successfully"
        else
            echo "Error Creating the Database"
        fi
    else
        echo "Runalyze database already exists."
    fi
else
    echo "Could not access the database server, no database will be created"
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

# install Runalyze, nothing should happen, if already installed
php ${ROOTDIR}/bin/console runalyze:install
