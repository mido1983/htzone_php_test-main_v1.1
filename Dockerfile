FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# Enable required PHP extensions
RUN docker-php-ext-install pdo_sqlite

# Enable Apache modules
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Create necessary directories with proper permissions
RUN mkdir -p database logs \
    && chown -R www-data:www-data database logs \
    && chmod -R 755 database logs

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Set up cron job for database updates
RUN apt-get update && apt-get install -y cron \
    && echo "0 */6 * * * www-data php /var/www/html/update_database.php >> /var/www/html/logs/cron.log 2>&1" > /etc/cron.d/database-update \
    && chmod 0644 /etc/cron.d/database-update \
    && crontab /etc/cron.d/database-update

# Start cron service in the background and Apache in the foreground
CMD service cron start && apache2-foreground 