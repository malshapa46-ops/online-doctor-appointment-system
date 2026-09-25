<?php
require_once 'db.php';

$products = [
    [
        'category' => 'Personal Care',
        'icon' => 'fa-pump-soap',
        'name' => 'Gentle Hand Sanitizer',
        'description' => 'A convenient everyday hygiene essential for keeping hands clean when soap and water are not immediately available.',
        'details' => 'Portable size • Quick-drying formula • Everyday use'
    ],
    [
        'category' => 'First Aid Essentials',
        'icon' => 'fa-kit-medical',
        'name' => 'Family First Aid Kit',
        'description' => 'A practical collection of basic first-aid supplies for minor everyday cuts, scrapes, and household emergencies.',
        'details' => 'Dressings • Gauze • Adhesive tape • Safety scissors'
    ],
    [
        'category' => 'Healthcare Equipment',
        'icon' => 'fa-heart-pulse',
        'name' => 'Digital Blood Pressure Monitor',
        'description' => 'An easy-to-use home monitoring device for recording blood pressure readings for discussion with a healthcare professional.',
        'details' => 'Digital display • Easy operation • Home monitoring'
    ],
    [
        'category' => 'Healthcare Equipment',
        'icon' => 'fa-temperature-half',
        'name' => 'Digital Thermometer',
        'description' => 'A simple digital thermometer for checking body temperature at home when needed.',
        'details' => 'Fast reading • Clear display • Family-friendly design'
    ],
    [
        'category' => 'Wellness Products',
        'icon' => 'fa-person-walking',
        'name' => 'Wellness & Mobility Support',
        'description' => 'Selected wellness accessories that support comfortable movement and daily self-care routines.',
        'details' => 'Daily wellness • Comfort support • Home use'
    ],
    [
        'category' => 'Medical Accessories',
        'icon' => 'fa-mask-face',
        'name' => 'Protective Face Masks',
        'description' => 'Comfortable protective masks suitable for everyday travel, public spaces, and healthcare visits.',
        'details' => 'Comfort fit • Lightweight • Convenient pack'
    ],
    [
        'category' => 'Medical Accessories',
        'icon' => 'fa-hand-holding-medical',
        'name' => 'Reusable Hot & Cold Pack',
        'description' => 'A reusable comfort accessory for hot or cold application according to professional guidance.',
        'details' => 'Reusable design • Flexible use • Easy storage'
    ],
    [
        'category' => 'Elderly Care Products',
        'icon' => 'fa-person-cane',
        'name' => 'Elderly Daily Care Kit',
        'description' => 'A thoughtfully selected group of everyday care items that can help support comfortable home routines for older adults.',
        'details' => 'Daily assistance • Home care • Easy organization'
    ]
];

