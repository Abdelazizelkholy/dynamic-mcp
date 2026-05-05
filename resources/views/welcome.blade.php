<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VJNCODE — لتقنية المعلومات</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&family=Tajawal:wght@300;400;700;900&display=swap" rel="stylesheet">
<style>
  :root {
    --primary: #080c1a;
    --accent: #6b7fd4;
    --accent2: #2d3a8c;
    --accent3: #4f5fc4;
    --text: #e8eaf6;
    --muted: #8892b4;
    --card: #0e1428;
    --border: rgba(107,127,212,0.2);
    --glow: 0 0 40px rgba(107,127,212,0.18);
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }

  html { scroll-behavior: smooth; }

  body {
    font-family: 'Cairo', sans-serif;
    background: var(--primary);
    color: var(--text);
    overflow-x: hidden;
  }

  /* NAV */
  nav {
    position: fixed; top: 0; width: 100%; z-index: 999;
    padding: 18px 5%;
    display: flex; justify-content: space-between; align-items: center;
    background: rgba(10,15,30,0.85);
    backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--border);
  }

  .logo-img { height: 55px; width: auto; display: block; filter: brightness(1.1); }
  .logo span { font-size: 13px; display: block; font-weight: 300; color: var(--muted); -webkit-text-fill-color: var(--muted); }

  .nav-links { display: flex; gap: 36px; list-style: none; }
  .nav-links a {
    text-decoration: none; color: var(--muted);
    font-size: 15px; font-weight: 600;
    transition: color 0.3s;
    position: relative;
  }
  .nav-links a::after {
    content: ''; position: absolute; bottom: -4px; right: 0;
    width: 0; height: 2px;
    background: var(--accent);
    transition: width 0.3s;
  }
  .nav-links a:hover { color: var(--accent); }
  .nav-links a:hover::after { width: 100%; }

  .nav-cta {
    padding: 10px 24px;
    background: linear-gradient(135deg, #6b7fd422, #2d3a8c22);
    border: 1px solid var(--accent);
    border-radius: 8px; color: var(--accent) !important;
    font-weight: 700 !important;
    transition: background 0.3s !important;
  }
  .nav-cta:hover { background: linear-gradient(135deg, #6b7fd444, #2d3a8c44) !important; }

  /* HERO */
  .hero {
    min-height: 100vh;
    display: flex; align-items: center;
    padding: 120px 5% 80px;
    position: relative; overflow: hidden;
  }

  .hero-bg {
    position: absolute; inset: 0; z-index: 0;
    background:
      radial-gradient(ellipse 60% 50% at 70% 50%, rgba(45,58,140,0.2) 0%, transparent 70%),
      radial-gradient(ellipse 50% 60% at 20% 60%, rgba(107,127,212,0.1) 0%, transparent 70%);
  }

  .hero-grid {
    position: absolute; inset: 0; z-index: 0;
    background-image:
      linear-gradient(rgba(107,127,212,0.05) 1px, transparent 1px),
      linear-gradient(90deg, rgba(107,127,212,0.05) 1px, transparent 1px);
    background-size: 60px 60px;
  }

  .hero-content { position: relative; z-index: 1; max-width: 650px; }

  .hero-badge {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 18px;
    background: rgba(107,127,212,0.1);
    border: 1px solid rgba(107,127,212,0.35);
    border-radius: 100px;
    font-size: 13px; color: var(--accent);
    margin-bottom: 28px;
    font-weight: 600;
  }
  .hero-badge span { width: 8px; height: 8px; border-radius: 50%; background: var(--accent); animation: pulse 1.5s infinite; }
  @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }

  .hero h1 {
    font-family: 'Tajawal', sans-serif;
    font-size: clamp(38px, 5vw, 64px);
    font-weight: 900;
    line-height: 1.15;
    margin-bottom: 24px;
  }
  .hero h1 .grad {
    background: linear-gradient(135deg, #8b9fe8 0%, #4f5fc4 50%, #6b7fd4 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  }

  .hero p {
    font-size: 18px; color: var(--muted); line-height: 1.9;
    margin-bottom: 40px; max-width: 500px;
  }

  .hero-btns { display: flex; gap: 16px; flex-wrap: wrap; }

  .btn-primary {
    padding: 14px 36px;
    background: linear-gradient(135deg, #4f5fc4, #2d3a8c);
    border: none; border-radius: 10px;
    color: #fff; font-family: 'Cairo', sans-serif;
    font-size: 16px; font-weight: 700;
    cursor: pointer; text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
    display: inline-block;
  }
  .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 36px rgba(79,95,196,0.4); }

  .btn-outline {
    padding: 14px 36px;
    background: transparent;
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 10px;
    color: var(--text); font-family: 'Cairo', sans-serif;
    font-size: 16px; font-weight: 600;
    cursor: pointer; text-decoration: none;
    transition: border-color 0.3s, color 0.3s;
    display: inline-block;
  }
  .btn-outline:hover { border-color: var(--accent); color: var(--accent); }

  .hero-stats {
    display: flex; gap: 40px; margin-top: 56px; padding-top: 40px;
    border-top: 1px solid rgba(255,255,255,0.06);
  }
  .stat-num {
    font-family: 'Tajawal', sans-serif;
    font-size: 32px; font-weight: 900; color: #8b9fe8;
  }
  .stat-label { font-size: 13px; color: var(--muted); margin-top: 4px; }

  .hero-visual {
    position: absolute; left: 5%; top: 50%;
    transform: translateY(-50%);
    width: 420px; height: 420px;
    z-index: 1;
  }
  .hero-orb {
    width: 100%; height: 100%;
    border-radius: 50%;
    background: radial-gradient(circle at 40% 40%, rgba(124,58,237,0.25), rgba(0,200,255,0.1), transparent);
    border: 1px solid rgba(0,200,255,0.1);
    animation: float 6s ease-in-out infinite;
    display: flex; align-items: center; justify-content: center;
  }
  @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }

  /* SERVICES */
  section { padding: 100px 5%; }

  .section-label {
    font-size: 13px; font-weight: 700; letter-spacing: 3px;
    color: var(--accent); text-transform: uppercase;
    margin-bottom: 12px;
  }
  .section-title {
    font-family: 'Tajawal', sans-serif;
    font-size: clamp(28px, 3vw, 42px);
    font-weight: 900; margin-bottom: 16px;
  }
  .section-sub { color: var(--muted); font-size: 17px; max-width: 520px; line-height: 1.8; }

  .services-header { margin-bottom: 60px; }

  .services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
  }

  .service-card {
    background: var(--card);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 16px;
    padding: 36px 30px;
    transition: transform 0.3s, border-color 0.3s, box-shadow 0.3s;
    position: relative; overflow: hidden;
    cursor: default;
  }
  .service-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, transparent, var(--card-accent, #00c8ff), transparent);
    opacity: 0; transition: opacity 0.3s;
  }
  .service-card:hover { transform: translateY(-6px); border-color: rgba(0,200,255,0.2); box-shadow: var(--glow); }
  .service-card:hover::before { opacity: 1; }

  .service-icon {
    width: 56px; height: 56px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px;
    margin-bottom: 24px;
  }

  .service-card h3 {
    font-family: 'Tajawal', sans-serif;
    font-size: 20px; font-weight: 700;
    margin-bottom: 12px;
  }
  .service-card p { color: var(--muted); font-size: 15px; line-height: 1.8; }

  .service-tags {
    display: flex; flex-wrap: wrap; gap: 8px; margin-top: 20px;
  }
  .tag {
    font-size: 12px; padding: 4px 12px;
    border-radius: 100px;
    background: rgba(255,255,255,0.05);
    color: var(--muted);
    border: 1px solid rgba(255,255,255,0.08);
  }

  /* WHY US */
  .why-section { background: rgba(255,255,255,0.01); }

  .why-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 80px; align-items: center;
    margin-top: 60px;
  }

  .why-items { display: flex; flex-direction: column; gap: 28px; }
  .why-item {
    display: flex; gap: 20px; align-items: flex-start;
    padding: 24px;
    background: var(--card);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 14px;
    transition: border-color 0.3s;
  }
  .why-item:hover { border-color: rgba(0,200,255,0.2); }

  .why-icon {
    width: 44px; height: 44px; min-width: 44px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
  }

  .why-item h4 {
    font-size: 17px; font-weight: 700; margin-bottom: 6px;
    font-family: 'Tajawal', sans-serif;
  }
  .why-item p { color: var(--muted); font-size: 14px; line-height: 1.7; }

  .why-visual {
    display: flex; flex-direction: column; gap: 16px;
  }

  .metric-card {
    background: var(--card);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 14px; padding: 24px 28px;
    display: flex; justify-content: space-between; align-items: center;
  }
  .metric-title { font-size: 15px; color: var(--muted); margin-bottom: 8px; }
  .metric-value {
    font-family: 'Tajawal', sans-serif;
    font-size: 36px; font-weight: 900;
    background: linear-gradient(135deg, #6b7fd4, #2d3a8c);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  }
  .metric-badge {
    padding: 8px 16px; border-radius: 100px;
    font-size: 13px; font-weight: 700;
    background: rgba(16,185,129,0.1);
    color: #10b981;
    border: 1px solid rgba(16,185,129,0.2);
  }

  .progress-bar {
    height: 6px; border-radius: 100px;
    background: rgba(255,255,255,0.06);
    margin-top: 10px; overflow: hidden;
  }
  .progress-fill {
    height: 100%; border-radius: 100px;
    background: linear-gradient(90deg, #6b7fd4, #2d3a8c);
  }

  /* PROCESS */
  .process-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0;
    margin-top: 60px;
    position: relative;
  }
  .process-steps::before {
    content: '';
    position: absolute; top: 32px; right: 10%; left: 10%;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(107,127,212,0.4), transparent);
  }

  .step {
    text-align: center; padding: 0 20px;
    position: relative;
  }
  .step-num {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6b7fd422, #2d3a8c22);
    border: 1px solid rgba(107,127,212,0.35);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px;
    font-family: 'Tajawal', sans-serif;
    font-size: 22px; font-weight: 900;
    color: var(--accent);
    position: relative; z-index: 1;
  }
  .step h3 { font-size: 17px; font-weight: 700; margin-bottom: 10px; font-family: 'Tajawal', sans-serif; }
  .step p { color: var(--muted); font-size: 14px; line-height: 1.7; }

  /* TESTIMONIALS */
  .testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px; margin-top: 60px;
  }
  .testi-card {
    background: var(--card);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 16px; padding: 32px;
    transition: transform 0.3s;
  }
  .testi-card:hover { transform: translateY(-4px); }

  .testi-stars { color: #f59e0b; font-size: 18px; margin-bottom: 16px; letter-spacing: 2px; }
  .testi-text { color: var(--muted); font-size: 15px; line-height: 1.8; margin-bottom: 24px; font-style: italic; }
  .testi-author { display: flex; align-items: center; gap: 14px; }
  .testi-avatar {
    width: 44px; height: 44px; border-radius: 50%;
    background: linear-gradient(135deg, #6b7fd4, #2d3a8c);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; font-weight: 700; color: #fff;
    font-family: 'Tajawal', sans-serif;
  }
  .testi-name { font-weight: 700; font-size: 15px; }
  .testi-role { font-size: 13px; color: var(--muted); }

  /* CTA */
  .cta-section {
    margin: 0 5% 80px;
    background: linear-gradient(135deg, rgba(107,127,212,0.1), rgba(45,58,140,0.15));
    border: 1px solid rgba(0,200,255,0.15);
    border-radius: 24px; padding: 80px 60px;
    text-align: center;
    position: relative; overflow: hidden;
  }
  .cta-section::before {
    content: '';
    position: absolute; top: -50%; left: 50%; transform: translateX(-50%);
    width: 400px; height: 400px; border-radius: 50%;
    background: radial-gradient(circle, rgba(107,127,212,0.08), transparent 70%);
  }
  .cta-section h2 {
    font-family: 'Tajawal', sans-serif;
    font-size: clamp(28px, 3vw, 46px); font-weight: 900;
    margin-bottom: 16px; position: relative;
  }
  .cta-section p { color: var(--muted); font-size: 18px; margin-bottom: 40px; position: relative; }
  .cta-btns { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; position: relative; }

  /* CONTACT */
  .contact-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 60px; align-items: start;
    margin-top: 60px;
  }

  .contact-info { display: flex; flex-direction: column; gap: 24px; }
  .contact-item {
    display: flex; gap: 16px; align-items: flex-start;
    padding: 20px 24px;
    background: var(--card);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 12px;
  }
  .contact-icon {
    width: 40px; height: 40px; min-width: 40px; border-radius: 10px;
    background: rgba(0,200,255,0.1); border: 1px solid rgba(0,200,255,0.2);
    display: flex; align-items: center; justify-content: center; font-size: 18px;
  }
  .contact-item h4 { font-size: 14px; color: var(--muted); margin-bottom: 4px; }
  .contact-item p { font-size: 16px; font-weight: 600; }

  .contact-form {
    background: var(--card);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 20px; padding: 40px;
  }
  .form-group { margin-bottom: 20px; }
  .form-group label {
    display: block; font-size: 14px; font-weight: 600;
    margin-bottom: 8px; color: var(--muted);
  }
  .form-group input,
  .form-group select,
  .form-group textarea {
    width: 100%; padding: 14px 16px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px; color: var(--text);
    font-family: 'Cairo', sans-serif; font-size: 15px;
    outline: none; transition: border-color 0.3s;
    direction: rtl;
  }
  .form-group input:focus,
  .form-group select:focus,
  .form-group textarea:focus { border-color: var(--accent); }
  .form-group textarea { min-height: 120px; resize: vertical; }
  .form-group select option { background: var(--primary); }

  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

  /* FOOTER */
  footer {
    border-top: 1px solid rgba(255,255,255,0.06);
    padding: 60px 5% 30px;
  }
  .footer-grid {
    display: grid; grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 40px; margin-bottom: 50px;
  }
  .footer-brand .logo { font-size: 26px; }
  .footer-brand p { color: var(--muted); font-size: 14px; line-height: 1.8; margin-top: 16px; max-width: 280px; }

  .footer-col h4 {
    font-size: 15px; font-weight: 700; margin-bottom: 20px;
    color: var(--text);
    font-family: 'Tajawal', sans-serif;
  }
  .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 12px; }
  .footer-col ul a {
    text-decoration: none; color: var(--muted);
    font-size: 14px; transition: color 0.3s;
  }
  .footer-col ul a:hover { color: var(--accent); }

  .footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.06);
    padding-top: 24px;
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: 12px;
  }
  .footer-bottom p { color: var(--muted); font-size: 13px; }

  .social-links { display: flex; gap: 12px; }
  .social-link {
    width: 36px; height: 36px; border-radius: 8px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; text-decoration: none;
    transition: background 0.3s, border-color 0.3s;
  }
  .social-link:hover { background: rgba(107,127,212,0.12); border-color: rgba(107,127,212,0.35); }

  /* TECH STACK MARQUEE */
  .tech-section { padding: 40px 0; overflow: hidden; border-top: 1px solid rgba(255,255,255,0.04); border-bottom: 1px solid rgba(255,255,255,0.04); }
  .tech-marquee { display: flex; gap: 40px; animation: marquee 25s linear infinite; width: max-content; }
  @keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
  .tech-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 20px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 8px; white-space: nowrap;
    font-size: 14px; color: var(--muted);
    font-weight: 600;
  }
  .tech-item span { font-size: 20px; }

  /* FLOATING PARTICLES */
  .particles { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
  .particle {
    position: absolute; width: 2px; height: 2px;
    background: rgba(107,127,212,0.5); border-radius: 50%;
    animation: drift linear infinite;
  }
  @keyframes drift {
    0% { transform: translateY(100vh) translateX(0); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(-100px) translateX(20px); opacity: 0; }
  }

  @media (max-width: 768px) {
    .hero-visual { display: none; }
    .nav-links { display: none; }
    .why-grid, .contact-grid { grid-template-columns: 1fr; }
    .footer-grid { grid-template-columns: 1fr 1fr; }
    .form-row { grid-template-columns: 1fr; }
    .hero-stats { gap: 24px; flex-wrap: wrap; }
    .process-steps::before { display: none; }
    .cta-section { padding: 50px 30px; }
  }
</style>
</head>
<body>

<div class="particles" id="particles"></div>

<!-- NAV -->
<nav>
  <img src="vjncode-2.png" alt="VJNCODE" class="logo-img">
  <ul class="nav-links">
    <li><a href="#services">خدماتنا</a></li>
    <li><a href="#why">لماذا VJNCODE</a></li>
    <li><a href="#process">كيف نعمل</a></li>
    <li><a href="#testimonials">عملاؤنا</a></li>
    <li><a href="#contact" class="nav-cta">تواصل معنا</a></li>
  </ul>
</nav>

<!-- HERO -->
<section class="hero" id="home">
  <div class="hero-bg"></div>
  <div class="hero-grid"></div>

  <div class="hero-content">
    <div class="hero-badge">
      <span></span>
      شركة تقنية معتمدة — مصر 🇪🇬
    </div>

    <h1>
      نبني مستقبلك<br>
      <span class="grad">الرقمي معك</span>
    </h1>

    <p>
      VJNCODE لتقنية المعلومات — شريكك التقني في تطوير التطبيقات والمواقع وأنظمة الكاشير وERP وحلول الذكاء الاصطناعي. حلول متكاملة تُسرِّع نمو أعمالك.
    </p>

    <div class="hero-btns">
      <a href="#contact" class="btn-primary">ابدأ مشروعك الآن</a>
      <a href="#services" class="btn-outline">استكشف خدماتنا</a>
    </div>

    <div class="hero-stats">
      <div>
        <div class="stat-num">+200</div>
        <div class="stat-label">مشروع منجز</div>
      </div>
      <div>
        <div class="stat-num">+50</div>
        <div class="stat-label">عميل راضٍ</div>
      </div>
      <div>
        <div class="stat-num">7+</div>
        <div class="stat-label">سنوات خبرة</div>
      </div>
    </div>
  </div>
</section>

<!-- TECH MARQUEE -->
<div class="tech-section">
  <div class="tech-marquee" id="marquee">
    <div class="tech-item"><span>⚛️</span> React</div>
    <div class="tech-item"><span>🐍</span> Python</div>
    <div class="tech-item"><span>🤖</span> AI & ML</div>
    <div class="tech-item"><span>📱</span> Flutter</div>
    <div class="tech-item"><span>☁️</span> Cloud</div>
    <div class="tech-item"><span>🗄️</span> Node.js</div>
    <div class="tech-item"><span>🔧</span> Laravel</div>
    <div class="tech-item"><span>📊</span> Power BI</div>
    <div class="tech-item"><span>🛡️</span> Cybersecurity</div>
    <div class="tech-item"><span>💳</span> POS Systems</div>
    <div class="tech-item"><span>📦</span> ERP</div>
    <div class="tech-item"><span>🔗</span> API Integration</div>
    <div class="tech-item"><span>⚛️</span> React</div>
    <div class="tech-item"><span>🐍</span> Python</div>
    <div class="tech-item"><span>🤖</span> AI & ML</div>
    <div class="tech-item"><span>📱</span> Flutter</div>
    <div class="tech-item"><span>☁️</span> Cloud</div>
    <div class="tech-item"><span>🗄️</span> Node.js</div>
    <div class="tech-item"><span>🔧</span> Laravel</div>
    <div class="tech-item"><span>📊</span> Power BI</div>
    <div class="tech-item"><span>🛡️</span> Cybersecurity</div>
    <div class="tech-item"><span>💳</span> POS Systems</div>
    <div class="tech-item"><span>📦</span> ERP</div>
    <div class="tech-item"><span>🔗</span> API Integration</div>
  </div>
</div>

<!-- SERVICES -->
<section id="services">
  <div class="services-header">
    <div class="section-label">خدماتنا</div>
    <h2 class="section-title">حلول تقنية متكاملة</h2>
    <p class="section-sub">نقدم طيفاً واسعاً من الخدمات التقنية لتحويل أفكارك إلى منتجات رقمية احترافية</p>
  </div>

  <div class="services-grid">

    <div class="service-card" style="--card-accent: #6b7fd4;">
      <div class="service-icon" style="background: rgba(0,200,255,0.1); border: 1px solid rgba(0,200,255,0.2);">💻</div>
      <h3>تطوير المواقع الإلكترونية</h3>
      <p>مواقع احترافية بتصاميم عصرية وأداء عالٍ — من المواقع التعريفية إلى منصات التجارة الإلكترونية الكاملة.</p>
      <div class="service-tags">
        <span class="tag">React</span>
        <span class="tag">Vue.js</span>
        <span class="tag">Laravel</span>
        <span class="tag">WordPress</span>
      </div>
    </div>

    <div class="service-card" style="--card-accent: #4f5fc4;">
      <div class="service-icon" style="background: rgba(124,58,237,0.1); border: 1px solid rgba(124,58,237,0.2);">📱</div>
      <h3>تطوير تطبيقات الموبايل</h3>
      <p>تطبيقات iOS وAndroid بتجربة مستخدم استثنائية — بتقنية Flutter للحصول على أفضل أداء على كلا النظامين.</p>
      <div class="service-tags">
        <span class="tag">Flutter</span>
        <span class="tag">React Native</span>
        <span class="tag">iOS</span>
        <span class="tag">Android</span>
      </div>
    </div>

    <div class="service-card" style="--card-accent: #2d6a8c;">
      <div class="service-icon" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2);">🏪</div>
      <h3>أنظمة الكاشير (POS)</h3>
      <p>أنظمة نقاط بيع متكاملة للمطاعم والمحلات التجارية — مع إدارة المخزون والتقارير الفورية والدفع الإلكتروني.</p>
      <div class="service-tags">
        <span class="tag">نقاط البيع</span>
        <span class="tag">المخزون</span>
        <span class="tag">التقارير</span>
        <span class="tag">متعدد الفروع</span>
      </div>
    </div>

    <div class="service-card" style="--card-accent: #8b9fe8;">
      <div class="service-icon" style="background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.2);">📊</div>
      <h3>أنظمة ERP</h3>
      <p>حلول تخطيط موارد المؤسسة المتكاملة — لإدارة المحاسبة والمشتريات والمبيعات والموارد البشرية في منصة واحدة.</p>
      <div class="service-tags">
        <span class="tag">محاسبة</span>
        <span class="tag">HR</span>
        <span class="tag">مستودعات</span>
        <span class="tag">مشتريات</span>
      </div>
    </div>

    <div class="service-card" style="--card-accent: #7c8fd4;">
      <div class="service-icon" style="background: rgba(236,72,153,0.1); border: 1px solid rgba(236,72,153,0.2);">🤖</div>
      <h3>حلول الذكاء الاصطناعي</h3>
      <p>تطبيقات AI مخصصة — من chatbots ذكية إلى نماذج تحليل البيانات والتنبؤ ومعالجة اللغة الطبيعية بالعربية.</p>
      <div class="service-tags">
        <span class="tag">Chatbots</span>
        <span class="tag">NLP عربي</span>
        <span class="tag">Computer Vision</span>
        <span class="tag">ML</span>
      </div>
    </div>

    <div class="service-card" style="--card-accent: #3d5fc4;">
      <div class="service-icon" style="background: rgba(6,182,212,0.1); border: 1px solid rgba(6,182,212,0.2);">🔗</div>
      <h3>تكامل الأنظمة والـ API</h3>
      <p>ربط منظومتك التقنية بسلاسة — تكاملات مع بوابات الدفع وخدمات السحابة وأنظمة الطرف الثالث بأمان تام.</p>
      <div class="service-tags">
        <span class="tag">REST API</span>
        <span class="tag">GraphQL</span>
        <span class="tag">Webhooks</span>
        <span class="tag">Cloud</span>
      </div>
    </div>

  </div>
