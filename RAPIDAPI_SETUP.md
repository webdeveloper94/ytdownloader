# 🚀 RapidAPI Setup Guide

## Nima O'zgardi?

✅ **yt-dlp o'chirildi** - Endi kerak emas!  
✅ **RapidAPI integratsiyasi** - Professional YouTube API  
✅ **.env konfiguratsiya** - Xavfsiz credentials  
✅ **Timeout muammolari hal qilindi** - API tezroq ishlaydi  

---

## Tezkor Sozlash

### 1. RapidAPI Key Olish

1. [RapidAPI.com](https://rapidapi.com) ga ro'yxatdan o'ting
2. [YT API](https://rapidapi.com/ytjar/api/yt-api) ga obuna bo'ling
3. API key ni nusxalang

### 2. Environment Sozlash

`.env` faylni tekshiring (allaqachon sozlangan):

```env
# Database
DB_HOST=localhost:3307
DB_NAME=ytdownloader
DB_USER=root
DB_PASS=root

# RapidAPI
RAPIDAPI_KEY=571c48cfcbmsh691468f84ccbcc9apic1f814jsnb6685146df3a
RAPIDAPI_HOST=yt-api.p.rapidapi.com
```

### 3. Test Qilish

```bash
# Lokal serverda
http://localhost/ytdownloader/yt_info.php?url=https://www.youtube.com/watch?v=dQw4w9WgXcQ
```

**Kutilgan:** JSON formatda video ma'lumotlari

---

## Arxitektura

### Yangi Fayl Tuzilmasi

```
ytdownloader/
├── .env                      # Environment variables (GIT IGNORE!)
├── .env.example              # Example config
├── composer.json             # Dependencies
│
├── config/
│   └── db.php               # .env dan o'qiydi
│
├── includes/
│   ├── auth.php
│   └── rapidapi.php         # ✨ YANGI: API helper functions
│
├── yt_info.php              # ✅ RapidAPI ga qayta yozildi
├── yt_api.php               # ✅ RapidAPI ga qayta yozildi
├── VPS_yt_info.php          # ✅ RapidAPI
├── VPS_yt_api.php           # ✅ RapidAPI
│
└── user/
    └── download.php         # ✅ To'liq yangilandi
```

---

## API Endpoint Tafsilotlari

### RapidAPI YT API

**Base URL:** `https://yt-api.p.rapidapi.com`

**Endpoint:** `/dl?id={video_id}`

**Headers:**
```
X-RapidAPI-Host: yt-api.p.rapidapi.com
X-RapidAPI-Key: YOUR_KEY_HERE
```

**Response:**
```json
{
  "title": "Video nomi",
  "thumbnail": "https://...",
  "description": "Tavsif",
  "formats": [
    {
      "format_id": "22",
      "ext": "mp4",
      "height": 720,
      "url": "https://...",
      "filesize": 12345678
    }
  ]
}
```

---

## RapidAPI vs yt-dlp

| Feature | yt-dlp | RapidAPI |
|---------|---------|----------|
| **Setup** | Server dependency | Environment variable |
| **Speed** | Sekin (60-180s) | Tez (5-10s) ⚡ |
| **Reliability** | Timeout issues | Stable API ✅ |
| **Node.js** | Kerak edi | Kerak emas ✅ |
| **ffmpeg** | Kerak edi | Kerak emas ✅ |
| **Updates** | Manual | Auto ✅ |

---

## VPS Deploy

### 1. Fayllarni Upload Qilish

```bash
# VPS da
cd /var/www/html
git pull origin master
```

### 2. .env Sozlash

```bash
# .env.example dan nusxa oling
cp .env.example .env

# Edit qiling
nano .env
```

`.env` da:
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` sozlang
- `RAPIDAPI_KEY` ni kiriting

### 3. Ruxsatlar

```bash
chmod 644 .env
chmod 755 includes/rapidapi.php
```

### 4. Test

```bash
curl "http://YOUR_VPS_IP/yt_info.php?url=https://www.youtube.com/watch?v=dQw4w9WgXcQ"
```

---

## Troubleshooting

### "RapidAPI credentials not configured"

✅ `.env` fayl mavjudligini tekshiring  
✅ `RAPIDAPI_KEY` to'g'ri ekanligini tasdiqlang

### "Invalid YouTube URL"

✅ URL formatini tekshiring  
✅ `youtube.com` yoki `youtu.be` bo'lishi kerak

### "API Error: HTTP 429"

⚠️ RapidAPI limit tugagan  
📊 Free tier: 500 requests/month  
💰 Paid plan zarur

### "No download URL found"

⚠️ Ba'zi videolar download URL qaytarmaydi  
🔒 Mumkin sabab: DRM, private, age-restricted

---

## Xavfsizlik

### .env Fayl

🔒 **GIT GA YUKLAMANG!**  
✅ `.gitignore` da `.env` bor  
✅ Faqat `.env.example` commit qiling

### API Key Himoyasi

🔐 Serverda faqat root foydalanuvchi o'qiy oladi  
📝 Production da environment variable ishlatiladi

---

## Free Tier Limits

- **500 requests/month** besplatno
- Har bir video info request = 1 call
- Download = 0 calls (direct URL)
- ~16 video/kun

### Optimizatsiya

💡 Keshni ishlatish (kelgusida)  
💡 Popular videolarni pre-cache qilish  
💡 User limit qo'yish

---

## Keyingi Qadamlar

1. ✅ Test qilish - Turli videolar bilan
2. 📊 Monitoring - API usage tracking
3. 💰 Paid plan (agar kerak bo'lsa)
4. 🚀 Production deploy

**Muvaffaqiyatlar! 🎉**
