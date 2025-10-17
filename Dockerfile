# Sử dụng image PHP 8.2 FPM làm image cơ sở
FROM php:8.2-fpm
# Cài đặt các extension cần thiết cho Laravel
RUN apt-get update && apt-get install -y \
 git \
 curl \
 libpng-dev \
 libonig-dev \
 libxml2-dev \
 zip \
 unzip \
 && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# Đặt thư mục làm việc
WORKDIR /var/www
# Copy code của project vào container
COPY . .
# Cài đặt các dependencies của project
RUN composer install
# Cấp quyền cho thư mục storage và bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
# Expose port 9000 và chạy php-fpm
EXPOSE 9000
CMD ["php-fpm"]