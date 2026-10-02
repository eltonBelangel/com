<?php
session_start();

// If already logged in as admin, go straight to the panel
if (!empty($_SESSION['is_admin'])) {
    header('Location: admin.php');
    exit;
}

$error = '';
if (isset($_GET['error'])) {
    $error = 'Wrong username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login | ReelPick</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #0e0c0d; --surface: #171415; --line: #2a2526; --text: #f4eee8; --muted: #9a9092;
      --red: #e5383b; --red-dark: #b3282b; --amber: #f5a524;
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

    .card {
      width: 100%; max-width: 420px; background: var(--surface);
      border: 1px solid var(--line); border-radius: 26px; padding: 40px 36px 30px;
      box-shadow: 0 30px 80px rgba(0,0,0,.45);
      animation: rise .45s ease both;
    }
    @keyframes rise { from { opacity: 0; transform: translateY(14px); } }

    .top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; flex-wrap: wrap; gap: 10px; }
    .brand { font-family: var(--display); font-weight: 800; font-size: 24px; color: var(--red); text-decoration: none; letter-spacing: -.5px; }
    .tag { border: 1px solid var(--amber); color: var(--amber); border-radius: 999px; padding: 2px 12px; font-size: 12px; font-weight: 600; }

    .badge { width: 56px; height: 56px; border-radius: 18px; display: grid; place-items: center; margin-bottom: 18px;
             background: rgba(229,56,59,.12); border: 1px solid rgba(229,56,59,.35); color: var(--red); }
    .badge svg { width: 26px; height: 26px; }
    svg { fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }

    h1 { font-family: var(--display); font-weight: 800; font-size: 32px; letter-spacing: -1px; line-height: 1.1; }
    .sub { color: var(--muted); font-size: 15px; margin: 8px 0 26px; }

    .error { display: flex; gap: 10px; align-items: center; background: rgba(229,56,59,.12); border: 1px solid var(--red);
             color: #ff9a9c; border-radius: 12px; padding: 11px 14px; font-size: 14px; margin-bottom: 18px; }
    .error svg { width: 18px; height: 18px; flex: none; }

    .field { margin-bottom: 16px; }
    .field label { display: block; font-size: 14px; font-weight: 500; color: var(--muted); margin-bottom: 7px; }
    .control { position: relative; }
    .control > svg { position: absolute; left: 14px; top: 50%; width: 19px; height: 19px; transform: translateY(-50%); color: var(--muted); pointer-events: none; }
    .control input {
      width: 100%; background: var(--bg); border: 1px solid var(--line); border-radius: 14px;
      padding: 13px 44px 13px 44px; color: var(--text); font: inherit; transition: border-color .2s, box-shadow .2s;
    }
    .control input::placeholder { color: #5f5658; }
    .control input:focus { outline: none; border-color: var(--red); box-shadow: 0 0 0 4px rgba(229,56,59,.15); }
    .eye { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; display: grid; place-items: center;
           background: none; border: 0; border-radius: 10px; color: var(--muted); cursor: pointer; transition: color .2s, background .2s; }
    .eye:hover { color: var(--text); background: var(--surface); }
    .eye svg { position: static; transform: none; width: 19px; height: 19px; }

    .submit { width: 100%; margin-top: 8px; background: var(--red); border: 1px solid var(--red); color: #fff; border-radius: 999px;
              padding: 14px; font: inherit; font-weight: 600; cursor: pointer; transition: background .2s, transform .1s; }
    .submit:hover { background: var(--red-dark); }
    .submit:active { transform: scale(.985); }
    .submit:disabled { opacity: .65; cursor: wait; }

    .divider { display: flex; align-items: center; gap: 14px; color: var(--muted); font-size: 13px; margin: 26px 0 18px; }
    .divider::before, .divider::after { content: ""; flex: 1; height: 1px; background: var(--line); }

    /* the improved "back" link */
    .back { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; text-decoration: none;
            border: 1px solid var(--line); border-radius: 999px; padding: 12px 18px; font-weight: 500; color: var(--text);
            transition: border-color .2s, background .2s; }
    .back svg { width: 18px; height: 18px; transition: transform .2s; }
    .back:hover { border-color: var(--muted); background: var(--bg); }
    .back:hover svg { transform: translateX(-4px); }

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

    @media (max-width: 480px) { .card { padding: 32px 22px 24px; border-radius: 22px; } h1 { font-size: 28px; } }
    @media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }
  </style>
</head>
<body>
<main class="card">
  <div class="top">
    <div class="head">
    <a class="back-home" href="../src/screens/index.html" aria-label="Back to home">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
      Back to home
    </a>
    <a href="../src/screens/index.html" class="brand">ReelPick</a>
    </div>
    <span class="tag">Admin</span>
  </div>

  <div class="badge" aria-hidden="true">
    <svg viewBox="0 0 24 24"><path d="M12 3l8 3v6c0 4.5-3.2 8-8 9-4.8-1-8-4.5-8-9V6z"/><rect x="9" y="10.5" width="6" height="5" rx="1"/><path d="M10.5 10.5V9a1.5 1.5 0 0 1 3 0v1.5"/></svg>
  </div>
  <h1>Admin sign in</h1>
  <p class="sub">This area is for site administrators only.</p>

  <form method="POST" action="process_admin_login.php" id="loginForm">
    <?php if ($error): ?>
      <p class="error" role="alert">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/></svg>
        <?= htmlspecialchars($error) ?>
      </p>
    <?php endif; ?>

    <div class="field">
      <label for="username">Username</label>
      <div class="control">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
        <input type="text" id="username" name="username" placeholder="Enter your username" autocomplete="username" required autofocus>
      </div>
    </div>

    <div class="field">
      <label for="password">Password</label>
      <div class="control">
        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10.5" width="16" height="10" rx="2"/><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5"/></svg>
        <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
        <button type="button" class="eye" id="togglePw" aria-label="Show password" aria-pressed="false">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
    </div>

    <button class="submit" id="submitBtn">Login as admin</button>
  </form>

  <div class="divider">not an admin?</div>

  <a class="back" href="../auth/login.php">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
    Back to user login
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
  // stop double submits
  document.getElementById('loginForm').addEventListener('submit', () => {
    const b = document.getElementById('submitBtn'); b.disabled = true; b.textContent = 'Signing in...';
  });
</script>
</body>
</html>