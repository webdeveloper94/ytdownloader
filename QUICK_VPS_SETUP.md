# ⚡ Tezkor VPS Sozlash (5 Daqiqa)

## 🎯 VPS ga Fayllarni Joylashtirish

### 1️⃣ SSH orqali VPS ga Ulanish
```bash
ssh root@95.111.250.26
```

### 2️⃣ Fayllarni Joylashtirish

**Eng oson usul - Git pull:**
```bash
cd /var/www/html
git pull origin master

# VPS_yt_info.php ni yt_info.php ga o'zgartirish
mv VPS_yt_info.php yt_info.php
mv VPS_yt_api.php yt_api.php

# Ruxsatlar
chmod 644 yt_info.php yt_api.php
```

### 3️⃣ Test Qilish
```bash
# Terminal da test
curl "http://95.111.250.26/yt_info.php?url=https://www.youtube.com/watch?v=dQw4w9WgXcQ"
```

**Kutilgan:** JSON formatida video ma'lumotlari

### 4️⃣ Production Mode Yoqish

**Lokal kompyuterda** `download.php` da:

```php
// 34-qator
$useLocal = false;  // ← false qiling

// 219-qator
$useLocal = false;  // ← false qiling
```

**Saqlang va Git ga push qiling:**
```bash
git add user/download.php
git commit -m "Production mode enabled"
git push origin master
```

**VPS da pull qiling:**
```bash
cd /var/www/html
git pull origin master
```

### 5️⃣ Brauzerda Test Qiling
```
http://95.111.250.26/
```

✅ Tayyor!

---

## 🔍 Agar Subdirectory Ishlatilsa

Agar VPS da loyiha `http://95.111.250.26/ytdownloader/` shaklida bo'lsa:

### download.php da (42-43 qator):
```php
} else {
    // VPS - SUBDIRECTORY
    $baseUrl = "http://95.111.250.26/ytdownloader/";
    $infoApi1 = $baseUrl . "yt_info.php?url=" . urlencode($videoUrl);
    $infoApi2 = $baseUrl . "yt_api.php?info=1&url=" . urlencode($videoUrl);
}
```

### 219-qator:
```php
} else {
    $api = "http://95.111.250.26/ytdownloader/yt_api.php?url=" . urlencode($videoUrl);
}
```

---

## ✅ Muvaffaqiyat Belgilari

- ✅ `http://95.111.250.26/yt_info.php` JSON qaytaradi
- ✅ Video metadata ko'rinadi
- ✅ Formatlar ro'yxati chiqadi
- ✅ Katta videolar timeout bermaydi

**Omad! 🚀**
