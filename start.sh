#!/bin/bash
set -e

# Force-remove any conflicting MPM modules right before every boot.
# We saw this become necessary at *runtime* even though the image
# builds clean, so this can't be trusted to a build-time step alone.
rm -f /etc/apache2/mods-enabled/mpm_event.load \
      /etc/apache2/mods-enabled/mpm_event.conf \
      /etc/apache2/mods-enabled/mpm_worker.load \
      /etc/apache2/mods-enabled/mpm_worker.conf

a2enmod mpm_prefork >/dev/null 2>&1 || true

echo "=== Loaded Apache modules at container start ==="
apache2ctl -M

exec apache2-foreground