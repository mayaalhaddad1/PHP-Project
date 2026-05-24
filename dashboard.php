<?php
require_once __DIR__ . '/connection.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <title>لوحة التحكم</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <link rel="stylesheet" href="lginCSS.css">
    <link rel="stylesheet" href="assets.css">
        <style>
            :root{
                --bg:#f5f7fb;
                --card:#ffffff;
                --muted:#6b7280;
                --accent:#2563eb;
                --accent-2:#06b6d4;
                --radius:12px;
                --shadow: 0 6px 18px rgba(13,38,59,0.08);
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            }
            *{box-sizing:border-box}
            body{margin:0;background:var(--bg);color:#0f172a;}
            header{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff;padding:20px 24px;border-bottom-left-radius:18px;border-bottom-right-radius:18px}
            .container{max-width:1100px;margin:28px auto;padding:0 20px}
            .header-row{display:flex;align-items:center;justify-content:space-between;gap:12px}
            .greeting{font-size:1.05rem}
            .controls{display:flex;gap:10px;align-items:center}
            .btn{background:rgba(255,255,255,0.12);color:#fff;padding:8px 12px;border-radius:10px;text-decoration:none;font-weight:600;border:1px solid rgba(255,255,255,0.06)}
            .btn.logout{background:transparent;border:1px solid rgba(255,255,255,0.18)}

            nav.breadcrumb{margin-top:14px}
            nav.breadcrumb a{color:rgba(255,255,255,0.9);text-decoration:none;margin-left:12px;font-size:0.95rem}

            main{margin-top:22px}
            .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px}
            .card{background:var(--card);padding:18px;border-radius:var(--radius);box-shadow:var(--shadow);display:flex;flex-direction:column;gap:12px;align-items:flex-end;text-align:right}
            .card .icon{width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,rgba(37,99,235,0.12),rgba(6,182,212,0.12));display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--accent)}
            .card h4{margin:0;font-size:1.02rem}
            .card p{margin:0;color:var(--muted);font-size:0.92rem}
            .card a{margin-top:auto;align-self:stretch;display:block;text-align:center;padding:10px;border-radius:8px;background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff;text-decoration:none;font-weight:600}

            footer{margin-top:28px;text-align:center;color:var(--muted);font-size:0.9rem;padding:30px 10px}

            @media (max-width:420px){
                header{padding:16px}
                .greeting{font-size:0.95rem}
            }
        </style>
    </head>
<body>
 <header>
    <div class="container header-row">
        <div>
            <h2 style="margin:0">لوحة التحكم</h2>
            <nav class="breadcrumb" aria-label="breadcrumb">
                <a href="#">الرئيسية</a>
                <a href="dashboard.php">لوحة التحكم</a>
            </nav>
        </div>
        <div class="controls">
            <div class="greeting">مرحباً، <?php echo htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8'); ?> </div>
            <a class="btn logout" href="logout.php">تسجيل الخروج</a>
        </div>
    </div>
 </header>

    <div class="container fade-in">
    <main>
        <section style="margin-bottom:18px">
            <h3 style="margin:0 0 8px 0">القائمة السريعة</h3>
            <p style="margin:0;color:var(--muted)">استخدم البطاقات التالية للوصول السريع إلى مهام إدارة الأخبار والفئات.</p>
        </section>

        <section class="grid" aria-label="quick actions">
            <div class="card">
                <div class="icon">+</div>
                <h4>إضافة فئة</h4>
                <p>إنشاء فئات جديدة لتنظيم الأخبار.</p>
                <a href="addCategory.php">اذهب إلى الفئة</a>
            </div>

            <div class="card">
                <div class="icon">📂</div>
                <h4>عرض الفئات</h4>
                <p>استعرض الفئات الموجودة وعدلها أو احذفها.</p>
                <a href="showCategory.php">عرض الفئات</a>
            </div>

            <div class="card">
                <div class="icon">📰</div>
                <h4>عرض الأخبار</h4>
                <p>عرض كل الأخبار المفعلة في الموقع.</p>
                <a href="showNews.php">عرض الأخبار</a>
            </div>

            <div class="card">
                <div class="icon">✏️</div>
                <h4>إضافة خبر</h4>
                <p>اضف خبرًا جديدًا مع وسائط وتصنيف مناسب.</p>
                <a href="addNew.php">أضف خبر</a>
            </div>

            <div class="card">
                <div class="icon">🗑️</div>
                <h4>الأخبار المحذوفة</h4>
                <p>راجع أو استعد الأخبار المحذوفة.</p>
                <a href="showdeletedNews.php">عرض المحذوفات</a>
            </div>
        </section>

    </main>

    <footer>
        <p>Maya ALhaddad — 220230939</p>
    </footer>
 </div>
</body>
</html>