$selected_category = trim($_GET['category'] ?? '');
$search = trim($_GET['search'] ?? '');
$visible_products = array_filter($products, function ($product) use ($selected_category, $search) {
    $matches_category = $selected_category === '' || $product['category'] === $selected_category;
    $haystack = strtolower($product['name'] . ' ' . $product['category'] . ' ' . $product['description']);
    $matches_search = $search === '' || strpos($haystack, strtolower($search)) !== false;
    return $matches_category && $matches_search;
});
$categories = array_values(array_unique(array_column($products, 'category')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Healthcare Products - DOC.lk</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root { --primary:#2563eb; --primary-dark:#1d4ed8; --navy:#0f172a; --muted:#64748b; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            color: var(--navy);
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 88% 6%, rgba(37,99,235,.13), transparent 28rem), linear-gradient(135deg,#f8fbff,#eef4ff);
        }
        .page-wrap { width:min(1160px,calc(100% - 2rem)); margin:0 auto; padding:2.5rem 0 3.5rem; }
        .topbar { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1.5rem; }
        .brand { color:var(--navy); font-size:1.35rem; font-weight:800; letter-spacing:-.04em; text-decoration:none; }
        .brand span { color:var(--primary); }
        .back-link { color:var(--primary-dark); font-size:.85rem; font-weight:700; text-decoration:none; }
        .hero { position:relative; overflow:hidden; padding:clamp(1.6rem,4vw,3rem); border-radius:30px; color:#fff; background:linear-gradient(125deg,#0b2b5c,#2563eb); box-shadow:0 20px 45px rgba(30,64,175,.2); }
        .hero::after { content:''; position:absolute; width:250px; height:250px; right:-75px; top:-105px; border:28px solid rgba(255,255,255,.1); border-radius:50%; }
        .hero-content { position:relative; z-index:1; max-width:780px; }
        .hero-icon { display:inline-flex; align-items:center; justify-content:center; width:54px; height:54px; margin-bottom:1rem; border-radius:17px; color:var(--primary); background:#fff; font-size:1.5rem; }
        .hero h1 { margin:0 0 .7rem; font-size:clamp(1.8rem,4vw,2.8rem); letter-spacing:-.04em; }
        .hero p { margin:0; color:#dbeafe; line-height:1.7; }
        .card { margin-top:1.5rem; padding:clamp(1.3rem,3vw,2rem); border:1px solid rgba(226,232,240,.9); border-radius:24px; background:rgba(255,255,255,.94); box-shadow:0 12px 35px rgba(15,23,42,.06); }
        .intro { margin:0 0 1.3rem; color:#475569; line-height:1.75; }
        .filters { display:grid; grid-template-columns:minmax(0,1fr) 240px auto; gap:.8rem; margin-bottom:1.6rem; }
        .filters input, .filters select { width:100%; padding:.8rem 1rem; border:1px solid #dbeafe; border-radius:14px; color:#334155; background:#f8fbff; font:inherit; }
        .filters input:focus, .filters select:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.1); }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:.5rem; padding:.78rem 1.1rem; color:#fff; background:linear-gradient(135deg,var(--primary),var(--primary-dark)); border:0; border-radius:999px; box-shadow:0 8px 18px rgba(37,99,235,.2); font-size:.85rem; font-weight:700; text-decoration:none; cursor:pointer; }
        .btn.secondary { color:var(--primary-dark); background:#eff6ff; box-shadow:none; border:1px solid #bfdbfe; }
        .results-heading { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1rem; }
        .results-heading h2 { margin:0; font-size:1.15rem; }
        .count { padding:.35rem .7rem; color:#1e40af; background:#eff6ff; border:1px solid #bfdbfe; border-radius:999px; font-size:.75rem; font-weight:700; }
        .product-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:1rem; }
        .product-card { display:flex; flex-direction:column; padding:1.2rem; border:1px solid #e2e8f0; border-radius:20px; background:#fff; transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease; }
        .product-card:hover { transform:translateY(-4px); border-color:#bfdbfe; box-shadow:0 12px 28px rgba(37,99,235,.1); }
        .product-icon { display:flex; align-items:center; justify-content:center; width:54px; height:54px; margin-bottom:.8rem; color:#fff; background:linear-gradient(135deg,var(--primary),#1e3a8a); border-radius:17px; font-size:1.3rem; }
        .category { display:inline-block; width:fit-content; padding:.3rem .6rem; color:#1d4ed8; background:#eff6ff; border-radius:999px; font-size:.7rem; font-weight:700; }
        .product-card h3 { margin:.7rem 0 .4rem; font-size:1rem; }
        .product-card p { margin:0; color:var(--muted); font-size:.8rem; line-height:1.65; }
        .details { margin:1rem 0; padding-top:.8rem; border-top:1px solid #f1f5f9; color:#475569; font-size:.73rem; line-height:1.5; }
        .product-card .btn { margin-top:auto; }
        .notice { margin-top:1.5rem; padding:1rem; color:#1e40af; background:#eff6ff; border:1px solid #bfdbfe; border-radius:16px; font-size:.8rem; line-height:1.6; }
        .empty { padding:2rem 1rem; text-align:center; color:var(--muted); border:1px dashed #bfdbfe; border-radius:18px; background:#f8fbff; }
        @media (max-width:700px) { .page-wrap{padding-top:1.2rem}.filters{grid-template-columns:1fr}.results-heading{align-items:flex-start;flex-direction:column} }
        @media (max-width:480px) { .page-wrap{width:min(100% - 1rem,1160px)}.hero,.card{border-radius:20px}.hero{padding:1.4rem} }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="topbar">
            <a class="brand" href="main_dashboard.php">DOC.<span>lk</span></a>
            <a class="back-link" href="service_details.php?service=health-mart"><i class="fas fa-arrow-left"></i> Back to Health Mart</a>
        </div>
        <header class="hero">
            <div class="hero-content">
                <div class="hero-icon"><i class="fas fa-cart-shopping"></i></div>
                <h1>Browse Healthcare Products</h1>
                <p>Explore healthcare essentials, first-aid supplies, wellness products, medical accessories, and elderly care items through DOC.lk Health Mart.</p>
            </div>
        </header>
        <section class="card">
            <p class="intro">Find useful healthcare products in one convenient place. Use the search and category filters to explore available items. Product availability, delivery, and ordering options may vary.</p>
            <form class="filters" method="GET" action="health_products.php">
                <input type="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search healthcare products">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $selected_category === $category ? 'selected' : ''; ?>><?php echo htmlspecialchars($category); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn" type="submit"><i class="fas fa-search"></i> Search</button>
            </form>
            <div class="results-heading">
                <h2><i class="fas fa-box-open" style="color:var(--primary);margin-right:.4rem;"></i> Healthcare Products</h2>
                <span class="count"><?php echo count($visible_products); ?> product<?php echo count($visible_products) === 1 ? '' : 's'; ?> shown</span>
            </div>
            <?php if ($visible_products): ?>
                <div class="product-grid">
                    <?php foreach ($visible_products as $product): ?>
                        <article class="product-card">
                            <div class="product-icon"><i class="fas <?php echo htmlspecialchars($product['icon']); ?>"></i></div>
                            <span class="category"><?php echo htmlspecialchars($product['category']); ?></span>
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="details"><i class="fas fa-circle-info" style="color:var(--primary);margin-right:.3rem;"></i><?php echo htmlspecialchars($product['details']); ?></div>
                            <a href="contact_us.php" class="btn"><i class="fas fa-headset"></i> Ask About Product</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty"><i class="fas fa-box-open" style="font-size:2rem;color:var(--primary);"></i><p>No products matched your search. Try another keyword or category.</p><a href="health_products.php" class="btn secondary">View All Products</a></div>
            <?php endif; ?>
            <div class="notice"><i class="fas fa-triangle-exclamation"></i> Product information is provided for general guidance. Medicines and healthcare products should be used according to professional advice and applicable product instructions.</div>
        </section>
    </div>
</body>
</html>
