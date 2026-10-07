<?php
session_start();

if (empty($_SESSION['is_admin'])) {
    header('Location: admin_login.php');
    exit;
}

require_once __DIR__ . '/db.php';

$DEBUG = true;

function pick(array $row, array $names, $default = '') {
    foreach ($names as $n) {
        if (array_key_exists($n, $row) && $row[$n] !== null) return $row[$n];
    }
    return $default;
}

$movies = $users = [];
$movieCount = $userCount = 0;
$dbError = '';
try {
    $movieCount = (int) $pdo->query('SELECT COUNT(*) FROM movies')->fetchColumn();
    $userCount  = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

    foreach ($pdo->query('SELECT * FROM movies ORDER BY movie_id DESC')->fetchAll() as $r) {
        $year = pick($r, ['year', 'release_year', 'release_date', 'released'], '');
        $movies[] = [
            'id'     => pick($r, ['id', 'movie_id']),
            'title'  => pick($r, ['title', 'movie_title', 'name']),
            'genre'  => pick($r, ['genre', 'category', 'genres']),
            'year'   => substr((string) $year, 0, 4),
            'rating' => (float) pick($r, ['rating', 'score', 'vote_average'], 0),
            'poster' => pick($r, ['poster', 'image', 'poster_path', 'poster_url'], ''),
        ];
    }

    foreach ($pdo->query('SELECT * FROM users')->fetchAll() as $r) {
        $name = pick($r, ['name', 'username', 'full_name', 'fullname'], '');
        if ($name === '') {
            $name = trim(pick($r, ['first_name'], '') . ' ' . pick($r, ['last_name'], ''));
        }
        $users[] = [
            'id'     => pick($r, ['id', 'user_id']),
            'name'   => $name ?: 'User',
            'email'  => pick($r, ['email', 'email_address']),
            'role'   => pick($r, ['role', 'user_role', 'type'], 'User'),
            'joined' => pick($r, ['created_at', 'joined', 'date_created', 'registered_at', 'created'], ''),
        ];
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $dbError = $DEBUG ? 'Database error: ' . $e->getMessage() : 'Could not load data from the database.';
}
$json = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin | ReelPick</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #0e0c0d; --surface: #171415; --line: #2a2526; --text: #f4eee8; --muted: #9a9092;
  --red: #e5383b; --red-dark: #b3282b; --amber: #f5a524;
  --display: "Bricolage Grotesque", "Arial Black", sans-serif;
  --body: "Figtree", system-ui, sans-serif;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--bg); color: var(--text); font-family: var(--body); line-height: 1.5; min-height: 100vh; }
button { font: inherit; color: inherit; cursor: pointer; }
:focus-visible { outline: 2px solid var(--amber); outline-offset: 3px; }
[hidden] { display: none !important; }

.layout { display: grid; grid-template-columns: 250px 1fr; min-height: 100vh; }

