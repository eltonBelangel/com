/* ReelPick: content-based recommender. Runs in the browser, remembers taste in localStorage. */
const IMG = "../../assets/Images/";
const $ = (s, r = document) => r.querySelector(s);
const store = {
  get(k, d) { try { return JSON.parse(localStorage.getItem("rp_" + k)) ?? d; } catch { return d; } },
  set(k, v) { try { localStorage.setItem("rp_" + k, JSON.stringify(v)); } catch {} }
};
const cap = s => s.charAt(0).toUpperCase() + s.slice(1);
const tagsOf = m => [...m.genres.map(g => g.toLowerCase()), ...m.moods];

const ALL_MOODS = [...new Set(MOVIES.flatMap(m => m.moods))].sort();
const ALL_GENRES = [...new Set(MOVIES.flatMap(m => m.genres))].sort();

const S = {
  picks: new Set(store.get("picks", [])),   // chosen mood/genre tags (lowercase)
  liked: new Set(store.get("liked", [])),
  passed: new Set(store.get("passed", [])),
  saved: new Set(store.get("saved", [])),
  genre: "All", q: ""
};
const save = () => ["picks", "liked", "passed", "saved"].forEach(k => store.set(k, [...S[k]]));
const byId = id => MOVIES.find(m => m.id === id);

/* ---- recommender ---- */
function profile() {
  const w = {};
  S.picks.forEach(t => w[t] = (w[t] || 0) + 3);
  S.liked.forEach(id => tagsOf(byId(id)).forEach(t => w[t] = (w[t] || 0) + 1.2));
  S.passed.forEach(id => tagsOf(byId(id)).forEach(t => w[t] = (w[t] || 0) - 1.2));
  return w;
}
const hasSignal = () => S.picks.size + S.liked.size + S.passed.size > 0;

function score(m, w) {
  const raw = tagsOf(m).reduce((a, t) => a + (w[t] || 0), 0);
  const match = Math.round(100 / (1 + Math.exp(-(raw - 3) / 2.5)));
  return { m, match: Math.min(98, Math.max(3, match)), rank: raw + m.rating * 0.15 };
}
function why(m, w) {
  const picked = tagsOf(m).filter(t => S.picks.has(t));
  if (picked.length) return "Fits your " + picked.slice(0, 2).join(" + ") + " mood";
  let best = null, n = 0;
  S.liked.forEach(id => { const o = byId(id), c = tagsOf(o).filter(t => tagsOf(m).includes(t)).length; if (c > n) { n = c; best = o; } });
  return best && n ? "Because you liked " + best.title : "Highly rated";
}
function recommend() {
  const w = profile();
  return MOVIES.filter(m => !S.liked.has(m.id) && !S.passed.has(m.id))
    .map(m => score(m, w)).sort((a, b) => b.rank - a.rank)
    .map(r => ({ ...r, reason: why(r.m, w) }));
}
function similar(m) {
  const t = new Set(tagsOf(m));
  return MOVIES.filter(o => o.id !== m.id).map(o => {
    const ot = tagsOf(o), inter = ot.filter(x => t.has(x)).length;
    return { o, s: inter / (t.size + ot.length - inter) };
  }).sort((a, b) => b.s - a.s).slice(0, 4).map(x => x.o);
}

/* ---- rendering ---- */
const bookmark = `<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M6 3h12v18l-6-4-6 4z" fill="currentColor"/></svg>`;
function card(m, match) {
  const on = S.saved.has(m.id);
  return `<article class="card">
    <div class="poster-wrap">
      <button class="poster" data-open="${m.id}" aria-label="Details for ${m.title}"><img src="${IMG + m.img}" alt="${m.title} poster" loading="lazy"></button>
      ${match != null ? `<span class="badge">${match}% match</span>` : ""}
      <button class="save ${on ? "on" : ""}" data-save="${m.id}" aria-pressed="${on}" aria-label="${on ? "Remove from" : "Add to"} watchlist">${bookmark}</button>
    </div>
    <h3>${m.title}</h3>
    <p class="sub"><span>${m.year}</span><span>${m.genres.slice(0, 2).join(", ")}</span></p>
  </article>`;
}
function renderChips() {
  const mk = (list) => list.map(t => `<button class="chip" data-pick="${t.toLowerCase()}" aria-pressed="${S.picks.has(t.toLowerCase())}">${cap(t)}</button>`).join("");
  $("#moodChips").innerHTML = mk(ALL_MOODS);
  $("#genreChips").innerHTML = mk(ALL_GENRES);
  $("#resetBtn").hidden = !hasSignal();
}
function renderTop(r) {
  const el = $("#topPick"), m = r.m, sig = hasSignal();
  el.innerHTML = `<button class="tp-poster" data-open="${m.id}" aria-label="Details for ${m.title}"><img src="${IMG + m.img}" alt="${m.title} poster"></button>
    <div class="tp-info">
      <div class="ring" style="--p:${sig ? r.match : Math.round(m.rating * 10)}"><b>${sig ? r.match + "%" : m.rating}</b></div>
      <p class="tp-label">${sig ? "Your top pick" : "Start here"}</p>
      <h2>${m.title}</h2>
      <p class="reason">${r.reason}</p>
      <div class="tp-actions"><button class="btn" data-open="${m.id}">See details</button>
      <button class="btn ghost" data-save="${m.id}">${S.saved.has(m.id) ? "Saved" : "Add to watchlist"}</button></div>
    </div>`;
}
function renderAll() {
  const recs = recommend();
  renderChips(); renderTop(recs[0]);
  $("#recsNote").textContent = hasSignal() ? "Ranked by how well each film fits your taste." : "Pick a mood above to personalise this list.";
  $("#recsGrid").innerHTML = recs.slice(1, 9).map(r => card(r.m, hasSignal() ? r.match : null)).join("");

  const sv = MOVIES.filter(m => S.saved.has(m.id));
  $("#saved").hidden = !sv.length;
  $("#saved").querySelector("#savedGrid").innerHTML = sv.map(m => card(m)).join("");
  const c = $("#savedCount"); c.hidden = !sv.length; c.textContent = sv.length;

  $("#filters").innerHTML = ["All", ...ALL_GENRES].map(g => `<button class="chip small" data-genre="${g}" aria-pressed="${S.genre === g}">${g}</button>`).join("");
  const q = S.q.toLowerCase();
  const list = MOVIES.filter(m => (S.genre === "All" || m.genres.includes(S.genre)) && m.title.toLowerCase().includes(q));
  $("#allGrid").innerHTML = list.length ? list.map(m => card(m)).join("") : `<p class="empty">No movies match that search.</p>`;
}

