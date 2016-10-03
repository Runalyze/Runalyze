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
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS runalyze;"
cp /vagrant/data/vagrant/mysql-runalyze.cnf /etc/mysql/mysql.conf.d/
service mysql restart

echo "======================================================================="
echo "======================================================================="
echo "= now go to http://localhost:8080/install and install Runalyze"
