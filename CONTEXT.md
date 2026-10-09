# 📋 وثيقة سياق المشروع (CONTEXT.md)

## 📌 نبذة عن المشروع
* **الاسم:** نظام إدارة الموانئ والإيرادات البحرية (GCPI Ports System).
* **الجهة:** الشركة العامة لموانئ العراق (GCPI).
* **الهدف:** إدارة شاملة لحركة الموانئ، مناولة الحاويات، تفريغ البضائع العامة والمشتقات النفطية، ومطابقة مراكز الإيرادات والتحصيل المالي.

## ✅ ما تم إنجازه حتى الآن
* **إدارة الموانئ والأرصفة (Ports):** تعريف الموانئ، الأرصفة، وتحديد ميزات وتفعيل الوحدات المينائية لكل ميناء.
* **حركة ومناولة الحاويات (Containers):** تتبع حركات الدخول والخروج والترانزيت، حالات الحاويات، تفاصيل البنود، وسجلات الحاويات.
* **سجلات البضائع والمشتقات النفطية:** رصد وتوثيق أوزان وحمولات البضائع وحالاتها التفصيلية والمنتجات النفطية.
* **مراكز الإيرادات والتحصيل المالي:** تسجيل الإيرادات الشهرية والسنوية ومطابقتها مع الخطط السنوية والشهور المالية.
* **سجل التدقيق الشامل (Audit Logs):** توثيق تدقيقي لكافة التعديلات والإدخالات مع هوية المستخدم والتوقيت عبر Laravel Auditing.
* **التقارير والمؤشرات البيانية:** لوحات بيانية متقدمة (Apex Charts) وتصدير تقارير الحركة والإيرادات بصيغ PDF وExcel.

## 🛠️ التقنيات المستخدمة
* **Backend:** Laravel 12 (PHP 8.2+)
* **Admin Panel:** Filament Admin v3/v5
* **Database:** MySQL
* **Packages:** Owen-it Laravel Auditing, Leandrocfe Apex Charts, Spatie Browsershot, Spatie Laravel PDF, Maatwebsite Excel, Google2FA

## 📂 هيكل الملفات الأساسية
* `app/Models/`: (Port, ContainerStatusRecord, ContainerItem, CargoStatusRecord, RevenueRecord, RevenueCenter, MonthlyPortRecord, Audit, User, إلخ).
* `app/Filament/Resources/`: (PortResource, ContainerStatusRecordResource, CargoStatusRecordResource, RevenueRecordResource, MonthlyPortRecordResource, AuditResource, إلخ).
* `app/Http/Controllers/`: (ReportPdfController, ReportExcelController).
* `database/migrations/`: 32 ملف هجرة تشمل جداول الحاويات، البضائع، الموانئ، وسجلات التدقيق.
