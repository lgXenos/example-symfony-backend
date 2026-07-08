FROM php:8.3-fpm

# Установка системных утилит, мониторинга и отладки
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    mc \
    htop \
    net-tools \
    iputils-ping \
    curl \
    wget \
    procps \
    && docker-php-ext-install zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --- НАЧАЛО БЛОКА ВЫРАВНИВАНИЯ UID ---
ARG USER_ID=1000
ARG GROUP_ID=1000

RUN if [ ${USER_ID} -ne 33 ]; then \
    apt-get update && apt-get install -y passwd && \
    usermod -u ${USER_ID} www-data && \
    groupmod -g ${GROUP_ID} www-data; \
fi
# --- КОНЕЦ БЛОКА ВЫРАВНИВАНИЯ UID ---\

# Права пользователю www-data, для работы mc
RUN chown -R www-data:www-data /var/www

WORKDIR /var/www/microservice

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
