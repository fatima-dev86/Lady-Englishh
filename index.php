<?php
require __DIR__ . '/lib.php';
$S = settings();
[$rate, $live] = usd_try();
$sent = isset($_GET['sent']) ? (string)$_GET['sent'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Lady English – Learn English with Confidence</title>
<meta name="description" content="Simple, practical English lessons: vocabulary, grammar, speaking and everyday English, with Arabic support.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --beige:#F2E6D0; --beige-deep:#E8D6B6; --white:#FFFDF8; --yellow:#F5C431; --yellow-deep:#D9A410;
  --ink:#3A2E1E; --muted:#6E5E47; --line:#DCC9A5;
  --bg:var(--beige); --surface:var(--white); --text:var(--ink); --soft:var(--muted); --rule:var(--line);
  --btn-text:#3A2E1E;
  --serif:"DM Serif Display",Georgia,"Times New Roman",serif;
  --sans:"Nunito",system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;
  box-sizing:border-box; padding-top:env(safe-area-inset-top,0px); padding-bottom:env(safe-area-inset-bottom,0px);
}
@media (prefers-color-scheme:dark){
  :root:not([data-theme="light"]){--bg:#2A2218; --surface:#362C1F; --text:#F6EBD5; --soft:#CDBB98; --rule:#4C3E2B;}
}
:root[data-theme="dark"]{--bg:#2A2218; --surface:#362C1F; --text:#F6EBD5; --soft:#CDBB98; --rule:#4C3E2B;}
*,*::before,*::after{box-sizing:inherit}
html{scroll-behavior:smooth;scroll-padding-top:calc(env(safe-area-inset-top,0px) + 72px)}
body{margin:0;background:var(--bg);color:var(--text);font-family:var(--sans);font-size:1.0625rem;line-height:1.7}
body::before{content:"";position:fixed;inset:0 0 auto;height:env(safe-area-inset-top,0px);background:var(--bg);z-index:30}
a{color:inherit}
:focus-visible{outline:3px solid var(--yellow-deep);outline-offset:3px;border-radius:6px}
.wrap{max-width:1040px;margin:0 auto;padding:0 22px}
h1,h2,h3{font-family:var(--serif);font-weight:400;line-height:1.15;margin:0}
h2{font-size:clamp(1.8rem,4vw,2.5rem);margin-bottom:.6rem}
p{margin:0 0 1rem;max-width:62ch}

header{position:sticky;top:env(safe-area-inset-top,0px);z-index:20;background:var(--bg);border-bottom:1px solid var(--rule)}
.nav{display:flex;align-items:center;justify-content:space-between;gap:16px;height:64px}
.brand{font-family:var(--serif);font-size:1.5rem;text-decoration:none;display:flex;align-items:center;gap:10px}
.dot{width:14px;height:14px;border-radius:50%;background:var(--yellow);box-shadow:0 0 0 4px var(--beige-deep)}
nav ul{display:flex;gap:6px;list-style:none;margin:0;padding:0}
nav a{display:block;padding:8px 14px;border-radius:999px;text-decoration:none;font-weight:600;font-size:.97rem}
nav a:hover{background:var(--yellow);color:var(--btn-text)}
@media (max-width:760px){nav ul{overflow-x:auto;max-width:62vw}nav a{white-space:nowrap;padding:8px 11px}.brand span.t{display:none}}

.hero{padding:72px 0 80px;position:relative;overflow:hidden}
.hero .grid{display:grid;grid-template-columns:1.15fr .85fr;gap:48px;align-items:center}
@media (max-width:820px){.hero .grid{gap:22px}.hero{padding:44px 0 56px}.hero h1{font-size:clamp(2rem,6.6vw,3rem)}.hero p.lead{font-size:1.05rem}.hero p{font-size:1rem}}@media (max-width:520px){.hero .grid{grid-template-columns:1fr}}
h1{font-size:clamp(2.6rem,7vw,4.6rem);letter-spacing:-.01em;margin-bottom:1.1rem}
.hero p.lead{font-size:1.2rem;color:var(--soft)}
.btns{display:flex;flex-wrap:wrap;gap:12px;margin-top:1.6rem}
.btn{display:inline-block;padding:14px 26px;border-radius:999px;background:var(--yellow);color:var(--btn-text);font-weight:700;text-decoration:none;transition:transform .15s,background .15s}
.btn:hover{background:var(--yellow-deep);transform:translateY(-2px)}
.btn.alt{background:var(--surface);border:2px solid var(--yellow)}
.btn.alt:hover{background:var(--yellow)}

.card-demo{background:var(--surface);border-radius:28px;padding:30px;border:1px solid var(--rule);box-shadow:14px 14px 0 var(--yellow);transform:rotate(-1.5deg)}
.card-demo small{color:var(--soft);font-weight:700}
.card-demo .en{font-family:var(--serif);font-size:1.7rem;margin:.4rem 0 .8rem;line-height:1.25}
.card-demo .ar{font-size:1.25rem;direction:rtl;text-align:right;border-top:1px dashed var(--rule);padding-top:.9rem;margin:0}
@media (max-width:820px){.card-demo{box-shadow:10px 10px 0 var(--yellow)}}

section{padding:72px 0}
.band{background:var(--surface);border-block:1px solid var(--rule)}
.topics{display:grid;grid-template-columns:repeat(auto-fit,minmax(290px,1fr));gap:20px;margin-top:2rem}
.topic{background:var(--surface);border:1px solid var(--rule);border-radius:20px;padding:26px}
.band .topic{background:var(--bg)}
.topic h3{font-size:1.4rem;margin-bottom:.4rem}
.topic p{margin:0;color:var(--soft)}
.ico{width:44px;height:44px;border-radius:14px;background:var(--yellow);display:grid;place-items:center;font-size:1.3rem;margin-bottom:14px;color:var(--btn-text)}
.two{display:grid;grid-template-columns:1fr 1fr;gap:48px}
@media (max-width:820px){.two{grid-template-columns:1fr;gap:24px}section{padding:52px 0}}
.quote{font-family:var(--serif);font-size:clamp(1.4rem,3vw,1.9rem);line-height:1.35;border-left:6px solid var(--yellow);padding-left:20px}

.contact{text-align:center}
.contact p{margin-inline:auto}
.mail{font-family:var(--serif);font-size:clamp(1.2rem,4.5vw,2rem);word-break:break-all;text-decoration:none;border-bottom:4px solid var(--yellow)}
.mail:hover{background:var(--yellow);color:var(--btn-text)}

.policy{max-width:760px}
details{background:var(--surface);border:1px solid var(--rule);border-radius:16px;margin-bottom:10px;padding:0 20px}
summary{cursor:pointer;font-weight:700;padding:16px 0;list-style:none;display:flex;justify-content:space-between;gap:12px}
summary::-webkit-details-marker{display:none}
summary::after{content:"+";font-size:1.4rem;line-height:1;color:var(--yellow-deep)}
details[open] summary::after{content:"–"}
details p,details li{color:var(--soft);font-size:1rem}
details ul{margin:0 0 1rem;padding-left:1.2rem}

footer{background:var(--yellow);color:#3A2E1E;padding:36px 0 44px;text-align:center}
footer .brand{justify-content:center;margin-bottom:6px}
footer p{margin:.2rem auto;font-size:.95rem}
footer a{font-weight:700}

.hero .grid>*{animation:in .8s ease both}
.hero .grid>*:nth-child(2){animation-delay:.15s}
@keyframes in{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
@media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important;scroll-behavior:auto!important}}
</style>
<style>
:root{--logo:url("images/logo.jpg");--face:url("images/logo-circle.jpg")}
.portrait{position:relative;max-width:470px;margin-inline:auto}
.portrait img{display:block;width:100%;height:auto;-webkit-mask-image:linear-gradient(to bottom,#000 80%,transparent 100%);mask-image:linear-gradient(to bottom,#000 80%,transparent 100%)}
.portrait .card-demo{position:absolute;left:-9%;bottom:7%;width:74%;padding:16px 20px;border-radius:20px;transform:rotate(-2deg);box-shadow:7px 7px 0 var(--yellow)}
.portrait .card-demo .en{font-size:1.2rem;margin:.2rem 0 .5rem}
.portrait .card-demo .ar{font-size:1.05rem;padding-top:.6rem}
@media (max-width:820px){.portrait{max-width:380px}.portrait .card-demo{left:0;width:82%;bottom:3%}}
.av{display:inline-block;width:44px;height:44px;border-radius:50%;background:var(--face) center/cover;box-shadow:0 0 0 3px var(--yellow);flex:none}
.logo-badge{width:min(240px,64%);aspect-ratio:1;border-radius:34px;background:var(--logo) center/cover;border:5px solid var(--surface);box-shadow:10px 10px 0 var(--yellow);margin:0 0 1.8rem;transform:rotate(-2deg)}
.contact .logo-badge{margin:0 auto 2rem}
footer .av{width:60px;height:60px;box-shadow:0 0 0 4px rgba(255,253,248,.7)}
footer .brand{flex-direction:column;gap:10px;margin-bottom:10px}
.pill{display:inline-block;background:var(--yellow);color:var(--btn-text);font-weight:700;font-size:.85rem;padding:3px 12px;border-radius:999px;margin-bottom:10px}
.topic .ar{direction:rtl;text-align:right;margin-top:.8rem;padding-top:.8rem;border-top:1px dashed var(--rule)}
button.btn{border:0;cursor:pointer;font:inherit;font-weight:700}
button.btn.alt{border:2px solid var(--yellow)}
button.btn:disabled{opacity:.5;cursor:default;transform:none}
.admin,.cform{background:var(--surface);border:2px dashed var(--yellow);border-radius:22px;padding:24px;margin-top:2rem}
.cform{max-width:520px;margin-inline:auto;text-align:left;border-style:solid;border-width:1px;border-color:var(--rule)}
.admin input,.admin select,.admin textarea,.cform input,.cform textarea{display:block;width:100%;padding:12px 14px;margin:0 0 10px;border:1px solid var(--rule);border-radius:12px;background:var(--bg);color:var(--text);font:inherit}
.arow{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:10px 0;border-top:1px solid var(--rule)}
button.sm{margin-left:6px;padding:6px 12px;border-radius:999px;border:1px solid var(--rule);background:var(--bg);color:var(--text);font:inherit;cursor:pointer}
.offer{display:inline-block;background:var(--yellow);color:var(--btn-text);font-weight:700;padding:8px 18px;border-radius:999px;margin:.4rem 0 0}
.course{position:relative;display:flex;flex-direction:column;gap:6px}
.course h3{font-size:1.7rem;padding-right:84px}
.course .off{position:absolute;top:18px;right:18px;background:var(--yellow);color:var(--btn-text);font-weight:700;font-size:.8rem;padding:4px 10px;border-radius:999px}
.course .meta{list-style:none;margin:0 0 6px;padding:0;color:var(--soft)}
.course .price{margin:6px 0 14px}
.course .old{text-decoration:line-through;color:var(--soft);font-size:1.1rem}
.course .new{font-family:var(--serif);font-size:2.4rem;line-height:1}
.course .price small{color:var(--soft)}
.course .btn{margin-top:auto;text-align:center}
.tr{position:fixed;right:18px;bottom:calc(env(safe-area-inset-bottom,0px) + 18px);z-index:40;display:flex;flex-direction:column;align-items:flex-end;gap:10px}
.tr-panel{background:var(--surface);color:var(--text);border:1px solid var(--rule);border-radius:18px;padding:16px;box-shadow:0 10px 30px rgba(58,46,30,.25);max-width:min(300px,86vw)}
.tr-panel p{margin:0 0 10px;font-size:.95rem}
.tr-btn{box-shadow:0 8px 22px rgba(58,46,30,.28)}
.goog-te-banner-frame{display:none!important}
body{top:0!important}
.course .try{margin:-8px 0 14px;font-weight:700;color:var(--soft)}
.try-note{margin-top:1.2rem;color:var(--soft);font-size:.92rem}
.offer[hidden]{display:none}
.hp{position:absolute;left:-9999px;width:1px;height:1px;opacity:0}
.cnote{margin:0 0 12px;padding:10px 14px;border-radius:12px;font-weight:700}
.cnote.ok{background:var(--yellow);color:var(--btn-text)}
.cnote.bad{background:var(--bg);border:2px solid var(--yellow-deep)}
.admin-btn{display:inline-block;margin-top:8px;padding:8px 22px;border-radius:999px;background:#FFFDF8;color:#3A2E1E;font-weight:700;text-decoration:none;font-size:.9rem}
.admin-btn:hover{background:#3A2E1E;color:#FFFDF8}
</style>
</head>
<body>
<header>
  <div class="wrap nav">
    <a class="brand" href="#home"><span class="av" role="img" aria-label="Lady English logo"></span><span class="t">Lady English</span></a>
    <nav aria-label="Main"><ul>
      <li><a href="#learn">Learn</a></li>
      <li><a href="#courses">Courses</a></li>
      <li><a href="#kids">English for Kids</a></li>
      <li><a href="#about">About</a></li>
      <li><a href="#contact">Contact</a></li>
      <li><a href="#privacy">Privacy</a></li>
    </ul></nav>
  </div>
</header>

<main>
<section class="hero" id="home" style="padding-bottom:80px">
  <div class="wrap grid">
    <div>
      <h1>Learn English with confidence.</h1>
      <p class="lead">Welcome to Lady English, your friendly place to learn English. Whether you are a beginner or an advanced learner, you will find simple, practical and enjoyable lessons here.</p>
      <p>Explore vocabulary, grammar, speaking practice, everyday expressions, and English lessons through movies and podcasts.</p>
      <div class="btns">
        <a class="btn" href="#learn">See what you can learn</a>
        <a class="btn alt" href="#contact">Contact us</a>
      </div>
    </div>
    <div class="portrait"><img src="images/lady-photo.webp" alt="Portrait of Lady English in her yellow Lady English T-shirt" width="683" height="768"></div>
  </div>
</section>

<section class="band" id="learn">
  <div class="wrap">
    <h2>What you can learn here</h2>
    <p>Educational content to help you build your skills and communicate more naturally in everyday situations.</p>
    <div class="topics">
      <div class="topic"><div class="ico" aria-hidden="true">Aa</div><h3>English Vocabulary</h3><p>Useful words, expressions and phrases with clear explanations and examples.</p></div>
      <div class="topic"><div class="ico" aria-hidden="true">✎</div><h3>English Grammar</h3><p>Grammar rules explained simply, with practical examples.</p></div>
      <div class="topic"><div class="ico" aria-hidden="true">☕</div><h3>Everyday English</h3><p>Improve your ability to communicate in common daily situations.</p></div>
      <div class="topic"><div class="ico" aria-hidden="true">☺</div><h3>Speaking and Conversation</h3><p>Practice useful expressions and build your speaking confidence.</p></div>
      <div class="topic"><div class="ico" aria-hidden="true">❦</div><h3>Reading and Comprehension</h3><p>Understand written English better through educational texts and activities.</p></div>
      <div class="topic"><div class="ico" aria-hidden="true">✓</div><h3>Exercises and Practice</h3><p>Test your knowledge and reinforce what you have learned.</p></div>
    </div>
    <p style="margin-top:1.6rem;color:var(--soft)">Some materials include Arabic translations to help Arabic-speaking learners understand English more easily.</p>
  </div>
</section>

<section id="courses">
  <div class="wrap">
    <h2>Our courses</h2>
    <p>Live lessons with a teacher. Every course is 3 months and 36 live lessons per level.</p>
    <?php if ($S['offerActive']): ?><p class="offer">Special offer: <?= (int)$S['offerPercent'] ?>% off every course</p><?php endif; ?>
    <div class="topics">
    <?php foreach (courses() as [$name, $base]):
      $now = price_now($base, $S);
      $tl  = (int)(round($now * $rate / 10) * 10); ?>
      <article class="topic course">
        <?php if ($S['offerActive']): ?><span class="off"><?= (int)$S['offerPercent'] ?>% OFF</span><?php endif; ?>
        <h3><?= h($name) ?></h3>
        <ul class="meta"><li>3 months per level</li><li>36 live lessons per level</li></ul>
        <p class="price"><?php if ($S['offerActive']): ?><span class="old">$<?= $base ?></span> <?php endif; ?><span class="new">$<?= $now ?></span> <small>per level</small></p>
        <p class="try">≈ ₺<?= number_format($tl, 0, ',', '.') ?> (Turkish lira)</p>
        <a class="btn" href="#contact">Ask about this course</a>
      </article>
    <?php endforeach; ?>
    </div>
    <p class="try-note">Turkish lira prices are estimates<?= $live ? ' based on today’s exchange rate' : '' ?> and may change with the exchange rate.</p>
  </div>
</section>
<section class="band" id="kids">
  <div class="wrap">
    <span class="pill">For young learners · Ages 5–12</span>

    <h2>Make Learning English Fun, Easy and Exciting!</h2>

    <p>
      Give your child a positive and enjoyable English-learning experience
      with private, personalized one-to-one lessons. Each lesson is adapted
      to your child's age, level, learning style and individual needs.
    </p>

    <div class="topics">
      <div class="topic">
        <div class="ico">💬</div>
        <h3>Speaking</h3>
        <p>Help children express themselves and communicate with confidence.</p>
      </div>

      <div class="topic">
        <div class="ico">Aa</div>
        <h3>Vocabulary</h3>
        <p>Learn useful words and expressions through fun activities.</p>
      </div>

      <div class="topic">
        <div class="ico">✏️</div>
        <h3>Grammar</h3>
        <p>Understand basic grammar through simple explanations and examples.</p>
      </div>

      <div class="topic">
        <div class="ico">🔤</div>
        <h3>Pronunciation</h3>
        <p>Practice sounds and words to develop clearer pronunciation.</p>
      </div>

      <div class="topic">
        <div class="ico">📖</div>
        <h3>Reading</h3>
        <p>Build reading skills with age-appropriate texts and activities.</p>
      </div>

      <div class="topic">
        <div class="ico">🎧</div>
        <h3>Listening</h3>
        <p>Improve understanding through listening games and exercises.</p>
      </div>
    </div>

    <h2 style="margin-top:2.5rem">Why Choose Lady English?</h2>

    <div class="topics">
      <div class="topic">
        <h3>Personalized One-to-One Lessons</h3>
        <p>Every child receives individual attention and lessons adapted to their needs.</p>
      </div>

      <div class="topic">
        <h3>Fun Learning Materials</h3>
        <p>Age-appropriate materials, games and flashcards make learning enjoyable.</p>
      </div>

      <div class="topic">
        <h3>Supportive Teaching</h3>
        <p>A friendly learning environment where children can learn at their own pace.</p>
      </div>

      <div class="topic">
        <h3>Progress and Feedback</h3>
        <p>Parents receive feedback about learning progress and goals.</p>
      </div>
    </div>

    <p style="margin-top:2rem">
      These lessons are suitable for beginners, children who want to improve
      their school performance, and young learners who want to communicate
      more confidently in English.
    </p>

    <p class="quote" style="margin-top:1.5rem">
      Give your child a great start in English with Lady English!
      Book a private lesson and let the learning adventure begin!
    </p>

    <div class="btns">
      <a class="btn" href="#contact">Book a Private Lesson</a>
    </div>
  </div>
</section>

<section id="about">
  <div class="wrap two">
    <div>
      <div class="logo-badge" role="img" aria-label="Lady English logo"></div>
      <h2>Who is Lady English for?</h2>
      <p>Students, adults, self-learners, and anyone who wants better English for education, work, travel or everyday communication.</p>
      <p>Whether you are learning your first English words or expanding your vocabulary and improving your grammar, our aim is to make your learning journey easier.</p>
    </div>
    <div>
      <h2>Our mission</h2>
      <p class="quote">Practical English skills through clear explanations, useful examples and engaging lessons.</p>
      <p style="margin-top:1.2rem">Learning a language takes practice, patience and consistency. Lady English is here to support you as you take each step toward speaking English with greater confidence.</p>
    </div>
  </div>
</section>

<section class="band contact" id="contact">
  <div class="wrap">
    <div class="logo-badge" role="img" aria-label="Lady English logo"></div>
    <h2>Contact Lady English</h2>
    <p>We welcome questions, feedback and suggestions. Ask about an article, suggest a topic for a future lesson, or tell us what you need help understanding.</p>
    <p style="margin-top:1.4rem"><a class="mail" href="mailto:ladyenglishcourse@gmail.com?subject=Question%20for%20Lady%20English">ladyenglishcourse@gmail.com</a></p>
    <p style="margin-top:1.2rem;color:var(--soft)">Please include a clear subject line so we can understand your message.</p>
    <form class="cform" method="post" action="contact.php">
      <?php if ($sent === '1'): ?><p class="cnote ok" role="status">Thank you! Your message was sent.</p>
      <?php elseif ($sent === '2'): ?><p class="cnote bad" role="alert">Please write your name and your message, then try again.</p>
      <?php elseif ($sent === '3'): ?><p class="cnote bad" role="alert">Please wait a few seconds before sending another message.</p>
      <?php elseif ($sent === '0'): ?><p class="cnote bad" role="alert">Sorry, the message could not be sent. Please email ladyenglishcourse@gmail.com.</p><?php endif; ?>
      <input name="name" placeholder="Your name" aria-label="Your name" autocomplete="name" required maxlength="100">
      <input name="email" type="email" placeholder="Your email (optional)" aria-label="Your email" autocomplete="email" maxlength="150">
      <textarea name="message" rows="5" placeholder="Your question or suggestion" aria-label="Your message" required maxlength="3000"></textarea>
      <input class="hp" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
      <button class="btn" type="submit">Send message</button>
    </form>
  </div>
</section>

<section id="privacy">
  <div class="wrap policy">
    <h2>Privacy Policy</h2>

    <details><summary>1. Information we collect</summary>
      <p>When you visit Lady English, certain information may be collected automatically by the website platform or third-party services. This may include your browser type, device information, pages visited, referring website, and general usage information.</p>
      <p>If you contact us by email, we may receive your email address and any information you choose to include in your message.</p></details>

    <details><summary>2. How we use information</summary>
      <ul><li>Maintain and improve our website.</li><li>Understand which educational articles and resources are useful to visitors.</li><li>Respond to questions, comments and requests.</li><li>Monitor website performance and help protect the website from misuse.</li></ul>
      <p>We do not ask visitors to provide personal information simply to read our educational articles.</p></details>

    <details><summary>3. Cookies and similar technologies</summary>
      <p>Lady English and third-party services may use cookies or similar technologies to support website functionality, understand visitor activity, measure performance, and, where applicable, display advertisements. You can manage or disable cookies through your browser settings. Some features may not work as intended if certain cookies are disabled.</p></details>

    <details><summary>4. Google AdSense and advertising</summary>
      <p>Lady English may use Google AdSense or other advertising services to display advertisements. Third-party vendors, including Google, may use cookies or similar technologies to serve ads based on a user’s previous visits to this website or other websites, where permitted by applicable law and user preferences. Google’s use of advertising cookies enables Google and its partners to serve advertisements.</p>
      <p>You can review Google’s advertising policies and settings at <a href="https://policies.google.com/technologies/ads" target="_blank" rel="noopener">policies.google.com/technologies/ads</a>. If personalized advertising is available in your region, you may be able to manage your preferences through Google’s settings.</p></details>

    <details><summary>5. Third-party services and links</summary>
      <p>Our website may use third-party services, including Blogger, Google Analytics, or advertising providers, depending on the features enabled. We may also link to external websites or educational resources. These sites operate under their own privacy policies, and we are not responsible for their independent practices. We encourage you to review the policies of any external services you use.</p></details>

    <details><summary>6. Data retention and security</summary>
      <p>Information submitted through email may be retained for as long as necessary to respond to your inquiry or maintain relevant records. We take reasonable steps to protect information under our control. However, no method of internet transmission or electronic storage can be guaranteed to be completely secure.</p></details>

    <details><summary>7. Children’s privacy</summary>
      <p>Lady English provides educational content that may be useful to younger learners. Our website offers learning resources without requiring children to submit personal information to read articles. Parents and guardians should supervise children’s online activities and contact us if they have concerns about information submitted through our website.</p></details>

    <details><summary>8. Your privacy rights</summary>
      <p>Depending on your location and applicable law, you may have rights to access, correct, delete, or restrict the processing of your personal information. To ask a question about privacy or exercise an applicable right, email <a href="mailto:ladyenglishcourse@gmail.com">ladyenglishcourse@gmail.com</a>.</p></details>

    <details><summary>9. Changes to this policy</summary>
      <p>We may update this Privacy Policy when our website, services or legal requirements change. Any updates will be published on this page with a revised date.</p></details>

    <details><summary>10. Contact us</summary>
      <p>Questions about this policy? Write to <a href="mailto:ladyenglishcourse@gmail.com">ladyenglishcourse@gmail.com</a>.</p></details>
  </div>
</section>
</main>

<footer>
  <div class="wrap">
    <div class="brand"><span class="av" role="img" aria-label="Lady English logo"></span>Lady English</div>
    <p>Learn English with Confidence.</p>
    <p><a href="#contact">Contact</a> · <a href="#privacy">Privacy Policy</a></p>
    <p><a href="admin.php" style="display:inline-block;margin-top:8px;padding:8px 22px;border-radius:999px;background:#FFFDF8;color:#3A2E1E;text-decoration:none;font-weight:700">Admin</a></p>
  
    <p>Thank you for visiting and being part of our learning community.</p>
  </div>
</footer>
<div class="tr">
  <div class="tr-panel" id="trPanel" hidden>
    <p id="trHelp">Choose your language and this page will be translated.</p>
    <div id="google_translate_element"></div>
  </div>
  <button class="btn tr-btn" id="trBtn" type="button" aria-expanded="false" aria-controls="trPanel">🌐 Translate this page</button>
</div>
<script>
(function(){
var el=function(i){return document.getElementById(i)};
window.googleTranslateElementInit=function(){
 try{new google.translate.TranslateElement({pageLanguage:'en',autoDisplay:false,includedLanguages:'ar,fr,es,tr,it,de,pt,ru,zh-CN,hi,ur,fa,nl,ja,ko,ku'},'google_translate_element')}
 catch(e){el('trHelp').textContent='Translation could not load. Please use your browser’s own translate option.'}};
var trLoaded=false;
el('trBtn').onclick=function(){
 var p=el('trPanel');p.hidden=!p.hidden;el('trBtn').setAttribute('aria-expanded',String(!p.hidden));
 if(!p.hidden&&!trLoaded){trLoaded=true;
  var sc=document.createElement('script');sc.src='https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit&hl=en';
  sc.onerror=function(){trLoaded=false;el('trHelp').textContent='Translation could not load. Please check your internet connection or use your browser’s own translate option.'};
  document.body.appendChild(sc)}};
})();
</script>
</body>
</html>
