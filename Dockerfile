# Gunakan image resmi PHP dengan Apache
FROM php:8.2-apache

# Install ekstensi MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy semua file proyek ke dalam container
COPY . /var/www/html/

# Set hak akses yang benar
RUN chown -R www-data:www-data /var/www/html

# Expose port 8080 (Railway membutuhkan ini)
EXPOSE 8080

# Jalankan Apache sebagai proses utama
CMD ["apache2-foreground"]