</section>

<!-- WHY US -->
<section id="why" class="why-section">
  <div class="section-label">لماذا فجن؟</div>
  <h2 class="section-title">الفرق يبدأ من هنا</h2>

  <div class="why-grid">
    <div class="why-items">

      <div class="why-item">
        <div class="why-icon" style="background:rgba(0,200,255,0.1);border:1px solid rgba(0,200,255,0.2);">🎯</div>
        <div>
          <h4>حلول مخصصة 100%</h4>
          <p>لا قوالب جاهزة — كل مشروع يُبنى من الصفر وفق احتياجاتك الفريدة وطبيعة أعمالك.</p>
        </div>
      </div>

      <div class="why-item">
        <div class="why-icon" style="background:rgba(124,58,237,0.1);border:1px solid rgba(124,58,237,0.2);">⚡</div>
        <div>
          <h4>تسليم في الموعد</h4>
          <p>نلتزم بالجداول الزمنية المتفق عليها بدقة عالية مع تحديثات مستمرة طوال مراحل التطوير.</p>
        </div>
      </div>

      <div class="why-item">
        <div class="why-icon" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);">🛡️</div>
        <div>
          <h4>جودة وأمان مضمونان</h4>
          <p>كود نظيف ومراجعات أمنية دورية وضمان ما بعد التسليم — نحن مسؤولون عن منتجك طويل الأمد.</p>
        </div>
      </div>

      <div class="why-item">
        <div class="why-icon" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);">🌍</div>
        <div>
          <h4>دعم فني على مدار الساعة</h4>
          <p>فريق دعم متاح دائماً للرد على استفساراتك وحل أي مشكلة فنية في أسرع وقت ممكن.</p>
        </div>
      </div>

    </div>

    <div class="why-visual">
      <div class="metric-card">
        <div>
          <div class="metric-title">معدل رضا العملاء</div>
          <div class="metric-value">98%</div>
          <div class="progress-bar"><div class="progress-fill" style="width:98%"></div></div>
        </div>
        <div class="metric-badge">ممتاز ↑</div>
      </div>

      <div class="metric-card">
        <div>
          <div class="metric-title">مشاريع مكتملة في موعدها</div>
          <div class="metric-value">95%</div>
          <div class="progress-bar"><div class="progress-fill" style="width:95%"></div></div>
        </div>
        <div class="metric-badge">+12% ↑</div>
      </div>

      <div class="metric-card" style="flex-direction: column; align-items: flex-start; gap: 16px;">
        <div style="width:100%">
          <div class="metric-title">المشاريع المنجزة لهذا العام</div>
          <div class="metric-value">47</div>
        </div>
        <div style="display:flex; gap:12px; width:100%">
          <div style="flex:1; text-align:center; padding:12px; background:rgba(0,200,255,0.06); border-radius:10px; border:1px solid rgba(0,200,255,0.1)">
            <div style="font-size:11px;color:var(--muted);margin-bottom:4px">مواقع</div>
            <div style="font-family:'Tajawal',sans-serif;font-size:20px;font-weight:900;color:#6b7fd4">18</div>
          </div>
          <div style="flex:1; text-align:center; padding:12px; background:rgba(124,58,237,0.06); border-radius:10px; border:1px solid rgba(124,58,237,0.1)">
            <div style="font-size:11px;color:var(--muted);margin-bottom:4px">تطبيقات</div>
            <div style="font-family:'Tajawal',sans-serif;font-size:20px;font-weight:900;color:#4f5fc4">15</div>
          </div>
          <div style="flex:1; text-align:center; padding:12px; background:rgba(16,185,129,0.06); border-radius:10px; border:1px solid rgba(16,185,129,0.1)">
            <div style="font-size:11px;color:var(--muted);margin-bottom:4px">ERP/POS</div>
            <div style="font-family:'Tajawal',sans-serif;font-size:20px;font-weight:900;color:#2d8c6a">14</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- PROCESS -->
