/*
 * سرویس‌ورکر هاچ لند
 * ---------------------------------------------------------------------
 * هدف: فقط فایل‌های استاتیک (css/js/فونت/تصویر) کش بشن تا سایت سریع‌تر لود
 * بشه و آیکون نصب PWA فعال بشه. عمداً:
 *   - هیچ صفحه‌ی HTML کش نمی‌شه (نه صفحه اصلی، نه محصول، نه پنل) — چون
 *     قیمت/موجودی/سبد خرید/سفارش‌ها دائم عوض می‌شن و نسخه‌ی کهنه نشون دادن
 *     می‌تونه گمراه‌کننده یا حتی خطرناک باشه (مثلاً موجودی اشتباه).
 *   - هیچ درخواست POST/غیر-GET دست نمی‌خوره — فرم‌ها، افزودن به سبد، پرداخت
 *     و پنل مدیریت همیشه مستقیم با شبکه کار می‌کنن.
 * اگه بعداً خواستید یه صفحه‌ی آفلاین واقعی هم اضافه بشه، این فایل نقطه‌ی
 * شروع خوبیه؛ فعلاً برای اطمینان از درست‌کار‌کردن همه‌چیز، ساده نگهش داشتیم.
 *
 * آپدیت: صفحه‌ی آفلاین (offline.html) اضافه شد — فقط وقتی ناوبری بین
 * صفحات (کلیک لینک/رفرش) کاملاً بدون اینترنت با خطا مواجه بشه نشون داده
 * می‌شه؛ وقتی آنلاینید، صفحات همیشه مستقیم و تازه از شبکه میان (بدون تغییر
 * در رفتار قبلی).
 */

const CACHE_VERSION = 'hachland-static-v3';

const PRECACHE_URLS = [
  '/images/logo.png',
  '/favicon-32.png',
  '/offline.html',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_VERSION)
      .then((cache) => cache.addAll(PRECACHE_URLS))
      .catch(() => {})
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key !== CACHE_VERSION).map((key) => caches.delete(key)))
    )
  );
  self.clients.claim();
});

const STATIC_ASSET_RE = /\.(css|js|mjs|woff2?|ttf|otf|png|jpe?g|webp|gif|svg|ico)(\?.*)?$/i;

self.addEventListener('fetch', (event) => {
  const req = event.request;

  // فقط GET؛ فرم‌ها/API/پرداخت همیشه مستقیم می‌رن سراغ شبکه
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  // ناوبری بین صفحات (باز کردن یه صفحه‌ی HTML) — همیشه مستقیم و تازه از
  // شبکه میاد (بدون کش)، دقیقاً طبق همون سیاست بالا. تنها استثنا: وقتی
  // کاملاً آفلاینید و شبکه شکست می‌خوره، به‌جای صفحه‌ی خطای پیش‌فرض مرورگر،
  // صفحه‌ی آفلاین برندشده‌ی خودمون نشون داده می‌شه.
  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req).catch(() => caches.match('/offline.html'))
    );
    return;
  }

  // فقط همین دامنه، و فقط فایل استاتیک — هر چیز دیگه‌ای دست‌نخورده از
  // کنار سرویس‌ورکر رد می‌شه (رفتار پیش‌فرض مرورگر)
  if (!STATIC_ASSET_RE.test(url.pathname)) return;

  // stale-while-revalidate: فوراً نسخه‌ی کش‌شده (اگه بود) نشون بده، همزمان
  // از شبکه هم نسخه‌ی تازه رو بگیر و کش رو برای دفعه‌ی بعد آپدیت کن
  event.respondWith(
    caches.open(CACHE_VERSION).then((cache) =>
      cache.match(req).then((cached) => {
        const network = fetch(req)
          .then((res) => {
            if (res && res.ok) cache.put(req, res.clone());
            return res;
          })
          .catch(() => cached);
        return cached || network;
      })
    )
  );
});
