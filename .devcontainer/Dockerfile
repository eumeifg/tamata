#--- original ../Dockerfile contents START




# this file will create the "fpm" image to serve the php files 
# it will use multi-stage build to create the files in the "builder" container
# and then copy the files to the "fpm" container
#
# Note: I was able to use a single stage build while using docker desktop but that 
# failed on the build pipeline. it showed an error "bin/magento not found" 
# while trying to execute "RUN bin/magento module:enable --all" , propably 
# file permissions issue with php composer. 
FROM magento/magento-cloud-docker-php:7.3-fpm-1.3.0 as builder
WORKDIR /var/www/html
# copy composer files from php composer version 1 official image
COPY --from=composer:1 /usr/bin/composer /usr/bin/composer
# copy php composer related files from the building directory
COPY --chown=www-data composer.json composer.json
COPY --chown=www-data composer.lock composer.lock
COPY --chown=www-data auth.json auth.json
# speedup composer install with prestissimo since we cant use composer 2
RUN composer global require hirak/prestissimo
RUN composer install --no-dev --ansi --ignore-platform-reqs
# copy custom files to code and design directories
COPY app/code/ app/code/
COPY app/design/ app/design/
# set the production mode for magento
ENV MAGE_MODE=production
# update composer autoloade file
RUN composer dump-autoload --optimize --apcu
# copy the build configuration file
COPY app/etc/config.php app/etc/config.php
# execute the required magento commands
RUN bin/magento setup:di:compile
RUN bin/magento setup:static-content:deploy en_US ar_SA -j 12
COPY app/etc/env.php app/etc/env.php



# create the "fpm" image from the "builder" image
FROM magento/magento-cloud-docker-php:7.3-fpm-1.3.0 as fpm
#copy the app files from the builder image
COPY --from=builder /var/www/html /app
#add composer to the fpm image to be able to execute php composer commands 
COPY --from=composer:1 /usr/bin/composer /usr/bin/composer
RUN composer global require hirak/prestissimo
WORKDIR /app
# update composer autoload
RUN composer dump-autoload --optimize --apcu
# install newrelic php agent
RUN wget -r -l1 -nd -A"linux.tar.gz" https://download.newrelic.com/php_agent/release/  && \
    gzip -dc newrelic*.tar.gz | tar xf -  && \
    cd newrelic-php5-*-linux && \
    export NR_INSTALL_USE_CP_NOT_LN=1 && \
    export NR_INSTALL_SILENT=1 && \
    ./newrelic-install install
ADD start.sh /
#copy the custom php.ini file
COPY zzz-custom-php.ini /usr/local/etc/php/conf.d/zzz-custom-php.ini
RUN chmod +x /start.sh
CMD ["/start.sh"]

#-----------------------------------------------------------------------------------------------------------------------------------
#--- original ../Dockerfile contents END , add any "development only commands" below this line
#-----------------------------------------------------------------------------------------------------------------------------------
# # xdebug is already enabled using the php
# force xdebug to start with every request, even if there is no xdebug extention in chrome, 
# it will try to connect to connect to the client for 200ms and then give up
RUN echo "xdebug.start_with_request=1">> /usr/local/etc/php/conf.d/zz-xdebug-settings.ini