function openModal(id) {
  const m = byId(id), liked = S.liked.has(id), passed = S.passed.has(id), saved = S.saved.has(id);
  const r = recommend().find(x => x.m.id === id);
  $("#modal").innerHTML = `<button class="close" data-close aria-label="Close">×</button>
    <img class="m-poster" src="${IMG + m.img}" alt="${m.title} poster">
    <div class="m-body">
      <h2 id="mTitle">${m.title}</h2>
      <p class="sub"><span>${m.year}</span><span>${m.runtime} min</span><span>★ ${m.rating}</span></p>
      <p class="tags">${[...m.genres, ...m.moods].map(t => `<span>${cap(t)}</span>`).join("")}</p>
      <p class="about">${m.about}</p>
      ${r && hasSignal() ? `<p class="reason">${r.match}% match. ${r.reason}.</p>` : ""}
      <div class="tp-actions">
        <button class="btn ${liked ? "on" : ""}" data-like="${id}" aria-pressed="${liked}">${liked ? "Liked" : "I like this"}</button>
        <button class="btn ghost ${passed ? "on" : ""}" data-pass="${id}" aria-pressed="${passed}">${passed ? "Passed" : "Not for me"}</button>
        <button class="btn ghost ${saved ? "on" : ""}" data-save="${id}" aria-pressed="${saved}">${saved ? "In watchlist" : "Save"}</button>
      </div>
      <h3>More like this</h3>
      <div class="more">${similar(m).map(o => `<button data-open="${o.id}" aria-label="${o.title}"><img src="${IMG + o.img}" alt="${o.title}"></button>`).join("")}</div>
    </div>`;
  if (!$("#modal").open) $("#modal").showModal();
}

/* ---- account + database sync ---- */
const API = "../../api/";
let user = null;
async function api(method, path, body) {
  const r = await fetch(API + path, {
    method, headers: { "Content-Type": "application/json", "X-Requested-With": "fetch" },
    body: body ? JSON.stringify(body) : undefined
  });
  if (!r.ok) throw new Error(r.status);
  return r.json();
}
function toggleSaved(id) {
  const adding = !S.saved.has(id);
  adding ? S.saved.add(id) : S.saved.delete(id);
  if (!user) return;
  const req = adding ? api("POST", "watchlist.php", { movie_id: id }) : api("DELETE", "watchlist.php?movie_id=" + id);
  req.catch(() => { adding ? S.saved.delete(id) : S.saved.add(id); save(); renderAll(); }); // undo if the DB call fails
}
async function initSession() {
  try {
    const me = await api("GET", "me.php");
    if (me.loggedIn) {
      user = me;
      const { ids } = await api("GET", "watchlist.php");
      const guestOnly = [...S.saved].filter(id => !ids.includes(id));
      if (guestOnly.length) await api("POST", "watchlist.php", { movie_ids: guestOnly }); // merge guest picks into the account
      S.saved = new Set([...ids, ...guestOnly]);
      store.set("owner", true);
      $("#guestLinks").hidden = true; $("#userLinks").hidden = false; $("#userName").textContent = me.name;
    } else if (store.get("owner", false)) {   // just logged out: don't leave the last user's list on screen
      S.saved.clear(); store.set("owner", false);
    }
    save();
  } catch {}   // no PHP/DB reachable: stay in guest mode
  renderAll();
}

/* ---- events ---- */
const flip = (set, id) => set.has(id) ? set.delete(id) : set.add(id);
document.addEventListener("click", e => {
  const t = e.target.closest("[data-open],[data-save],[data-like],[data-pass],[data-pick],[data-genre],[data-close],#resetBtn");
  if (!t) { if (e.target === $("#modal")) $("#modal").close(); return; }
  const d = t.dataset, inModal = !!t.closest("#modal");
  if (d.open) return openModal(+d.open);
  if (d.close !== undefined) return $("#modal").close();
  if (d.pick) flip(S.picks, d.pick);
  else if (d.genre) S.genre = d.genre;
  else if (d.save) toggleSaved(+d.save);
  else if (d.like) { const id = +d.like; S.passed.delete(id); flip(S.liked, id); }
  else if (d.pass) { const id = +d.pass; S.liked.delete(id); flip(S.passed, id); }
  else if (t.id === "resetBtn") { S.picks.clear(); S.liked.clear(); S.passed.clear(); }
  save(); renderAll();
  if (inModal) openModal(+(d.save || d.like || d.pass));
});
$("#search").addEventListener("input", e => { S.q = e.target.value; renderAll(); });
renderAll();
initSession();
