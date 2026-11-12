FROM koromerzhin/php:8.4.27-apache-symfony

WORKDIR /var/www

COPY apps/ /var/www/

USER root
RUN rm -f /var/www/.env /var/www/.env.* \
 && find /var/www/private -type f -name '*.xml' -delete 2>/dev/null || true \
 && rm -rf /var/www/public/uploads/* 2>/dev/null || true \
 && chown -R www-data:www-data /var/www \
 && chmod -R 755 /var/www \
 && chmod -R 777 /var/www/var /var/www/public/uploads 2>/dev/null || true

USER www-data

EXPOSE 80