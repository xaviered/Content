#!/usr/bin/env bash
# post installation loads

# add info for mongodb
sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 0C49F3730359A14518585931BC711F9BA15703C6
echo "deb [ arch=amd64 ] http://repo.mongodb.org/apt/ubuntu trusty/mongodb-org/3.4 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.4.list
# add info for php7
sudo add-apt-repository -y ppa:ondrej/php
# remove info for php5
sudo add-apt-repository --remove ppa:ondrej/php5-5.6

# update dependencies
sudo apt-get update -y
sudo apt-get upgrade -y

# install essentials
sudo apt-get install -y build-essential nano wget curl git git-core

# install latest node + npm
curl -sL https://deb.nodesource.com/setup_4.x | sudo -E bash -
sudo apt-get install -y nodejs

# install grunt-cli
sudo npm install -g grunt-cli

# install PHP
sudo apt-get install -y php7.0 php7.0-fpm php7.0-xml php7.0-curl php7.0-cgi

# instal debconf-utils tool
sudo apt-get install -y debconf-utils

# install mongodb
sudo apt-get install -y mongodb-org

## install nginx
sudo apt-get install -y nginx
echo "cgi.fix_pathinfo=0" >> /etc/php/7.0/fpm/php.ini

# install composer
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

# install symfony
sudo mkdir -p /usr/local/bin
sudo curl -LsS https://symfony.com/installer -o /usr/local/bin/symfony
sudo chmod a+x /usr/local/bin/symfony

# install aws-cli
sudo apt-get install -y awscli

# setup home env
sh /home/local/beholder/server/vagrant/configs/home/home-configs.sh

# start nginx
sudo service nginx start
# restart fpm
sudo service php7.0-fpm restart
## start mongodb
sudo service mongod start
