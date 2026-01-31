FROM php:8.2-apache

# อัปเดตแพ็กเกจและติดตั้งใบรับรองความปลอดภัย
RUN apt-get update && apt-get install -y \
    ca-certificates \
    && update-ca-certificates

# ติดตั้ง PHP Extension สำหรับ MySQL
RUN docker-php-ext-install pdo pdo_mysql

# เปิดการใช้งาน Mod Rewrite สำหรับไฟล์ .htaccess
RUN a2enmod rewrite

# --- [ส่วนที่เพิ่ม] แก้ไข Apache Config ให้ยอมรับ .htaccess ---
# คำสั่งนี้จะไปแก้คำว่า AllowOverride None เป็น AllowOverride All ในไฟล์ตั้งค่าหลัก
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# คัดลอกโค้ดไปยังโฟลเดอร์ทำงาน
COPY . /var/www/html/

# ปรับปรุงสิทธิ์การเข้าถึงไฟล์
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80