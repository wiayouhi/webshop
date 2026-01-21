# ใช้ PHP เวอร์ชั่น 8.2 พร้อม Apache
FROM php:8.2-apache

# ติดตั้ง Extension ที่จำเป็น (ถ้ามี)
# RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy ไฟล์ทั้งหมดในโปรเจกต์ไปที่โฟลเดอร์ของ Server
COPY . /var/www/html/

# ปรับให้ Apache รันที่ Port 80 (Render จะจัดการ Route ให้เอง)
EXPOSE 80
