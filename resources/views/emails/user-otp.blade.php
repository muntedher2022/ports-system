<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تأكيد الهوية والتحقق الثنائي</title>
    <style>
        body {
            font-family: 'Cairo', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 30px 15px;
            direction: rtl;
            text-align: right;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 520px;
            background: #ffffff;
            margin: 0 auto;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            border: 1px solid #e5e7eb;
        }
        .header {
            background: linear-gradient(135deg, #1e1b4b 0%, #2e1065 50%, #312e81 100%);
            color: #ffffff;
            padding: 35px 25px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            color: #a5b4fc;
            letter-spacing: 0.5px;
        }
        .header .subtitle {
            margin: 8px 0 0 0;
            font-size: 14px;
            color: #e0e7ff;
            opacity: 0.9;
        }
        .content {
            padding: 35px 30px;
            color: #334155;
            line-height: 1.7;
        }
        .greeting {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 15px;
        }
        .description {
            font-size: 15px;
            color: #475569;
            margin-bottom: 25px;
        }
        .otp-container {
            text-align: center;
            margin: 30px auto;
            background: #f8fafc;
            border: 2px dashed #6366f1;
            border-radius: 14px;
            padding: 22px 20px;
            max-width: 320px;
        }
        .otp-code {
            font-family: 'Courier New', Courier, monospace, sans-serif;
            font-size: 38px;
            font-weight: 800;
            letter-spacing: 10px;
            color: #1e1b4b;
            display: inline-block;
            margin-right: -10px;
        }
        .warning-note {
            color: #dc2626;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.6;
            margin-top: 25px;
            padding: 12px 14px;
            background-color: #fef2f2;
            border-radius: 8px;
            border-right: 4px solid #ef4444;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            font-size: 13px;
            color: #64748b;
        }
        .meta-table td {
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .meta-label {
            font-weight: 600;
            color: #475569;
            width: 30%;
        }
        .meta-val {
            color: #0f172a;
            font-weight: 500;
        }
        .meta-val code {
            background: #e2e8f0;
            padding: 3px 8px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 12px;
            color: #334155;
        }
        .footer {
            background: #f8fafc;
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>تأكيد الهوية والتحقق الثنائي</h2>
        @if(!empty($projectName))
            <div class="subtitle">{{ $projectName }}</div>
        @endif
    </div>
    
    <div class="content">
        <div class="greeting">مرحباً {{ $userName }}،</div>
        <div class="description">
            هناك محاولة لتسجيل الدخول إلى حسابك{{ !empty($projectName) ? ' في ' . $projectName : '' }}.
            يرجى استخدام الرمز التالي لإكمال عملية التحقق والدخول الآمن:
        </div>
        
        <div class="otp-container">
            <span class="otp-code">{{ $otp }}</span>
        </div>
        
        <div class="warning-note">
            ملاحظة: هذا الرمز صالح لمدة 5 دقائق فقط. إذا لم تكن أنت من طلب هذا الرمز، يرجى تجاهل الرسالة وتغيير كلمة مرور حسابك فوراً.
        </div>
        
        <table class="meta-table">
            <tr>
                <td class="meta-label">عنوان الـ IP:</td>
                <td class="meta-val"><code>{{ $ipAddress }}</code></td>
            </tr>
            <tr>
                <td class="meta-label">التوقيت:</td>
                <td class="meta-val" dir="ltr" style="text-align: right;">{{ $time }}</td>
            </tr>
            @if(!empty($projectName))
            <tr>
                <td class="meta-label">النظام:</td>
                <td class="meta-val">{{ $projectName }}</td>
            </tr>
            @endif
        </table>
    </div>
    
    <div class="footer">
        تم إرسال هذا البريد تلقائياً لحماية أمن حسابك.
    </div>
</div>

</body>
</html>