<section id="process">
  <div class="section-label">كيف نعمل</div>
  <h2 class="section-title">من الفكرة إلى الإطلاق</h2>
  <p class="section-sub">منهجية عمل شفافة ومنظمة تضمن لك أفضل النتائج في كل مرحلة</p>

  <div class="process-steps">
    <div class="step">
      <div class="step-num">١</div>
      <h3>الاستشارة والتحليل</h3>
      <p>نفهم احتياجاتك ونحلل متطلباتك لنضع أساساً متيناً للمشروع</p>
    </div>
    <div class="step">
      <div class="step-num">٢</div>
      <h3>التصميم والنماذج</h3>
      <p>نصمم wireframes وprototypes ونعرضها عليك للموافقة قبل البدء بالكود</p>
    </div>
    <div class="step">
      <div class="step-num">٣</div>
      <h3>التطوير والبناء</h3>
      <p>فريق متخصص يبني مشروعك بأحدث التقنيات مع تحديثات أسبوعية</p>
    </div>
    <div class="step">
      <div class="step-num">٤</div>
      <h3>الاختبار والتسليم</h3>
      <p>اختبار شامل لضمان الجودة ثم تسليم المشروع مع تدريب فريقك</p>
    </div>
    <div class="step">
      <div class="step-num">٥</div>
      <h3>الدعم المستمر</h3>
      <p>نواصل معك بعد الإطلاق — تحديثات وصيانة ودعم فني لا ينتهي</p>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section id="testimonials">
  <div class="section-label">آراء عملائنا</div>
  <h2 class="section-title">شركاء نجاح يتحدثون</h2>

  <div class="testimonials-grid">
    <div class="testi-card">
      <div class="testi-stars">★★★★★</div>
      <p class="testi-text">"فريق فجن حوّل فكرتي إلى تطبيق احترافي في وقت قياسي. التواصل كان ممتازاً والنتيجة فاقت توقعاتي تماماً."</p>
      <div class="testi-author">
        <div class="testi-avatar">أح</div>
        <div>
          <div class="testi-name">أحمد حسين</div>
          <div class="testi-role">مؤسس تطبيق توصيل — القاهرة</div>
        </div>
      </div>
    </div>

    <div class="testi-card">
      <div class="testi-stars">★★★★★</div>
      <p class="testi-text">"نظام الكاشير الذي طوروه لسلسلة مطاعمنا غيّر طريقة عملنا كلياً. التقارير الآنية ساعدتنا في اتخاذ قرارات أفضل."</p>
      <div class="testi-author">
        <div class="testi-avatar">سم</div>
        <div>
          <div class="testi-name">سارة محمد</div>
          <div class="testi-role">مديرة سلسلة مطاعم — الإسكندرية</div>
        </div>
      </div>
    </div>

    <div class="testi-card">
      <div class="testi-stars">★★★★★</div>
      <p class="testi-text">"نظام ERP الذي بنوه لشركتنا وحّد كل إداراتنا في منصة واحدة. وفّرنا 40% من وقت الإدارة شهرياً."</p>
      <div class="testi-author">
        <div class="testi-avatar">ما</div>
        <div>
          <div class="testi-name">محمد العمري</div>
          <div class="testi-role">مدير عمليات — شركة استيراد وتصدير</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA SECTION -->