.sidebar { position: sticky; top: 0; height: 100vh; display: flex; flex-direction: column; padding: 28px 18px; background: var(--surface); border-right: 1px solid var(--line); }
.brand { font-family: var(--display); font-weight: 800; font-size: 26px; color: var(--red); letter-spacing: -.5px; padding: 0 12px; }
.brand small { display: block; font-family: var(--body); font-weight: 500; font-size: 13px; color: var(--muted); letter-spacing: 0; }
.side-nav { display: flex; flex-direction: column; gap: 4px; margin-top: 36px; }
.side-nav button, .logout { display: flex; align-items: center; gap: 12px; width: 100%; background: none; border: 0; text-align: left; padding: 11px 14px; border-radius: 12px; color: var(--muted); font-weight: 500; transition: color .2s, background .2s; }
.side-nav button:hover, .logout:hover { color: var(--text); background: var(--bg); }
.side-nav button.active { color: #fff; background: var(--red); }
.side-nav svg, .logout svg { width: 20px; height: 20px; flex: none; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
.logout { margin-top: auto; border: 1px solid var(--line); }
.logout:hover { color: var(--red); border-color: var(--red); }

main { padding: 40px 48px 60px; min-width: 0; }
.page-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 28px; }
.page-head h1 { font-family: var(--display); font-weight: 800; font-size: 36px; letter-spacing: -1.2px; line-height: 1.1; }
.page-head p { color: var(--muted); font-size: 15px; }
.tools { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.search { background: var(--surface); border: 1px solid var(--line); border-radius: 999px; padding: 10px 18px; color: var(--text); font: inherit; width: 240px; max-width: 100%; }
.search::placeholder { color: var(--muted); }
.btn { background: var(--red); border: 1px solid var(--red); color: #fff; border-radius: 999px; padding: 10px 22px; font-weight: 600; transition: background .2s; }
.btn:hover { background: var(--red-dark); }
.btn.ghost { background: transparent; border-color: var(--line); color: var(--text); }
.btn.ghost:hover { background: var(--surface); }
.btn:disabled { opacity: .6; cursor: not-allowed; }

.stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 22px; margin-bottom: 40px; }
.stat { position: relative; overflow: hidden; background: var(--surface); border: 1px solid var(--line); border-radius: 22px; padding: 30px 32px; }
.stat span { color: var(--muted); font-weight: 500; }
.stat b { display: block; font-family: var(--display); font-weight: 800; font-size: clamp(56px, 7vw, 92px); line-height: 1; letter-spacing: -3px; margin-top: 8px; }

.panels { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; }
.panel { background: var(--surface); border: 1px solid var(--line); border-radius: 22px; padding: 24px; min-width: 0; }
.panel h2 { font-family: var(--display); font-size: 20px; letter-spacing: -.4px; margin-bottom: 14px; }
.row { display: flex; align-items: center; gap: 14px; padding: 11px 0; border-top: 1px solid var(--line); }
.row:first-of-type { border-top: 0; }
.row .grow { flex: 1; min-width: 0; }
.row strong { display: block; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.row small { color: var(--muted); font-size: 13px; }

.table-wrap { background: var(--surface); border: 1px solid var(--line); border-radius: 22px; overflow-x: auto; }
table { width: 100%; border-collapse: collapse; min-width: 640px; }
th { text-align: left; color: var(--muted); font-weight: 500; font-size: 14px; padding: 16px 20px; border-bottom: 1px solid var(--line); }
td { padding: 14px 20px; border-bottom: 1px solid var(--line); vertical-align: middle; }
tr:last-child td { border-bottom: 0; }
tbody tr:hover { background: rgba(255,255,255,.02); }
.cell { display: flex; align-items: center; gap: 14px; }
.cell strong { font-weight: 600; }
.avatar, .thumb { flex: none; display: grid; place-items: center; font-family: var(--display); font-weight: 800; color: #fff; background: linear-gradient(145deg, var(--red), #5a1517); overflow: hidden; }
.avatar { width: 38px; height: 38px; border-radius: 50%; font-size: 15px; }
.thumb { width: 42px; height: 62px; border-radius: 8px; font-size: 18px; }
.thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pill { display: inline-block; border: 1px solid var(--line); border-radius: 999px; padding: 2px 12px; font-size: 13px; color: var(--muted); }
.pill.admin { border-color: var(--amber); color: var(--amber); }
.rating { color: var(--amber); font-weight: 600; }
.actions { display: flex; gap: 8px; justify-content: flex-end; }
.icon-btn { background: none; border: 1px solid var(--line); border-radius: 10px; padding: 6px 12px; font-size: 13px; color: var(--muted); transition: color .2s, border-color .2s; }
.icon-btn:hover { color: var(--text); border-color: var(--muted); }
.icon-btn.danger:hover { color: var(--red); border-color: var(--red); }
.empty { text-align: center; color: var(--muted); padding: 40px 20px; }

dialog { margin: auto; width: min(480px, 92vw); background: var(--surface); color: var(--text); border: 1px solid var(--line); border-radius: 22px; padding: 32px; }
dialog::backdrop { background: rgba(0,0,0,.72); backdrop-filter: blur(4px); }
dialog h2 { font-family: var(--display); font-size: 26px; letter-spacing: -.8px; margin-bottom: 20px; }
dialog label { display: block; color: var(--muted); font-size: 14px; margin-bottom: 14px; }
dialog input[type="text"],
dialog input[type="number"],
dialog input[type="file"] {
  display: block; width: 100%; margin-top: 6px; background: var(--bg); border: 1px solid var(--line);
  border-radius: 12px; padding: 11px 14px; color: var(--text); font: inherit;
}
dialog input:focus { border-color: var(--red); outline: none; }
dialog input[type="file"] { padding: 10px; cursor: pointer; }
.two { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.dialog-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }
.preview { margin-top: 10px; max-width: 120px; border-radius: 10px; display: none; }
.form-error { color: #ff8b8d; font-size: 14px; margin-bottom: 12px; }

@media (max-width: 860px) {
  .layout { grid-template-columns: 1fr; }
  .sidebar { position: static; height: auto; flex-direction: row; align-items: center; flex-wrap: wrap; gap: 12px; padding: 14px 16px; border-right: 0; border-bottom: 1px solid var(--line); }
  .brand small { display: none; }
  .side-nav { flex-direction: row; margin: 0; order: 3; width: 100%; }
  .side-nav button { justify-content: center; padding: 9px 10px; }
  .logout { margin: 0 0 0 auto; width: auto; }
  main { padding: 28px 18px 48px; }
  .stats, .panels { grid-template-columns: 1fr; }
}
@media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
</style>
</head>
<body>
<div class="layout">

  <aside class="sidebar">
    <div class="brand">ReelPick<small>Admin panel</small></div>
    <nav class="side-nav" aria-label="Admin">
      <button data-view="dashboard" class="active">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>Dashboard
      </button>
      <button data-view="users">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18 14.4c2 .7 3.5 2.6 3.5 5.6"/></svg>Users
      </button>
      <button data-view="movies">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 4v16M17 4v16M3 9h4M3 15h4M17 9h4M17 15h4"/></svg>Movies
      </button>
    </nav>
    <button class="logout" id="logoutBtn">
      <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>Log out
    </button>
  </aside>

  <main>
    <?php if ($dbError): ?><p class="empty" role="alert" style="color:#ff8b8d"><?= htmlspecialchars($dbError) ?></p><?php endif; ?>

    <section id="view-dashboard">
      <div class="page-head"><div><h1>Dashboard</h1><p>A quick look at your site.</p></div></div>
      <div class="stats">
        <div class="stat"><span>Total movies</span><b id="statMovies">0</b></div>
        <div class="stat"><span>Registered users</span><b id="statUsers">0</b></div>
      </div>
      <div class="panels">
        <div class="panel"><h2>Latest movies</h2><div id="recentMovies"></div></div>
        <div class="panel"><h2>New users</h2><div id="recentUsers"></div></div>
      </div>
    </section>

    <section id="view-users" hidden>
      <div class="page-head">
        <div><h1>Users</h1><p id="userCount"></p></div>
        <div class="tools"><input class="search" id="userSearch" type="search" placeholder="Search users" aria-label="Search users"></div>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>User</th><th>Role</th><th>Joined</th><th></th></tr></thead>
          <tbody id="userRows"></tbody>
        </table>
      </div>
    </section>

    <section id="view-movies" hidden>
      <div class="page-head">
        <div><h1>Movies</h1><p id="movieCount"></p></div>
        <div class="tools">
          <input class="search" id="movieSearch" type="search" placeholder="Search movies" aria-label="Search movies">
          <button class="btn" id="addMovieBtn">Add movie</button>
        </div>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Movie</th><th>Genre</th><th>Year</th><th>Rating</th><th></th></tr></thead>
          <tbody id="movieRows"></tbody>
        </table>
      </div>
    </section>
  </main>
</div>

<dialog id="movieDialog">
  <h2>Add movie</h2>
  <p id="formError" class="form-error" hidden></p>
  <form id="movieForm">
    <label>Title<input name="title" required></label>
    <label>Genre<input name="genre" placeholder="e.g. Horror, Action"></label>
    <div class="two">
      <label>Year<input name="year" type="number" min="1888" max="2100" value="<?= date('Y') ?>"></label>
      <label>Runtime (min)<input name="runtime" type="number" min="0" value="0"></label>
    </div>
    <div class="two">
      <label>Rating (0–10)<input name="rating" type="number" min="0" max="10" step="0.1" value="7.0"></label>
      <label>Price<input name="price" type="number" min="0" step="0.01" value="0"></label>
    </div>
    <label>Moods<input name="moods" placeholder="e.g. intense, creepy"></label>
    <label>About<input name="about" placeholder="Short description"></label>
    <label>
      Poster image
      <input type="file" name="poster" accept="image/jpeg,image/png,image/webp,image/gif">
    </label>
    <img id="posterPreview" class="preview" alt="">
    <div class="dialog-actions">
      <button type="button" class="btn ghost" id="cancelMovie">Cancel</button>
      <button type="submit" class="btn" id="saveMovieBtn">Save movie</button>
    </div>
  </form>
</dialog>

<script>
const totals = { movies: <?= $movieCount ?>, users: <?= $userCount ?> };
let movies = <?= json_encode($movies, $json) ?>;
let users  = <?= json_encode($users, $json) ?>;

const $ = s => document.querySelector(s);
const esc = s => String(s).replace(/[&<>"']/g, c => ({ "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;" }[c]));
const initial = s => esc((s || "?").trim().charAt(0).toUpperCase());
const fmtDate = d => !d || isNaN(new Date(d)) ? "" : new Date(d).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });

function thumbHTML(m, size = '') {
  const style = size ? `style="${size}"` : '';
  if (m.poster) {
    const src = m.poster.startsWith('http') || m.poster.startsWith('/') || m.poster.startsWith('assets')
      ? (m.poster.startsWith('assets') ? '../' + m.poster : m.poster)
      : m.poster;
    return `<div class="thumb" ${style}><img src="${esc(src)}" alt="" loading="lazy" onerror="this.parentNode.textContent='${initial(m.title)}'"></div>`;
  }
  return `<div class="thumb" ${style}>${initial(m.title)}</div>`;
}

function renderDashboard() {
  $("#statMovies").textContent = totals.movies;
  $("#statUsers").textContent = totals.users;
  $("#recentMovies").innerHTML = movies.slice(0, 4).map(m => `
    <div class="row">${thumbHTML(m, 'width:34px;height:50px;font-size:15px')}
    <div class="grow"><strong>${esc(m.title)}</strong><small>${esc(m.genre || '')}${m.year ? ', ' + m.year : ''}</small></div>
    <span class="rating">${Number(m.rating || 0).toFixed(1)}</span></div>`).join("") || `<p class="empty">No movies yet.</p>`;
  $("#recentUsers").innerHTML = users.slice(0, 4).map(u => `
    <div class="row"><div class="avatar">${initial(u.name)}</div>
    <div class="grow"><strong>${esc(u.name)}</strong><small>${esc(u.email)}</small></div>
    <small>${fmtDate(u.joined)}</small></div>`).join("") || `<p class="empty">No users yet.</p>`;
}

function renderUsers() {
  const q = $("#userSearch").value.toLowerCase();
  const list = users.filter(u => ((u.name || '') + (u.email || '')).toLowerCase().includes(q));
  $("#userCount").textContent = `${users.length} registered`;
  $("#userRows").innerHTML = list.map(u => `
    <tr><td><div class="cell"><div class="avatar">${initial(u.name)}</div>
      <div><strong>${esc(u.name)}</strong><br><small style="color:var(--muted)">${esc(u.email)}</small></div></div></td>
    <td><span class="pill ${u.role === "Admin" ? "admin" : ""}">${esc(u.role)}</span></td>
    <td>${fmtDate(u.joined)}</td>
    <td><div class="actions"><button class="icon-btn danger" data-del-user="${u.id}">Delete</button></div></td></tr>`
  ).join("") || `<tr><td colspan="4" class="empty">No users match your search.</td></tr>`;
}

function renderMovies() {
  const q = $("#movieSearch").value.toLowerCase();
  const list = movies.filter(m => ((m.title || '') + (m.genre || '')).toLowerCase().includes(q));
  $("#movieCount").textContent = `${movies.length} in the catalog`;
  $("#movieRows").innerHTML = list.map(m => `
    <tr>
      <td><div class="cell">${thumbHTML(m)}<strong>${esc(m.title)}</strong></div></td>
      <td><span class="pill">${esc(m.genre || '—')}</span></td>
      <td>${m.year || '—'}</td>
      <td class="rating">${Number(m.rating || 0).toFixed(1)}</td>
      <td><div class="actions"><button class="icon-btn danger" data-del-movie="${m.id}">Delete</button></div></td>
    </tr>`
  ).join("") || `<tr><td colspan="5" class="empty">No movies. Use Add movie to create one.</td></tr>`;
}

function renderAll() { renderDashboard(); renderUsers(); renderMovies(); }

function show(view) {
  ["dashboard", "users", "movies"].forEach(v => $("#view-" + v).hidden = v !== view);
  document.querySelectorAll(".side-nav button").forEach(b => b.classList.toggle("active", b.dataset.view === view));
  location.hash = view;
}
document.querySelectorAll(".side-nav button").forEach(b => b.addEventListener("click", () => show(b.dataset.view)));

$("#userSearch").addEventListener("input", renderUsers);
$("#movieSearch").addEventListener("input", renderMovies);

document.addEventListener("click", async e => {
  const du = e.target.closest("[data-del-user]");
  const dm = e.target.closest("[data-del-movie]");

  if (du && confirm("Delete this user?")) {
    users = users.filter(u => u.id != du.dataset.delUser);
    totals.users = users.length;
    renderAll();
  }

  if (dm && confirm("Delete this movie from the database?")) {
    const id = dm.dataset.delMovie;
    try {
      const fd = new FormData();
      fd.append("id", id);
      const res = await fetch("delete_movie.php", { method: "POST", body: fd });
      const json = await res.json();
      if (!json.ok) {
        alert(json.error || "Delete failed");
        return;
      }
      movies = movies.filter(m => m.id != id);
      totals.movies = movies.length;
      renderAll();
    } catch {
      alert("Network error");
    }
  }
});

/* ---------- ADD MOVIE ---------- */
const dlg = $("#movieDialog");
const form = $("#movieForm");
const preview = $("#posterPreview");
const formError = $("#formError");
const saveBtn = $("#saveMovieBtn");

$("#addMovieBtn").addEventListener("click", () => {
  form.reset();
  preview.style.display = "none";
  preview.src = "";
  formError.hidden = true;
  dlg.showModal();
});

$("#cancelMovie").addEventListener("click", () => dlg.close());

form.poster.addEventListener("change", () => {
  const file = form.poster.files[0];
  if (file) {
    preview.src = URL.createObjectURL(file);
    preview.style.display = "block";
  } else {
    preview.style.display = "none";
  }
});

form.addEventListener("submit", async (e) => {
  e.preventDefault();
  formError.hidden = true;
  saveBtn.disabled = true;
  saveBtn.textContent = "Saving…";

  try {
    const res = await fetch("add_movie.php", { method: "POST", body: new FormData(form) });
    const json = await res.json();
    if (!json.ok) {
      formError.textContent = json.error || "Something went wrong";
      formError.hidden = false;
      return;
    }
    movies.unshift({
      id: json.movie.id,
      title: json.movie.title,
      genre: json.movie.genre || '',
      year: json.movie.year || '',
      rating: json.movie.rating || 0,
      poster: json.movie.poster || '',
    });
    totals.movies = movies.length;
    renderAll();
    dlg.close();
  } catch {
    formError.textContent = "Network error. Please try again.";
    formError.hidden = false;
  } finally {
    saveBtn.disabled = false;
    saveBtn.textContent = "Save movie";
  }
});

$("#logoutBtn").addEventListener("click", () => {
  window.location.href = "logout.php";
});

renderAll();
const start = location.hash.slice(1);
show(["users", "movies"].includes(start) ? start : "dashboard");
</script>
</body>
</html>