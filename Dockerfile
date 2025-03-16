# Menggunakan image PHP dengan Apache
FROM php:8.2-apache

# Copy semua file ke dalam container
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Expose port 8080 (Railway default)
EXPOSE 8080

# Jalankan Apache saat container dimulai
CMD ["apache2-foreground"]
