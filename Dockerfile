FROM php:8.2-apache

# Copy all files to Apache's web directory
COPY . /var/www/html/

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html/

# Configure Apache to allow .htaccess
RUN echo '<Directory /var/www/html/>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/sites-available/000-default.conf

# Expose port 10000 (Render's default)
EXPOSE 10000

# Override Apache port to use Render's PORT environment variable
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Start Apache in foreground
CMD ["apache2-foreground"]
