# Use Node image to install dependencies.
FROM node:20 AS node

# Set working directory
WORKDIR /app

# Copy files to the container.
COPY . .

# Install npm dependencies.
RUN npm install

# Build base Drupal image.
FROM drupal:10-php8.2-apache-bookworm as base

# Set development ENV variables.
ENV PHP_XDEBUG=${PHP_XDEBUG}
ENV PHP_XDEBUG_CLIENT_HOST=${PHP_XDEBUG_CLIENT_HOST}
ENV PHP_IDE_CONFIG=${PHP_IDE_CONFIG}
ENV PHP_XDEBUG_IDEKEY=${PHP_XDEBUG_IDEKEY}
ENV DRUSH_OPTIONS_URI=${DRUSH_OPTIONS_URI}

# Install extra packages.
RUN	apt update; \
	apt install -y \
    zip \
    sqlite3 \
    git

# Remove stock Drupal codebase.
RUN rm -rf /opt/drupal && \
    mkdir -p /opt/drupal

RUN git config --global --add safe.directory /opt/drupal

FROM base as dev

RUN pecl install xdebug
RUN echo 'zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20220829/xdebug.so' | tee /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=\${PHP_XDEBUG}" | tee -a /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_handler=dbgp" | tee -a /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_port=9000" | tee -a /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.start_with_request=yes" | tee -a /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.idekey=\${PHP_XDEBUG_IDEKEY}" | tee -a /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_host=\${PHP_XDEBUG_CLIENT_HOST}" | tee -a /usr/local/etc/php/conf.d/xdebug.ini

# @todo Removing opcache shouldn't be necessary, but the current base image seems to ignore code changes. Fix this.
RUN rm /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini; \
    rm /usr/local/etc/php/conf.d/opcache-recommended.ini

EXPOSE 9000

VOLUME /opt/drupal

# Build dist image.
FROM base as dist

# Copy code needed for build in image.
COPY ./css ./css
COPY ./modules ./modules
COPY ./templates ./templates
COPY ./tests ./tests
COPY ./patches ./patches
COPY ./config ./config
COPY .env.dist .
COPY composer.json .
COPY ilo_base_theme.info.yml .
COPY ilo_base_theme.libraries.yml .
COPY ilo_base_theme.theme .
COPY logo.png .
COPY Makefile .
COPY runner.yml.dist .
COPY screenshot.png .
COPY phpunit.xml.dist .
COPY phpcs.xml.dist .

# Copy design system assets to the working directory.
COPY --from=node /app/modules/ilo_base_theme_companion/dist ./modules/ilo_base_theme_companion/dist
COPY --from=node /app/dist ./dist

RUN composer install
RUN ./vendor/bin/run drupal:site-install

# This will allow PHPUnit tests to create the web/sites/simpletest directory.
RUN chown www-data:www-data web/sites
