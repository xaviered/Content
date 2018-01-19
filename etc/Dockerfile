ARG DOCKER_REGISTRY_URL
ARG GITHUB_TOKEN

FROM ${DOCKER_REGISTRY_URL}/ixavier/php-nginx:latest

# get libs
WORKDIR /var/www/ixavier/Services/Libraries
COPY Services/Libraries ./

# get code
WORKDIR /var/www/ixavier/Services/Content
COPY Services/Content ./

# config web app
ADD Services/Content/etc/vhost.conf /etc/nginx/sites-enabled/content.conf

# add certificates
COPY etc/cert/ixavier.com/ /etc/nginx/ssl/ixavier.com/

# install code dependencies
USER root:root
RUN composer install -n --optimize-autoloader --prefer-dist --no-suggest
