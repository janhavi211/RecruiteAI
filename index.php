<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RecruitAI – Resume Shortlisting System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --primary: #2563eb;
  --primary-dark: #1d4ed8;
  --secondary: #7c3aed;
  --text: #0f172a;
  --text-muted: #64748b;
  --border: #e2e8f0;
  --surface: #fff;
  --bg: #f8fafc;
  --font: 'Plus Jakarta Sans', system-ui, sans-serif;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: var(--font); color: var(--text); background: #fff; }

/* NAV */
nav {
  display: flex; align-items: center; justify-content: space-between;
  padding: 18px 80px;
  border-bottom: 1px solid var(--border);
  position: sticky; top: 0; background: rgba(255,255,255,.95);
  backdrop-filter: blur(10px); z-index: 100;
}
.nav-logo { font-size: 1.3rem; font-weight: 800; text-decoration: none; color: var(--text); }
.nav-logo span { color: var(--primary); }
.nav-links { display: flex; gap: 8px; }
.nav-links a {
  padding: 9px 18px; border-radius: 8px; font-size: .875rem; font-weight: 600;
  text-decoration: none; color: var(--text-muted); transition: all .2s;
}
.nav-links a:hover { background: var(--bg); color: var(--text); }
.nav-links .btn-nav {
  background: var(--primary); color: #fff; border-radius: 8px;
  padding: 9px 20px;
}
.nav-links .btn-nav:hover { background: var(--primary-dark); }

