نشر المشروع تلقائيًا على Railway — دليل خطوة بخطوة

الهدف: بعد إعداد ربط المستودع في GitHub مع Railway، سيتم نشر أي `push` إلى الفرع الذي تختاره تلقائيًا.

ملفّات جاهزة في المستودع:
- `Dockerfile` و`docker-compose.yml` — لبناء الصورة محليًا أو عبر Railway.
- `includes/db.php` — يقرأ إعدادات الاتصال من متغيرات البيئة.
- `.env.example` — نموذج لمتغيرات البيئة.
- `.github/workflows/pre-deploy-checks.yml` — فحوصات تلقائية (Lint + build) قبل النشر.

الخطوة 1 — ادفع الكود إلى GitHub
1. تأكّد أنك تدفع التغييرات إلى GitHub (مثلاً إلى فرع `improve/deploy` أو `main`).

الخطوة 2 — الربط مع Railway (مرة واحدة فقط)
1. سجّل الدخول إلى https://railway.app.
2. اختر "New Project" → "Deploy from GitHub".
3. امنح Railway الوصول إلى المستودع الخاص بك على GitHub.
4. اختر الفرع الذي تريد النشر منه (مثلاً `main` أو `improve/deploy`).
5. Railway سيحاول اكتشاف إعدادات المشروع. اختَر "Docker" إن طُلب.

الخطوة 3 — إضافة قاعدة بيانات MySQL مُدارة
1. في لوحة مشروع Railway: Plugins → Add Plugin → MySQL → Create.
2. انسخ بيانات الاتصال (HOST, USER, PASSWORD, DATABASE).

الخطوة 4 — إضافة متغيرات البيئة في Railway
1. في Project → Variables أضف القيم التالية (من قاعدة البيانات التي أنشأتها):
   - `DB_HOST`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASS`
   - `APP_ENV=production`
   - `APP_DEBUG=false`

الخطوة 5 — تمكين النشر التلقائي
1. بعد الربط، Railway ستبني المشروع تلقائيًا عند كل `push` إلى الفرع الذي اخترته.
2. راجع صفحة Deployments لمشاهدة سجلات البناء والنشر.

الخطوة 6 — استيراد قاعدة البيانات
1. يمكنك استيراد `database.sql` باستخدام MySQL client:
```bash
mysql -h DB_HOST -u DB_USER -pDB_PASS DB_NAME < database.sql
```
أو استخدم واجهة Railway DB أو Adminer.

الخطوة 7 — ملفات `uploads/` وتخزين الملفات
- ملاحظة مهمة: نظام الملفات في Railway مؤقت. أي ملفات مرفوعة إلى `uploads/` قد لا تبقى بعد إعادة نشر أو إعادة تشغيل.
- لحلّ هذا في الإنتاج، استخدم S3 أو DigitalOcean Spaces وغيّر `upload_file()` لرفع الملفات هناك.

ملاحظات أمان وأفضل ممارسات
- لا ترفع `.env` إلى GitHub.
- خزّن أسرار الاتصال كمتغيرات في Railway.
- اختبر رفع/تحميل/مصادقة قبل الإعلان عن الموقع.

إن أردت، أستطيع:
- إضافة دعم عملي لـ S3 (تثبيت Composer وكتابة كود الرفع إلى S3).
- إنشاء workflow GitHub يقوم بدفع صورة Docker إلى GitHub Container Registry و/أو يحاول استدعاء Railway CLI (يتطلّب `RAILWAY_API_KEY`).
