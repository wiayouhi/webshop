# ใช้ PHP 8.2 พร้อม Apache
FROM php:8.2-apache

# ติดตั้ง Library ที่จำเป็นสำหรับการเชื่อมต่อ MySQL
RUN docker-php-ext-install pdo pdo_mysql

# เปิดโหมด Rewrite (เผื่อมีการใช้ .htaccess)
RUN a2enmod rewrite

# Copy ไฟล์ทั้งหมดเข้าไปใน Container
COPY . /var/www/html/

# ตั้งค่า Permission ให้ Apache อ่านไฟล์ได้
RUN chown -R www-data:www-data /var/www/html

# Render จะใช้ Port 80 เป็นมาตรฐาน
EXPOSE 80
