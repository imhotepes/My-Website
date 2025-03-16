FROM php:8.2-apache

# Install ekstensi MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Aktifkan modul rewrite di Apache
RUN a2enmod rewrite

# Ubah konfigurasi Apache agar berjalan di port 8080
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf

# Copy semua file proyek ke dalam container
COPY . /var/www/html/

# Set hak akses yang benar
RUN chown -R www-data:www-data /var/www/html

# Expose port 8080
EXPOSE 8080

# Jalankan Apache
CMD ["apache2-foreground"]
