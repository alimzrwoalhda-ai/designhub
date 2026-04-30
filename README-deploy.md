نصائح سريعة لنشر المشروع إلى منصة مجانية (Render / Railway) باستخدام Docker أو GitHub:

1) إعداد مستودع Git
- ابدأ مستودع: `git init`، ثم `git add .` و`git commit -m "initial"`.

2) استخدام Docker (محلياً)
- تشغيل محلي مع docker-compose:
```bash
docker compose up --build
```
- افتح: http://localhost:8080

3) نشر إلى Render
- ادفع المستودع إلى GitHub.
- في Render، أنشئ خدمة Web Service من GitHub واختر Docker.
- أضف المتغيرات البيئية (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
- استعمل خدمة Managed PostgreSQL أو MySQL إن رغبت.

4) نشر إلى Railway
- اربط مستودع GitHub بـ Railway.
- أضف خدمة Docker أو Node/Static بحسب الاختيار.
- أضف متغيرات البيئة ونفّذ `database.sql` عبر phpMyAdmin أو أداة إدارة قواعد البيانات.

5) إعداد .env
- أنشئ ملف `.env` بجذر المشروع يحتوي:
```
DB_HOST=127.0.0.1
DB_NAME=designhub_arabic
DB_USER=root
DB_PASS=
```

6) قواعد أمنية إضافية
- تأكد أن مجلدات `uploads` غير قابلة لتنفيذ ملفات سكربت.
- خزّن أسرار الإنتاج في متغيرات البيئة لا في ملفات.

7) إضافة تكامل مستمر (CI) بسيط على GitHub Actions
- أنشأت ملف إعداد CI في `.github/workflows/php-ci.yml` يقوم بالآتي:
	- التحقق من الشيفرة عند `push` و`pull_request` للفرع `main` وأفرع الميزات.
	- يجهز PHP 8.2 ثم ينفذ `php -l` (فحص تركيب) على ملفات `.php` في المشروع.

يمكنك تعديل الملف ليتضمن اختبارات أخرى مثل `phpunit` أو بناء صورة Docker ورفعها إلى registry.

ملاحظات عامة:
- لا أستطيع نشر الحساب نيابة عنك لأن ذلك يحتاج بيانات اعتماد إلى المنصات. أستطيع تجهيز المستودع وشرح الخطوات بالتفصيل، أو تنفيذ نشر إذا زوّدتني بصلاحيات مناسبة أو رابط المستودع وحق الوصول.
