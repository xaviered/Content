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
sudo mkdir /etc/nginx/backup && cd /etc/nginx
sudo mv -f nginx.conf backup/ && sudo mv -f sites-enabled backup/
sudo ln -s /home/local/beholder/server/vagrant/configs/nginx beholder
sudo ln -s beholder/nginx.conf nginx.conf
sudo ln -s beholder/sites-enabled sites-enabled

# change php configs
sudo mkdir -p /etc/php/7.0/fpm/backup && cd /etc/php/7.0/fpm
sudo mv -f php.ini backup/ && sudo mv -f pool.d backup/
sudo ln -s /home/local/beholder/server/vagrant/configs/php beholder
sudo ln -s beholder/php.ini php.ini
sudo ln -s beholder/pool.d pool.d
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
