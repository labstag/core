FROM koromerzhin/php-apache:8.5.12-symfony

WORKDIR /var/www

# Copy only the runtime-relevant parts of apps/
COPY --chown=www-data:www-data apps/src/ /var/www/src/
COPY --chown=www-data:www-data apps/config/ /var/www/config/
COPY --chown=www-data:www-data apps/templates/ /var/www/templates/
COPY --chown=www-data:www-data apps/translations/ /var/www/translations/
COPY --chown=www-data:www-data apps/public/ /var/www/public/
COPY --chown=www-data:www-data apps/vendor/ /var/www/vendor/
COPY --chown=www-data:www-data apps/bin/ /var/www/bin/
COPY --chown=www-data:www-data apps/migrations/ /var/www/migrations/
COPY --chown=www-data:www-data apps/composer.json apps/composer.lock apps/symfony.lock /var/www/
COPY --chown=www-data:www-data apps/private/ /var/www/private/

USER root
RUN mkdir -p /var/www/.config /var/www/var/cache /var/www/var/log /var/www/public/uploads && \
    chmod -R 777 /var/www/.config /var/www/var /var/www/public/uploads 2>/dev/null || true

USER www-data

EXPOSE 80