<div class="cta-section">
  <h2>مستعد لتحويل فكرتك إلى واقع؟</h2>
  <p>تحدث مع فريقنا اليوم واحصل على استشارة مجانية لمشروعك</p>
  <div class="cta-btns">
    <a href="#contact" class="btn-primary">احصل على استشارة مجانية</a>
    <a href="tel:+201065170831" class="btn-outline">📞 اتصل بنا الآن</a>
  </div>
</div>

<!-- CONTACT -->
<section id="contact">
  <div class="section-label">تواصل معنا</div>
  <h2 class="section-title">لنبدأ مشروعك معاً</h2>

  <div class="contact-grid">
    <div class="contact-info">
      <p style="color:var(--muted);font-size:16px;line-height:1.8;margin-bottom:8px">
        فريقنا جاهز للإجابة على أسئلتك وتقديم أفضل الحلول لاحتياجاتك التقنية. تواصل معنا الآن.
      </p>

      <div class="contact-item">
        <div class="contact-icon">📍</div>
        <div>
          <h4>الموقع</h4>
          <p>2 أبو العزائم، مصطفى النحاس، مدينة نصر، القاهرة</p>
        </div>
      </div>

      <div class="contact-item">
        <div class="contact-icon">📞</div>
        <div>
          <h4>الهاتف / واتساب</h4>
          <p dir="ltr">+201065170831</p>
        </div>
      </div>

      <div class="contact-item">
        <div class="contact-icon">✉️</div>
        <div>
          <h4>البريد الإلكتروني</h4>
          <p>info@vjncode.com</p>
        </div>
      </div>

      <div class="contact-item">
        <div class="contact-icon">🕐</div>
        <div>
          <h4>ساعات العمل</h4>
          <p>السبت – الخميس، 9 صباحاً – 6 مساءً</p>
        </div>
      </div>
    </div>

    <div class="contact-form">
      <h3 style="font-family:'Tajawal',sans-serif;font-size:22px;font-weight:700;margin-bottom:24px;">أرسل رسالتك</h3>

      <div class="form-row">
        <div class="form-group">
          <label>الاسم الكامل</label>
          <input type="text" placeholder="اسمك الكامل">
        </div>
        <div class="form-group">
          <label>رقم الهاتف</label>
          <input type="tel" placeholder="+20 xxx xxx xxxx" dir="ltr">
        </div>
      </div>

      <div class="form-group">
        <label>البريد الإلكتروني</label>
        <input type="email" placeholder="email@example.com" dir="ltr">
      </div>

      <div class="form-group">
        <label>الخدمة المطلوبة</label>
        <select>
          <option value="">اختر الخدمة...</option>
          <option>تطوير موقع إلكتروني</option>
          <option>تطوير تطبيق موبايل</option>
          <option>نظام كاشير (POS)</option>
          <option>نظام ERP</option>
          <option>حلول الذكاء الاصطناعي</option>
          <option>تكامل الأنظمة والـ API</option>
          <option>أخرى</option>
        </select>
      </div>

      <div class="form-group">
        <label>تفاصيل المشروع</label>
        <textarea placeholder="أخبرنا عن مشروعك، الميزانية التقريبية، والموعد المتوقع للتسليم..."></textarea>
      </div>

      <button class="btn-primary" style="width:100%;text-align:center;border:none;">إرسال الرسالة ✦</button>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-grid">
    <div class="footer-brand">
      <img src="vjncode-2.png" alt="VJNCODE" style="height:55px;width:auto;margin-bottom:16px;filter:brightness(1.1);">
      <p>شركة مصرية متخصصة في تقنية المعلومات — نحول الأفكار إلى منتجات رقمية احترافية تدفع نمو أعمالك للأمام.</p>
      <p style="margin-top:10px;font-size:13px;">📍 2 أبو العزائم، مصطفى النحاس، مدينة نصر، القاهرة</p>
      <div class="social-links" style="margin-top:20px;">
        <a href="https://vjncode.com" class="social-link" title="Website">🌐</a>
        <a href="https://wa.me/201065170831" class="social-link" title="WhatsApp">💬</a>
        <a href="#" class="social-link" title="Facebook">📘</a>
        <a href="#" class="social-link" title="Instagram">📸</a>
      </div>
    </div>

    <div class="footer-col">
      <h4>خدماتنا</h4>
      <ul>
        <li><a href="#">تطوير المواقع</a></li>
        <li><a href="#">تطبيقات الموبايل</a></li>
        <li><a href="#">أنظمة الكاشير</a></li>
        <li><a href="#">أنظمة ERP</a></li>
        <li><a href="#">الذكاء الاصطناعي</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>الشركة</h4>
      <ul>
        <li><a href="#">من نحن</a></li>
        <li><a href="#">فريقنا</a></li>
        <li><a href="#">أعمالنا</a></li>
        <li><a href="#">المدونة</a></li>
        <li><a href="#">الوظائف</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>الدعم</h4>
      <ul>
        <li><a href="#">تواصل معنا</a></li>
        <li><a href="#">الأسئلة الشائعة</a></li>
        <li><a href="#">سياسة الخصوصية</a></li>
        <li><a href="#">الشروط والأحكام</a></li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    <p>© 2025 VJNCODE لتقنية المعلومات — جميع الحقوق محفوظة</p>
    <p style="color:var(--muted)">صُنع بـ ❤️ في مصر 🇪🇬 | <a href="https://vjncode.com" style="color:var(--accent);text-decoration:none;">vjncode.com</a></p>
  </div>
