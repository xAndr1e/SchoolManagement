FROM php:8.2-apache

# Enable required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite (harmless even though we don't use rewrites,
# some libraries/assets may expect it)
RUN a2enmod rewrite

# mod_php requires the prefork MPM. Some base image updates enable
# event/worker MPM alongside it, which causes Apache to refuse to start
# with "More than one MPM loaded". Force prefork only.
RUN a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork

# Copy application code into Apache's web root
COPY . /var/www/html/

# Make sure Apache serves files directly rather than falling back
# to a single entry point. This disables any default catch-all
# rewriting behavior and lets every .php file execute as itself.
RUN { \
    echo '<Directory /var/www/html>'; \
    echo '    Options -Indexes +FollowSymLinks'; \
    echo '    AllowOverride None'; \
    echo '    Require all granted'; \
    echo '</Directory>'; \
    } > /etc/apache2/conf-available/app.conf \
    && a2enconf app

# Railway provides the PORT env var; make Apache listen on it
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf
ENV PORT=8080
EXPOSE 8080

CMD ["apache2-foreground"]