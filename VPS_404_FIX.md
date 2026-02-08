# 🚨 VPS Deploy - 404 Xatosini Tuzatish

## 📌 Asosiy Muammo

VPS serverda `yt_info.php` va `yt_api.php` fayllari topilmadi (404 error).

---

## ✅ Yechim: 3 Qadam

### 1️⃣ **Fayllarni VPS ga Yuklash**

VPS serveringizga SSH orqali ulanib, quyidagi fayllarni joylashtiring:

#### Variant A: Git orqali (TAVSIYA)
```bash
# VPS serverda
cd /var/www/html
sudo git pull origin master

# Yoki yangi clone
sudo rm -rf ytdownloader
sudo git clone https://github.com/webdeveloper94/ytdownloader.git
```

#### Variant B: FTP/SFTP orqali
Quyidagi fayllarni VPS serveringizga yuklang:
- `VPS_yt_info.php` → `/var/www/html/yt_info.php`
- `VPS_yt_api.php` → `/var/www/html/yt_api.php`

**YOKI** agar subdirectory ishlatilsa:
- `/var/www/html/ytdownloader/yt_info.php`
- `/var/www/html/ytdownloader/yt_api.php`

---

### 2️⃣ **Web Root ni Tekshirish**

VPS serverda fayl manzilini aniqlang:

```bash
# SSH orqali VPS ga kiring
ssh root@95.111.250.26

# Nginx konfiguratsiyasini ko'ring
sudo cat /etc/nginx/sites-enabled/default

# yoki
sudo cat /etc/nginx/sites-available/ytdownloader
```

**`root` direktivasini toping:**
```nginx
server {
    listen 80;
    server_name 95.111.250.26;
    root /var/www/html;           # ← Bu sizning root directory
    # yoki
    root /var/www/html/ytdownloader;  # ← Agar subdirectory bo'lsa
}
```

---

### 3️⃣ **download.php ni Yangilash**

Root directory ga qarab, `download.php` ni sozlang:

#### Agar root = `/var/www/html/` (fayllar to'g'ridan-to'g'ri root da):

```php
// download.php (34-qator va 219-qator)
$useLocal = false;  // PRODUCTION MODE

if ($useLocal) {
    // Lokal
} else {
    // VPS server - ROOT DIRECTORY
    $infoApi1 = "http://95.111.250.26/yt_info.php?url=" . urlencode($videoUrl);
    $infoApi2 = "http://95.111.250.26/yt_api.php?info=1&url=" . urlencode($videoUrl);
}

// 219-qator ham
if ($useLocal) {
    // ...
} else {
    $api = "http://95.111.250.26/yt_api.php?url=" . urlencode($videoUrl);
}
```

#### Agar root = `/var/www/html/ytdownloader/` (loyiha subdirectory da):

```php
// download.php (34-qator va 219-qator)
$useLocal = false;  // PRODUCTION MODE

if ($useLocal) {
    // Lokal
} else {
    // VPS server - SUBDIRECTORY
    $baseUrl = "http://95.111.250.26/ytdownloader/";
    $infoApi1 = $baseUrl . "yt_info.php?url=" . urlencode($videoUrl);
    $infoApi2 = $baseUrl . "yt_api.php?info=1&url=" . urlencode($videoUrl);
}
```

---

## 🧪 Test Qilish

### 1. SSH orqali test:
```bash
# VPS serverda
curl http://95.111.250.26/yt_info.php?url=https://www.youtube.com/watch?v=dQw4w9WgXcQ

# Yoki subdirectory bilan
curl http://95.111.250.26/ytdownloader/yt_info.php?url=https://www.youtube.com/watch?v=dQw4w9WgXcQ
```

**Kutilgan natija:** JSON format video ma'lumotlari

### 2. Brauzer orqali:
```
http://95.111.250.26/yt_info.php?url=https://www.youtube.com/watch?v=dQw4w9WgXcQ
```

**Kutilgan:** JSON response

---

## 🔧 Fayl Joylashuvi - To'liq Yo'riqnoma

### Variant 1: Root Directory (`/var/www/html/`)

```
/var/www/html/
│
├── index.php
├── login.php
├── register.php
├── yt_info.php      ← VPS_yt_info.php dan
├── yt_api.php       ← VPS_yt_api.php dan
│
├── user/
│   └── download.php
│
├── admin/
├── config/
└── ...
```

**URL:** `http://95.111.250.26/yt_info.php`

---

### Variant 2: Subdirectory (`/var/www/html/ytdownloader/`)

```
/var/www/html/
│
└── ytdownloader/
    ├── index.php
    ├── login.php
    ├── yt_info.php      ← VPS_yt_info.php dan
    ├── yt_api.php       ← VPS_yt_api.php dan
    │
    ├── user/
    │   └── download.php
    │
    ├── admin/
    └── ...
```

**URL:** `http://95.111.250.26/ytdownloader/yt_info.php`

---

## 📝 Qisqacha Checklist

- [ ] VPS ga SSH orqali ulaning
- [ ] Web root ni aniqlang (`/etc/nginx/sites-enabled/default`)
- [ ] `VPS_yt_info.php` va `VPS_yt_api.php` fayllarni to'g'ri joyga joylashtiring
- [ ] Fayllarni rename qiling (VPS_ prefiksiz)
- [ ] Ruxsatlarni sozlang: `chmod 644 yt_info.php yt_api.php`
- [ ] `download.php` da `$useLocal = false` qiling
- [ ] URL larni web root ga mos ravishda sozlang
- [ ] Test qiling: `curl` yoki brauzer orqali
- [ ] Katta video bilan test qiling

---

## 🆘 Agar Hali Ham 404 Bo'lsa

### 1. Nginx log ni tekshiring:
```bash
sudo tail -f /var/log/nginx/error.log
```

### 2. Fayl mavjudligini tekshiring:
```bash
ls -la /var/www/html/ | grep yt_
# yoki
ls -la /var/www/html/ytdownloader/ | grep yt_
```

### 3. PHP ishlaganini tekshiring:
```bash
curl http://95.111.250.26/index.php
```

### 4. Nginx configni qayta yuklang:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## ✅ Muvaffaqiyatli Deploy

Agar hammasi to'g'ri bo'lsa:
1. ✅ Kichik videolar 5-10 sekundda yuklanadi
2. ✅ Katta videolar 30-60 sekundda yuklanadi
3. ✅ Timeout xatolari yo'qoladi
4. ✅ Barcha formatlar ko'rinadi

**Omad! 🚀**
