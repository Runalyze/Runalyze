#!/usr/bin/env bash


apt-get update

##########################
# Bootstrap WebServer part
##########################

apt-get install -y apache2
apt-get install -y gettext perl libxml2

# PHP7
apt-get install -y php php-intl php-gettext php-zip php-curl php-xml php-mysql
apt-get install -y libapache2-mod-php

cp /vagrant/data/vagrant/runalyze.conf /etc/apache2/sites-available/

a2enmod rewrite
a2dissite 000-default
a2ensite runalyze

service apache2 restart


##########################
# Bootstrap DB-Server part
##########################
debconf-set-selections <<< 'mysql-server mysql-server/root_password password default'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password default'
apt-get install -y mysql-server
mysql -uroot -pdefault -e "SET PASSWORD FOR root@localhost=PASSWORD('');"
cp /vagrant/data/vagrant/mysql-runalyze.cnf /etc/mysql/mysql.conf.d/
service mysql restart


##########################
# Bootstrap Runalyze
##########################

if [ ! -f /vagrant/data/config.yml ]; then
    echo "copying default config file to data folder"
    cp /vagrant/app/config/default_config.yml /vagrant/data/config.yml
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