/* HERO */
.hero {
  padding: 100px 80px 80px;
  text-align: center;
  background: linear-gradient(180deg, #eff6ff 0%, #fff 100%);
}
.hero-tag {
  display: inline-flex; align-items: center; gap: 6px;
  background: #dbeafe; color: var(--primary);
  padding: 6px 14px; border-radius: 100px;
  font-size: .8rem; font-weight: 700;
  margin-bottom: 20px;
}
.hero h1 {
  font-size: 3.4rem; font-weight: 800; line-height: 1.15;
  max-width: 720px; margin: 0 auto 20px;
  letter-spacing: -1.5px;
}
.hero h1 span {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.hero p {
  font-size: 1.1rem; color: var(--text-muted); max-width: 560px;
  margin: 0 auto 36px; line-height: 1.7;
}
.hero-btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
.hero-btns a {
  padding: 14px 30px; border-radius: 10px;
  font-size: 1rem; font-weight: 700; text-decoration: none;
  transition: all .2s;
}
.btn-hero-primary { background: var(--primary); color: #fff; }
.btn-hero-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 10px 25px rgba(37,99,235,.3); }
.btn-hero-outline { background: #fff; color: var(--text); border: 2px solid var(--border); }
.btn-hero-outline:hover { border-color: var(--primary); color: var(--primary); }

/* STATS */
.stats {
  display: flex; justify-content: center; gap: 60px;
  padding: 40px 80px;
  background: var(--bg);
  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
}
.stat h3 { font-size: 2rem; font-weight: 800; color: var(--primary); }
.stat p { font-size: .875rem; color: var(--text-muted); margin-top: 4px; }

/* FEATURES */
.features { padding: 80px; }
.section-title { text-align: center; margin-bottom: 50px; }
.section-title h2 { font-size: 2.2rem; font-weight: 800; margin-bottom: 10px; letter-spacing: -.5px; }
.section-title p { color: var(--text-muted); font-size: 1rem; }
.features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; max-width: 1000px; margin: 0 auto; }
.feature-card {
  background: var(--bg); border: 1.5px solid var(--border);
  border-radius: 16px; padding: 28px 24px;
  transition: all .2s;
}
.feature-card:hover { border-color: var(--primary); transform: translateY(-3px); box-shadow: 0 10px 30px rgba(37,99,235,.1); }
.feature-icon {
  width: 50px; height: 50px;
  background: #dbeafe; border-radius: 12px;
  display: grid; place-items: center;
  font-size: 1.4rem; margin-bottom: 16px;
}
.feature-card h3 { font-size: 1rem; font-weight: 700; margin-bottom: 8px; }
.feature-card p { font-size: .875rem; color: var(--text-muted); line-height: 1.6; }

/* HOW IT WORKS */
.how-it-works {
  padding: 80px;
  background: linear-gradient(135deg, #1e3a8a, #2563eb, #7c3aed);
  color: #fff; text-align: center;
}
.how-it-works h2 { font-size: 2.2rem; font-weight: 800; margin-bottom: 14px; }
.how-it-works > p { opacity: .8; font-size: 1rem; margin-bottom: 50px; }
.steps { display: flex; justify-content: center; gap: 32px; flex-wrap: wrap; }
.step {
  background: rgba(255,255,255,.1); backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,.2);
  border-radius: 16px; padding: 28px 24px;
  width: 200px; text-align: center;
}
.step-num {
  width: 40px; height: 40px; background: rgba(255,255,255,.2);
  border-radius: 50%; display: grid; place-items: center;
  font-size: 1rem; font-weight: 800; margin: 0 auto 12px;
}
.step h4 { font-size: .9rem; font-weight: 700; margin-bottom: 6px; }
.step p { font-size: .78rem; opacity: .7; line-height: 1.5; }

/* CTA */
.cta {
  padding: 80px; text-align: center;
  background: var(--bg);
}
.cta h2 { font-size: 2rem; font-weight: 800; margin-bottom: 14px; }
.cta p { color: var(--text-muted); margin-bottom: 32px; font-size: 1rem; }
.cta-btns { display: flex; gap: 14px; justify-content: center; }
.cta-btns a {
  padding: 14px 30px; border-radius: 10px;
  font-size: 1rem; font-weight: 700; text-decoration: none;
  transition: all .2s;
}
.btn-cta-candidate { background: var(--primary); color: #fff; }
.btn-cta-hr { background: var(--secondary); color: #fff; }
.btn-cta-candidate:hover { background: var(--primary-dark); }
.btn-cta-hr:hover { background: #6d28d9; }

/* FOOTER */
footer {
  padding: 24px 80px;
  border-top: 1px solid var(--border);
  text-align: center;
  font-size: .82rem; color: var(--text-muted);
}

@media (max-width: 768px) {
  nav { padding: 14px 20px; }
  .hero { padding: 60px 20px 50px; }
  .hero h1 { font-size: 2rem; }
  .stats { flex-wrap: wrap; gap: 30px; padding: 30px 20px; }
  .features { padding: 50px 20px; }
  .features-grid { grid-template-columns: 1fr; }
  .how-it-works { padding: 50px 20px; }
  .cta { padding: 50px 20px; }
  .cta-btns { flex-direction: column; align-items: center; }
  footer { padding: 20px; }
}
</style>
</head>
<body>

<nav>
  <a href="index.php" class="nav-logo">Recruit<span>AI</span></a>
  <div class="nav-links">
    <a href="candidate/login.php">Candidate Login</a>
    <a href="hr/login.php">HR Login</a>
    <a href="candidate/register.php" class="btn-nav">Get Started</a>
  </div>
</nav>

<section class="hero">
  <div class="hero-tag">🤖 AI-Powered Recruitment</div>
  <h1>Smart Resume <span>Shortlisting</span> for Modern Hiring</h1>
  <p>Upload resumes, extract skills automatically with AI, and match candidates to jobs with precision. Save hours of manual screening.</p>
  <div class="hero-btns">
    <a href="candidate/register.php" class="btn-hero-primary">Find a Job →</a>
    <a href="hr/login.php" class="btn-hero-outline">I'm Hiring</a>
  </div>
</section>

<div class="stats">
  <div class="stat"><h3>500+</h3><p>Jobs Posted</p></div>
  <div class="stat"><h3>2K+</h3><p>Candidates</p></div>
  <div class="stat"><h3>85%</h3><p>Match Accuracy</p></div>
  <div class="stat"><h3>10x</h3><p>Faster Screening</p></div>
</div>

<section class="features">
  <div class="section-title">
    <h2>Everything You Need to Hire Smarter</h2>
    <p>A complete platform for candidates and recruiters</p>
  </div>
  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon">🧠</div>
      <h3>AI Skill Extraction</h3>
      <p>Python-powered NLP automatically extracts skills from any PDF or DOCX resume with high accuracy.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">📊</div>
      <h3>Match Percentage</h3>
      <p>Instantly compare candidate skills against job requirements and get a precise match score.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">⚡</div>
      <h3>Auto Shortlisting</h3>
      <p>Set a minimum match threshold and let the system shortlist candidates automatically.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">📅</div>
      <h3>Interview Scheduling</h3>
      <p>Schedule interviews directly and send automated email notifications to candidates.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">🔔</div>
      <h3>Real-time Updates</h3>
      <p>AJAX-powered live status updates. No page reloads — stay informed instantly.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">🔒</div>
      <h3>Secure Auth</h3>
      <p>Email OTP verification, bcrypt password hashing, and session management built-in.</p>
    </div>
  </div>
</section>

<section class="how-it-works">
  <h2>How It Works</h2>
  <p>Simple 4-step process to get matched with your dream job</p>
  <div class="steps">
    <div class="step">
      <div class="step-num">1</div>
      <h4>Register & Verify</h4>
      <p>Sign up with email and verify with OTP</p>
    </div>
    <div class="step">
      <div class="step-num">2</div>
      <h4>Upload Resume</h4>
      <p>AI extracts your skills automatically</p>
    </div>
    <div class="step">
      <div class="step-num">3</div>
      <h4>Browse & Apply</h4>
      <p>Find jobs and apply with one click</p>
    </div>
    <div class="step">
      <div class="step-num">4</div>
      <h4>Get Shortlisted</h4>
      <p>HR reviews match score and calls you</p>
    </div>
  </div>
</section>

<section class="cta">
  <h2>Ready to Get Started?</h2>
  <p>Join hundreds of candidates and companies already using RecruitAI</p>
  <div class="cta-btns">
    <a href="candidate/register.php" class="btn-cta-candidate">Register as Candidate</a>
    <a href="hr/login.php" class="btn-cta-hr">Post Jobs as HR</a>
  </div>
</section>

<footer>
  <p>© 2024 RecruitAI – Resume Shortlisting System | Built with PHP, MySQL, Python & ❤️</p>
</footer>

</body>
</html>
