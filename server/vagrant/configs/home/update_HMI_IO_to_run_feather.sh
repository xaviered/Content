#!/usr/bin/env bash
# download phpbrew
curl -L -O https://github.com/phpbrew/phpbrew/raw/master/phpbrew
chmod +x phpbrew
sudo mv phpbrew /usr/local/bin/phpbrew
# Be sure to have /usr/local/bin in your $PATH environment variable.

# install brew
phpbrew init
echo "[[ -e ~/.phpbrew/bashrc ]] && source ~/.phpbrew/bashrc" >> ~/.bashrc

# install dependencies for php
sudo yum install libxslt-devel
sudo yum install libxml2-devel
sudo yum install gmp-devel
sudo yum install curl-devel
sudo yum install enchant-devel

# install php 5.6
phpbrew install 5.6 +mysql +filter +dom +bcmath +ctype +mhash +fileinfo +pdo +posix +ipc +pcntl +bz2 +zip +cli +json +mbstring +mbregex +calendar +sockets +readline +openssl=/usr -- --with-libdir=lib64

# use php 5.6
phpbrew switch 5.6
