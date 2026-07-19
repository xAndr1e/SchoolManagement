FROM php:8.2-apache

# Enable required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite (harmless even though we don't use rewrites,
# some libraries/assets may expect it)
RUN a2enmod rewrite

# mod_php requires the prefork MPM. This base image ships with more
# than one MPM module symlinked into mods-enabled, which causes Apache
# to refuse to start with "More than one MPM loaded". Remove the
# conflicting MPM config/load files directly rather than relying on
# a2dismod (which can silently no-op), then enable only prefork.
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load \
          /etc/apache2/mods-enabled/mpm_event.conf \
          /etc/apache2/mods-enabled/mpm_worker.load \
          /etc/apache2/mods-enabled/mpm_worker.conf \
    && a2enmod mpm_prefork \
    && apache2ctl -M

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

# Startup script re-applies the MPM fix at container boot time (not
# just at build time), since the conflict was observed reappearing
# specifically at runtime on this platform.
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]