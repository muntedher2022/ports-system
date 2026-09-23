<?php

namespace App\Licensing;

class HardwareFingerprint
{
    /**
     * الحصول على البصمة الرقمية الفريدة للجهاز الحالي.
     *
     * @return string
     */
    public static function get(): string
    {
        $os = strtoupper(substr(PHP_OS, 0, 3));
        $rawIdentifier = '';

        if ($os === 'WIN') {
            $rawIdentifier = self::getWindowsHardwareId();
        } else {
            // نظام تشغيل آخر (Linux / macOS)
            $rawIdentifier = self::getLinuxMacHardwareId();
        }

        // إذا فشل الحصول على أي معرف، نستخدم اسم المضيف كملجأ أخير
        if (empty($rawIdentifier)) {
            $rawIdentifier = gethostname();
        }

        // تشفير البصمة لإنتاج صيغة مرتبة وسهلة القراءة والتداول
        $hash = hash('sha256', $rawIdentifier);
        
        // تحويل الـ hash إلى صيغة HWID-XXXX-XXXX-XXXX-XXXX
        $formatted = 'HWID-' . implode('-', str_split(strtoupper(substr($hash, 0, 16)), 4));

        return $formatted;
    }

    /**
     * الحصول على معرّف العتاد لنظام ويندوز باستخدام PowerShell أو WMIC.
     */
    private static function getWindowsHardwareId(): string
    {
        $identifiers = [];

        // 1. محاولة استخدام PowerShell (متوافق مع جميع إصدارات ويندوز الحديثة خاصة Windows 11 التي حذفت أداة wmic)
        if (function_exists('shell_exec')) {
            try {
                $psCommand = 'powershell -NoProfile -ExecutionPolicy Bypass -Command "(Get-CimInstance Win32_BaseBoard).SerialNumber; (Get-CimInstance Win32_ComputerSystemProduct).UUID; (Get-CimInstance Win32_Processor).ProcessorId" 2>nul';
                $output = @shell_exec($psCommand);
                if ($output) {
                    $lines = array_filter(array_map('trim', explode("\n", $output)));
                    $invalidValues = ['To be filled by O.E.M.', 'None', 'Default string', 'Not Specified', '00000000-0000-0000-0000-000000000000'];
                    foreach ($lines as $line) {
                        if (!empty($line) && !in_array($line, $invalidValues)) {
                            $identifiers[] = $line;
                        }
                    }
                }
            } catch (\Exception $e) {
                // استمرار المحاولة بالطرق البديلة
            }
        }

        // 2. إذا لم تتوفر المعرفات عبر PowerShell، محاولة استخدام WMIC كبديل للأجهزة القديمة
        if (empty($identifiers)) {
            // 1. الحصول على الرقم التسلسلي للوحة الأم (Motherboard Serial)
            $motherboard = self::runCommand('wmic baseboard get serialnumber 2>nul');
            if ($motherboard) {
                $identifiers[] = $motherboard;
            }

            // 2. الحصول على المعرف الفريد للمنتج (System UUID)
            $uuid = self::runCommand('wmic csproduct get uuid 2>nul');
            if ($uuid) {
                $identifiers[] = $uuid;
            }

            // 3. الحصول على الرقم التسلسلي للمعالج (CPU Processor ID)
            $cpu = self::runCommand('wmic cpu get processorid 2>nul');
            if ($cpu) {
                $identifiers[] = $cpu;
            }
        }

        // دمج المعرفات لتكوين سلسلة فريدة للجهاز
        return implode('|', $identifiers);
    }

    /**
     * الحصول على معرف العتاد لأنظمة لينكس وماك.
     */
    private static function getLinuxMacHardwareId(): string
    {
        $identifiers = [];

        if (file_exists('/etc/machine-id')) {
            $identifiers[] = trim(file_get_contents('/etc/machine-id'));
        } elseif (file_exists('/var/lib/dbus/machine-id')) {
            $identifiers[] = trim(file_get_contents('/var/lib/dbus/machine-id'));
        }

        // محاولة جلب عنوان الـ MAC للبطاقة الافتراضية
        $mac = self::runCommand("ip link | awk '/ether/ {print $2}' | head -n 1");
        if ($mac) {
            $identifiers[] = $mac;
        }

        return implode('|', $identifiers);
    }

    /**
     * تشغيل أوامر النظام وتنظيف النتائج.
     */
    private static function runCommand(string $command): ?string
    {
        if (!function_exists('shell_exec')) {
            return null;
        }

        try {
            // تشغيل الأمر وكتم الأخطاء البرمجية
            $output = @shell_exec($command);
            if ($output === null || $output === false) {
                return null;
            }

            // إزالة رأس الجدول (السطر الأول مثل "SerialNumber" أو "UUID") وتنظيف المسافات والأسطر الفارغة
            $lines = array_filter(array_map('trim', explode("\n", $output)));
            if (count($lines) > 1) {
                // استبعاد السطر الأول (العنوان) وأخذ القيمة الفعلية
                array_shift($lines);
                $value = trim(implode('', $lines));
                
                // تجاهل القيم الافتراضية غير المفيدة التي تضعها بعض الشركات المصنعة
                $invalidValues = ['To be filled by O.E.M.', 'None', 'Default string', 'Not Specified', '00000000-0000-0000-0000-000000000000'];
                if (!in_array($value, $invalidValues) && !empty($value)) {
                    return $value;
                }
            }
        } catch (\Exception $e) {
            // تجاهل أي استثناءات تحدث أثناء التشغيل
        }

        return null;
    }
}
