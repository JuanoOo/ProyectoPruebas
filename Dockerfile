FROM php:8.4-cli-bookworm

# Install system dependencies
RUN apt-get update && (apt-get install -y libaio1 || apt-get install -y libaio1t64) \
    && (if [ ! -f /usr/lib/x86_64-linux-gnu/libaio.so.1 ] && [ -f /usr/lib/x86_64-linux-gnu/libaio.so.1t64 ]; then ln -s /usr/lib/x86_64-linux-gnu/libaio.so.1t64 /usr/lib/x86_64-linux-gnu/libaio.so.1; fi) \
    && apt-get install -y \
        unzip \
        curl \
        git \
        libzip-dev \
        zip \
    && rm -rf /var/lib/apt/lists/*

# Install Oracle Instant Client
RUN mkdir -p /opt/oracle && cd /opt/oracle \
    && curl -L -o instantclient-basic.zip \
        "https://download.oracle.com/otn_software/linux/instantclient/2112000/instantclient-basic-linux.x64-21.12.0.0.0dbru.zip" \
    && curl -L -o instantclient-sdk.zip \
        "https://download.oracle.com/otn_software/linux/instantclient/2112000/instantclient-sdk-linux.x64-21.12.0.0.0dbru.zip" \
    && unzip instantclient-basic.zip && unzip instantclient-sdk.zip \
    && rm *.zip \
    && echo /opt/oracle/instantclient_21_12 > /etc/ld.so.conf.d/oracle-instantclient.conf \
    && ldconfig

ENV LD_LIBRARY_PATH=/opt/oracle/instantclient_21_12
ENV ORACLE_HOME=/opt/oracle/instantclient_21_12

# Install oci8 PHP extension
RUN echo "instantclient,/opt/oracle/instantclient_21_12" | pecl install oci8 \
    && docker-php-ext-enable oci8

# Install other PHP extensions
RUN docker-php-ext-install zip pdo

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application source
COPY src/ .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs 2>/dev/null || composer install --optimize-autoloader --ignore-platform-reqs

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
