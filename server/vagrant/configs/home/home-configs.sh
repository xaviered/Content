#!/usr/bin/env bash
#
# setup dev configs, codebase
#
# add link to projects
ln -s /home/local/beholder /home/vagrant/beholder
chown vagrant:vagrant /home/vagrant/beholder

# add beholder's parent dir as "projects"
ln -s /home/local/user /home/vagrant/user
chown vagrant:vagrant /home/vagrant/user

# add home dir stuff
cp /home/local/beholder/server/vagrant/configs/home/.gitconfig /home/vagrant
cp /home/local/beholder/server/vagrant/configs/home/.bash_profile /home/vagrant

# include composer bin in global path
echo "PATH=/home/vagrant/.composer/vendor/bin:$PATH" >> /home/vagrant/.bash_profile

# add HTTP server configs/vms
sudo mv -f /etc/nginx/nginx.conf /etc/nginx/nginx.conf-backup
sudo ln -s /home/local/beholder/server/vagrant/configs/nginx/nginx.conf /etc/nginx/nginx.conf
sudo mv -f /etc/nginx/sites-enabled /etc/nginx/sites-enabled-backup
sudo ln -s /home/local/beholder/server/vagrant/configs/nginx/sites-enabled /etc/nginx/sites-enabled
sudo mv -f /etc/php/7.0/fpm/php.ini /etc/php/7.0/fpm/php.ini-backup
sudo ln -s /home/local/beholder/server/vagrant/configs/php/php.ini /etc/php/7.0/fpm/php.ini
sudo mv -f /etc/php/7.0/fpm/php-fpm.conf /etc/php/7.0/fpm/php-fpm.conf-backup
sudo ln -s /home/local/beholder/server/vagrant/configs/php/php-fpm.conf /etc/php/7.0/fpm/php-fpm.conf
# @todo: do the same for /etc/php/7.0/fpm/pool.d/www.conf file

# add host-names
echo "" >> /etc/hosts
echo "127.0.0.1     cms.beholder.ixavier.local" >> /etc/hosts
echo "127.0.0.1     api.beholder.ixavier.local" >> /etc/hosts

# prepare public folders
sudo chown -R www-data:www-data /home/vagrant/beholder/api/public
sudo chmod 755 /home/vagrant/beholder/api/public
sudo chown -R www-data:www-data /home/vagrant/beholder/cms/public
sudo chmod 755 /home/vagrant/beholder/cms/public
