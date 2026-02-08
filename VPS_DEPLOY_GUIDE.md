# 🚀 VPS Deploy Qilish - To'liq Yo'riqnoma

## 📋 VPS Server Talablari

- **OS:** Ubuntu 20.04 / 22.04 LTS
- **PHP:** 7.4+ (yaxshisi 8.0+)
- **Python:** 3.8+
- **Node.js:** 16+
- **ffmpeg:** Eng yangi versiya
- **Nginx/Apache:** Web server

---

## 1️⃣ VPS ga SSH orqali ulanish

```bash
ssh root@95.111.250.26
# yoki
ssh yourusername@95.111.250.26
```

---

## 2️⃣ Sistema Yangilanishlar

```bash
# Sistema paketlarini yangilash
sudo apt update && sudo apt upgrade -y

# Kerakli paketlarni o'rnatish
sudo apt install -y software-properties-common curl wget git
```

---

## 3️⃣ PHP va Kengaytmalar O'rnatish

```bash
# PHP va kerakli kengaytmalar
sudo apt install -y php php-cli php-fpm php-mysql php-curl php-json php-mbstring php-xml

# PHP versiyasini tekshirish
php --version

# PHP konfiguratsiyasini sozlash
sudo nano /etc/php/8.1/fpm/php.ini
# Quyidagilarni o'zgartiring:
# max_execution_time = 600
# max_input_time = 600
# memory_limit = 512M
# upload_max_filesize = 100M
# post_max_size = 100M

# PHP-FPM ni qayta ishga tushirish
sudo systemctl restart php8.1-fpm
```

---

## 4️⃣ Python va yt-dlp O'rnatish

```bash
# Python va pip
sudo apt install -y python3 python3-pip

# Python versiyasini tekshirish
python3 --version

# yt-dlp o'rnatish (eng yangi versiya)
sudo pip3 install -U yt-dlp

# yt-dlp versiyasini tekshirish
yt-dlp --version
```

---

## 5️⃣ Node.js O'rnatish (JavaScript Runtime)

```bash
# NodeSource repository qo'shish
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -

# Node.js o'rnatish
sudo apt install -y nodejs

# Versiyani tekshirish
node --version
npm --version
```

---

## 6️⃣ ffmpeg O'rnatish (Video Processing)

```bash
# ffmpeg o'rnatish
sudo apt install -y ffmpeg

# Versiyani tekshirish
ffmpeg -version
```

---

## 7️⃣ Loyiha Deploy Qilish

### Git orqali (TAVSIYA ETILADI):

```bash
# Web directory ga kirish
cd /var/www/html

# Loyihani clone qilish
sudo git clone https://github.com/webdeveloper94/ytdownloader.git

# Ruxsatlarni sozlash
sudo chown -R www-data:www-data /var/www/html/ytdownloader
sudo chmod -R 755 /var/www/html/ytdownloader

# Uploads papkasiga write ruxsati
sudo chmod -R 777 /var/www/html/ytdownloader/uploads
```

### Yoki FTP/SFTP orqali:

```bash
# FileZilla yoki WinSCP dan foydalaning
# Fayllarni /var/www/html/ytdownloader ga yuklang
```

---

## 8️⃣ Database Sozlash

```bash
# MySQL o'rnatish
sudo apt install -y mysql-server

# MySQL xavfsizligini sozlash
sudo mysql_secure_installation

# MySQL ga kirish
sudo mysql -u root -p

# Database yaratish
CREATE DATABASE ytdownloader CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Foydalanuvchi yaratish
CREATE USER 'ytuser'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON ytdownloader.* TO 'ytuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# SQL faylni import qilish
mysql -u ytuser -p ytdownloader < /var/www/html/ytdownloader/db.sql
```

---

## 9️⃣ config/db.php Sozlash

```bash
sudo nano /var/www/html/ytdownloader/config/db.php
```

O'zgartiring:
```php
<?php
$host = 'localhost';
$db   = 'ytdownloader';
$user = 'ytuser';
$pass = 'STRONG_PASSWORD_HERE';  // Yuqorida yaratgan parol
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
```

---

## 🔟 Production Mode Yoqish

### download.php da:
```bash
sudo nano /var/www/html/ytdownloader/user/download.php
```

O'zgartiring:
```php
$useLocal = false;  // PRODUCTION MODE
```

**37-qatorda** va **219-qatorda** o'zgartiring!

---

## 1️⃣1️⃣ Nginx Konfiguratsiya (agar Nginx ishlatilsa)

```bash
sudo nano /etc/nginx/sites-available/ytdownloader
```

Quyidagini yozing:
```nginx
server {
    listen 80;
    server_name 95.111.250.26;  # yoki yourdomain.com
    root /var/www/html/ytdownloader;
    index index.php index.html;

    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Katta videolar uchun timeout
        fastcgi_read_timeout 600;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Aktivlashtirish:
```bash
sudo ln -s /etc/nginx/sites-available/ytdownloader /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## 1️⃣2️⃣ Test Qilish

### 1. yt-dlp ni test qiling:
```bash
cd /var/www/html/ytdownloader
yt-dlp -J "https://www.youtube.com/watch?v=dQw4w9WgXcQ" --skip-download
```

### 2. PHP API ni test qiling:
```bash
curl "http://95.111.250.26/yt_info.php?url=https://www.youtube.com/watch?v=dQw4w9WgXcQ"
```

### 3. Brauzerda ochish:
```
http://95.111.250.26/
```

---

## ✅ Monitoring va Debugging

### Nginx log:
```bash
sudo tail -f /var/log/nginx/error.log
```

### PHP log:
```bash
sudo tail -f /var/log/php8.1-fpm.log
```

### API debug log:
```bash
tail -f /var/www/html/ytdownloader/api_debug.log
```

---

## 🔒 Xavfsizlik (IMPORTANT!)

### 1. Firewall sozlash:
```bash
sudo ufw allow 22/tcp   # SSH
sudo ufw allow 80/tcp   # HTTP
sudo ufw allow 443/tcp  # HTTPS
sudo ufw enable
```

### 2. SSL sertifikat (Let's Encrypt):
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

### 3. Xavfsiz parollar:
- Database parolini kuchli qiling
- Admin panelga kirish uchun 2FA yoqing

---

## 🚀 Optimizatsiya

### 1. yt-dlp cache:
```bash
mkdir -p /tmp/yt-dlp-cache
chmod 755 /tmp/yt-dlp-cache
```

### 2. PHP OPcache:
```bash
sudo apt install -y php-opcache
sudo systemctl restart php8.1-fpm
```

---

## ❓ Muammolar va Yechimlar

### "JavaScript runtime topilmadi":
```bash
sudo apt install -y nodejs
```

### "ffmpeg topilmadi":
```bash
sudo apt install -y ffmpeg
```

### "Permission denied":
```bash
sudo chown -R www-data:www-data /var/www/html/ytdownloader
sudo chmod -R 755 /var/www/html/ytdownloader
sudo chmod -R 777 /var/www/html/ytdownloader/uploads
```

### "502 Bad Gateway":
```bash
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx
```

---

## 📞 Qo'llab-quvvatlash

Agar muammo bo'lsa, quyidagi ma'lumotlarni yuboring:
1. `php --version`
2. `yt-dlp --version`
3. `node --version`
4. `/var/log/nginx/error.log` oxirgi 50 qator
5. `api_debug.log` faylini

---

**Muvaffaqiyatlar! 🎉**
