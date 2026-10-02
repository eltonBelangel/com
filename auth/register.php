<?php
$msgs = [
    'invalid' => 'Please fill in every field with a valid email.',
    'short'   => 'Password must be at least 8 characters.',
    'exists'  => 'That email is already registered.',
    'server'  => 'Something went wrong. Please try again.',
];
$err = $msgs[$_GET['error'] ?? ''] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register | ReelPick</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #0e0c0d; --surface: #171415; --line: #2a2526; --text: #f4eee8; --muted: #9a9092;
      --red: #e5383b; --red-dark: #b3282b; --amber: #f5a524; --green: #3fae6a;
      --display: "Bricolage Grotesque", "Arial Black", sans-serif;
      --body: "Figtree", system-ui, sans-serif;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh; display: grid; place-items: center; padding: 24px;
      background:
        radial-gradient(circle at 15% 8%, rgba(229,56,59,.20), transparent 42%),
        radial-gradient(circle at 88% 92%, rgba(245,165,36,.09), transparent 40%),
        var(--bg);
      color: var(--text); font-family: var(--body); line-height: 1.5;
    }
    a { color: inherit; }
    :focus-visible { outline: 2px solid var(--amber); outline-offset: 3px; }
    svg { fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }

    .card {
      width: 100%; max-width: 440px; background: var(--surface);
      border: 1px solid var(--line); border-radius: 26px; padding: 40px 36px 28px;
      box-shadow: 0 30px 80px rgba(0,0,0,.45);
      animation: rise .45s ease both;
    }
    @keyframes rise { from { opacity: 0; transform: translateY(14px); } }

    .brand { display: inline-block; font-family: var(--display); font-weight: 800; font-size: 24px; color: var(--red); text-decoration: none; letter-spacing: -.5px; margin-bottom: 28px; }

    .badge { width: 56px; height: 56px; border-radius: 18px; display: grid; place-items: center; margin-bottom: 18px;
             background: rgba(229,56,59,.12); border: 1px solid rgba(229,56,59,.35); color: var(--red); }
    .badge svg { width: 26px; height: 26px; }

    h1 { font-family: var(--display); font-weight: 800; font-size: 32px; letter-spacing: -1px; line-height: 1.1; }
    .sub { color: var(--muted); font-size: 15px; margin: 8px 0 26px; }

    .error { display: flex; gap: 10px; align-items: center; background: rgba(229,56,59,.12); border: 1px solid var(--red);
             color: #ff9a9c; border-radius: 12px; padding: 11px 14px; font-size: 14px; margin-bottom: 18px; }
    .error svg { width: 18px; height: 18px; flex: none; }

    .two { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .field { margin-bottom: 16px; }
    .field label { display: block; font-size: 14px; font-weight: 500; color: var(--muted); margin-bottom: 7px; }
    .control { position: relative; }
    .control > svg { position: absolute; left: 14px; top: 50%; width: 19px; height: 19px; transform: translateY(-50%); color: var(--muted); pointer-events: none; }
    .control input {
      width: 100%; background: var(--bg); border: 1px solid var(--line); border-radius: 14px;
      padding: 13px 14px 13px 44px; color: var(--text); font: inherit; transition: border-color .2s, box-shadow .2s;
    }
    .control.pw input { padding-right: 44px; }
    .control input::placeholder { color: #5f5658; }
    .control input:focus { outline: none; border-color: var(--red); box-shadow: 0 0 0 4px rgba(229,56,59,.15); }
    .eye { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; display: grid; place-items: center;
           background: none; border: 0; border-radius: 10px; color: var(--muted); cursor: pointer; transition: color .2s, background .2s; }
    .eye:hover { color: var(--text); background: var(--surface); }
    .eye svg { position: static; transform: none; width: 19px; height: 19px; }

    .meter { display: grid; grid-template-columns: repeat(4, 1fr); gap: 5px; margin-top: 10px; }
    .meter i { height: 4px; border-radius: 99px; background: var(--line); transition: background .25s; }
    .hint { display: flex; justify-content: space-between; font-size: 13px; color: var(--muted); margin-top: 7px; }
    .hint b { font-weight: 500; }

    .submit { width: 100%; margin-top: 8px; background: var(--red); border: 1px solid var(--red); color: #fff; border-radius: 999px;
              padding: 14px; font: inherit; font-weight: 600; cursor: pointer; transition: background .2s, transform .1s; }
    .submit:hover { background: var(--red-dark); }
    .submit:active { transform: scale(.985); }
    .submit:disabled { opacity: .65; cursor: wait; }

    .divider { display: flex; align-items: center; gap: 14px; color: var(--muted); font-size: 13px; margin: 26px 0 18px; }
    .divider::before, .divider::after { content: ""; flex: 1; height: 1px; background: var(--line); }

    .alt { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; text-decoration: none;
           border: 1px solid var(--line); border-radius: 999px; padding: 12px 18px; font-weight: 500; color: var(--text);
           transition: border-color .2s, background .2s; }
    .alt svg { width: 18px; height: 18px; transition: transform .2s; }
    .alt:hover { border-color: var(--muted); background: var(--bg); }
    .alt:hover svg { transform: translateX(-4px); }

    /* header row: small back button beside the logo */
    .head { display: flex; align-items: center; gap: 12px; margin-bottom: 28px; flex-wrap: nowrap; }
    .head .brand { margin: 0; }
    .top .head { margin-bottom: 0; }
    .back-home { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px 3px 6px;
                 border: 1px solid var(--line); border-radius: 999px; color: var(--muted); font-size: 12px; font-weight: 500; line-height: 1.5;
                 text-decoration: none; transition: color .2s, border-color .2s, background .2s; }
    .back-home svg { width: 13px; height: 13px; transition: transform .2s; }
    .back-home:hover { color: var(--text); border-color: var(--muted); background: var(--bg); }
    .back-home:hover svg { transform: translateX(-3px); }

    @media (max-width: 480px) { .card { padding: 32px 22px 24px; border-radius: 22px; } h1 { font-size: 28px; } .two { grid-template-columns: 1fr; gap: 0; } }
    @media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }
  </style>
</head>
<body>
<main class="card">
  <div class="head">
    <a class="back-home" href="../src/screens/index.html" aria-label="Back to home">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
      Back to home
    </a>
    <a href="../src/screens/index.html" class="brand">ReelPick</a>
  </div>

  <div class="badge" aria-hidden="true">
    <svg viewBox="0 0 24 24"><circle cx="10" cy="8" r="4"/><path d="M2.5 20c0-3.8 3.4-6.2 7.5-6.2 1.6 0 3 .3 4.2 1M18 14v6M15 17h6"/></svg>
  </div>
  <h1>Create your account</h1>
  <p class="sub">Save movies to your watchlist and get picks that fit your taste.</p>

  <form method="POST" action="process_register.php" id="regForm">
    <?php if ($err): ?>
      <p class="error" role="alert">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/></svg>
        <?= htmlspecialchars($err) ?>
      </p>
    <?php endif; ?>

    <div class="two">
      <div class="field">
        <label for="first_name">First name</label>
        <div class="control">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
          <input type="text" id="first_name" name="first_name" placeholder="Ana" autocomplete="given-name" required autofocus>
        </div>
      </div>
      <div class="field">
        <label for="last_name">Last name</label>
        <div class="control">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
          <input type="text" id="last_name" name="last_name" placeholder="Cruz" autocomplete="family-name" required>
        </div>
      </div>
    </div>

    <div class="field">
      <label for="email">Email</label>
      <div class="control">
        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="M3.5 7.5l8.5 6 8.5-6"/></svg>
        <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" required>
      </div>
    </div>

    <div class="field">
      <label for="password">Password</label>
      <div class="control pw">
        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10.5" width="16" height="10" rx="2"/><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5"/></svg>
        <input type="password" id="password" name="password" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required aria-describedby="pwHint">
        <button type="button" class="eye" id="togglePw" aria-label="Show password" aria-pressed="false">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
      <div class="meter" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
      <p class="hint" id="pwHint"><span>Use 8 or more characters</span><b id="pwLabel"></b></p>
    </div>

    <button class="submit" id="submitBtn">Create account</button>
  </form>

  <div class="divider">already a member?</div>

  <a class="alt" href="login.php">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
    Back to login
  </a>
</main>

<script>
  // show / hide password
  const pw = document.getElementById('password'), eye = document.getElementById('togglePw');
  eye.addEventListener('click', () => {
    const show = pw.type === 'password';
    pw.type = show ? 'text' : 'password';
    eye.setAttribute('aria-pressed', show);
    eye.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
  });

  // simple strength meter (length, mixed case, digits, symbols)
  const bars = document.querySelectorAll('.meter i'), label = document.getElementById('pwLabel');
  const levels = [['', 'var(--line)'], ['Weak', 'var(--red)'], ['Okay', 'var(--amber)'], ['Good', 'var(--green)'], ['Strong', 'var(--green)']];
  pw.addEventListener('input', () => {
    const v = pw.value;
    let s = 0;
    if (v.length >= 8) s++;
    if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
    if (/\d/.test(v)) s++;
    if (/[^A-Za-z0-9]/.test(v) || v.length >= 12) s++;
    const short = v.length > 0 && v.length < 8;
    if (!v) s = 0; else if (short) s = 1;
    bars.forEach((b, i) => b.style.background = i < s ? levels[s][1] : 'var(--line)');
    label.textContent = short ? 'Too short' : levels[s][0];
  });

  // stop double submits
  document.getElementById('regForm').addEventListener('submit', () => {
    const b = document.getElementById('submitBtn'); b.disabled = true; b.textContent = 'Creating account...';
  });
</script>
</body>
</html>