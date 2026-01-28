# 🎨 Customization Guide

## How to Customize Colors

Semua warna UI dapat dikustomisasi dengan mudah melalui CSS variables. Ikuti panduan ini untuk mengubah tema sesuai kebutuhan.

### Step 1: Buka file style.css
```
File: public/css/style.css
Baris: 1-18 (section :root)
```

### Step 2: Ubah Color Variables

#### Option A: Mengubah di Style.css (Permanent)

Cari section `:root` di awal file:

```css
:root {
    --primary-bg: #0f0f1e;      /* Ubah warna background utama */
    --secondary-bg: #1a1a2e;    /* Ubah warna background sekunder */
    --accent-green: #00d4aa;    /* Ubah warna hijau */
    --accent-blue: #00b4ff;     /* Ubah warna biru */
    --accent-purple: #a855f7;   /* Ubah warna ungu */
    --accent-orange: #ff6b35;   /* Ubah warna orange */
    --accent-red: #ff4757;      /* Ubah warna merah */
    --accent-pink: #ff006e;     /* Ubah warna pink */
    --accent-yellow: #ffd60a;   /* Ubah warna kuning */
    --text-white: #e8eaed;      /* Ubah warna text putih */
    --text-light: #f5f5f5;      /* Ubah warna text cerah */
    --text-muted: #9ca3af;      /* Ubah warna text muted */
    --border-color: #2d3748;    /* Ubah warna border */
    --card-bg: #16213e;         /* Ubah warna card background */
    --hover-bg: #0f3460;        /* Ubah warna hover */
}
```

#### Option B: Menggunakan Browser DevTools (Testing)

1. Buka browser DevTools (F12)
2. Pilih Elements/Inspector
3. Cari `.navbar` atau element apapun
4. Edit CSS langsung di DevTools untuk preview
5. Salin warna yang Anda suka

---

## 🎯 Popular Color Themes

### Theme 1: Dark Blue (Current)
```css
--primary-bg: #0f0f1e;
--secondary-bg: #1a1a2e;
--accent-blue: #00b4ff;
--accent-green: #00d4aa;
```

### Theme 2: Purple Vibes
```css
--primary-bg: #1a0e2e;
--secondary-bg: #16213e;
--accent-blue: #9b59b6;
--accent-green: #a569bd;
--accent-orange: #e94b3c;
```

### Theme 3: Green Energy
```css
--primary-bg: #0d1b0f;
--secondary-bg: #1a2e1a;
--accent-blue: #1abc9c;
--accent-green: #27ae60;
--accent-orange: #f39c12;
```

### Theme 4: Ocean Blue
```css
--primary-bg: #0a1931;
--secondary-bg: #1a3a52;
--accent-blue: #00d4ff;
--accent-green: #00ff88;
--accent-orange: #ffaa00;
```

---

## 🎨 Where to Use Each Color

### --accent-blue
Digunakan untuk:
- Primary buttons
- Link colors
- Icon highlights
- Main CTA

### --accent-green
Digunakan untuk:
- Success messages
- Verified badges
- Positive indicators

### --accent-orange
Digunakan untuk:
- Warnings
- Pending status
- Caution alerts

### --accent-purple
Digunakan untuk:
- Special items
- Premium badges
- Highlights

### --accent-yellow
Digunakan untuk:
- Gold ranks (1st place)
- Premium features
- Badges

---

## 🔧 Component-Level Customization

### Change Stat Box Color

```css
.stat-box {
    background: linear-gradient(135deg, var(--card-bg) 0%, rgba(31, 41, 55, 0.5) 100%);
    border: 2px solid rgba(0, 180, 255, 0.15);  /* Ubah rgba untuk border color */
}

.stat-box:hover {
    border-color: rgba(0, 180, 255, 0.4);  /* Ubah hover border color */
    box-shadow: 0 16px 32px rgba(0, 180, 255, 0.15);  /* Ubah shadow color */
}
```

### Change Button Style

```css
.btn-primary-enhanced {
    background: linear-gradient(135deg, var(--accent-blue) 0%, #0077cc 100%);
    /* Ubah #0077cc dengan warna yang diinginkan */
}

.btn-primary-enhanced:hover {
    box-shadow: 0 10px 20px rgba(0, 180, 255, 0.3);
    /* Ubah rgba(0, 180, 255, 0.3) dengan warna shadow */
}
```