</footer>

<script>
  // Particles
  const container = document.getElementById('particles');
  for (let i = 0; i < 20; i++) {
    const p = document.createElement('div');
    p.className = 'particle';
    p.style.cssText = `
      left: ${Math.random()*100}%;
      animation-duration: ${8 + Math.random()*12}s;
      animation-delay: ${Math.random()*10}s;
      opacity: ${0.3 + Math.random()*0.5};
      width: ${1 + Math.random()*3}px;
      height: ${1 + Math.random()*3}px;
    `;
    container.appendChild(p);
  }

  // Nav scroll effect
  window.addEventListener('scroll', () => {
    const nav = document.querySelector('nav');
    nav.style.background = window.scrollY > 50
      ? 'rgba(10,15,30,0.95)'
      : 'rgba(10,15,30,0.85)';
  });

  // Smooth form feedback
  document.querySelector('.contact-form button').addEventListener('click', function() {
    this.textContent = 'تم الإرسال بنجاح ✓';
    this.style.background = 'linear-gradient(135deg, #10b981, #059669)';
    setTimeout(() => {
      this.textContent = 'إرسال الرسالة ✦';
      this.style.background = '';
    }, 3000);
  });

  // Intersection Observer for animations
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.style.opacity = '1';
        e.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.service-card, .why-item, .testi-card, .step').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(24px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
  });
</script>
</body>
</html>