### Change Table Header Color

```css
.table-enhanced thead {
    background: linear-gradient(135deg, rgba(0, 180, 255, 0.1) 0%, rgba(0, 212, 170, 0.05) 100%);
    /* Ubah rgba colors untuk gradient header */
}

.table-enhanced thead th {
    color: var(--accent-blue);  /* Ubah text color */
    border-bottom: 2px solid var(--border-color);
}
```

---

## 📐 Gradient Customization

### Create Custom Gradient

Format: `linear-gradient(angle, color1, color2, color3, ...)`

**Examples:**

```css
/* Blue to Green gradient */
background: linear-gradient(135deg, #00b4ff 0%, #00d4aa 100%);

/* Purple to Pink gradient */
background: linear-gradient(135deg, #a855f7 0%, #ff006e 100%);

/* Orange to Yellow gradient */
background: linear-gradient(135deg, #ff6b35 0%, #ffd60a 100%);

/* Diagonal gradient (45deg) */
background: linear-gradient(45deg, color1, color2);

/* Radial gradient */
background: radial-gradient(circle, color1, color2);
```

---

## 🎭 Shadow Customization

### Change Box Shadow

Format: `box-shadow: X Y BLUR SPREAD COLOR`

**Contoh:**

```css
/* Subtle shadow */
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);

/* Medium shadow */
box-shadow: 0 8px 16px rgba(0, 180, 255, 0.2);

/* Strong shadow with color */
box-shadow: 0 12px 24px rgba(0, 180, 255, 0.4);

/* Multiple shadows */
box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 30px rgba(0, 180, 255, 0.1);
```

---

## 🎪 Global Changes

### Change Primary Button Color Everywhere

Search dan replace di `style.css`:

**Find:**
```css
.btn-primary-enhanced {
    background: linear-gradient(135deg, var(--accent-blue) 0%, #0077cc 100%);
```

**Replace with:**
```css
.btn-primary-enhanced {
    background: linear-gradient(135deg, #00ff88 0%, #00ccaa 100%);
```

### Change All Accent Colors

**Find & Replace semua:**
```
var(--accent-blue) → var(--accent-green)
var(--accent-green) → var(--accent-blue)
```

---

## 💾 Best Practices

1. **Backup original style.css** sebelum melakukan perubahan
2. **Use color codes yang konsisten** (hex format)
3. **Test di semua halaman** setelah mengubah warna
4. **Use browser DevTools** untuk preview sebelum save
5. **Document perubahan** yang Anda buat

---

## 🖼️ Color Picker Tools

Gunakan tools online untuk mendapatkan warna yang sempurna:
- **Coolors.co** - Generate color palettes
- **Color-hex.com** - Find hex codes
- **Palette.pinetools.com** - Create gradients
- **Cssgradient.io** - Generate CSS gradients

---

## 📱 Testing Responsiveness

Setelah mengubah warna, test di:
- Desktop (1920x1080)
- Tablet (768x1024)
- Mobile (375x667)

Pastikan contrast ratio tetap baik untuk accessibility.

---

## ⚠️ Important Notes

- **Jangan hapus CSS variables** tanpa backup
- **Perubahan di style.css** akan mempengaruhi semua halaman
- **Setiap halaman punya `additionalCSS`** untuk styling khusus
- **Cache browser** mungkin perlu di-clear untuk melihat perubahan

---

## 🆘 Troubleshooting

### Warna tidak berubah?
1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh (Ctrl+F5)
3. Periksa apakah perubahan sudah tersave
4. Restart web server

### Warna terlihat aneh?
1. Gunakan hex color yang valid (#000000 format)
2. Check contrast ratio di DevTools
3. Pastikan format gradients benar
4. Test di multiple browsers

### Gradient tidak muncul?
1. Pastikan format gradients benar: `linear-gradient(angle, color, color)`
2. Gunakan vendor prefixes jika perlu: `-webkit-background-clip`
3. Check browser compatibility

---

## 📞 Need Help?

Jika ada pertanyaan atau ingin customize lebih lanjut:
1. Check file `UI_IMPROVEMENTS.md` untuk detail teknis
2. Lihat contoh di `style.css` untuk inspirasi
3. Gunakan browser DevTools untuk experiment

---

**Happy Customizing!** 🎨✨
