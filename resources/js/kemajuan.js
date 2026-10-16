// ── Kemajuan yang boleh dilihat ──────────────────────────────────────────────
// Untuk pelajar yang berhenti bila "tak nampak kemajuan": tahap setiap huruf,
// sasaran mingguan (bukan streak yang menghukum), kiraan sesi, dan bukti kelajuan.

const DAY = 86400000;
const SPEED_KEY = 'arabic_speed';      // { 'YYYY-MM-DD': { letterId: [ms, …] } }
const SESSIONS_KEY = 'arabic_sessions'; // { 'YYYY-MM-DD': bilangan sesi kilat }
const STREAK_KEY = 'arabic_hafalan_streak';
export const WEEKLY_TARGET = 5;

function iso(d = new Date()) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

function read(key, fallback) {
    try { return JSON.parse(localStorage.getItem(key) || 'null') ?? fallback; } catch { return fallback; }
}

function write(key, value) {
    try { localStorage.setItem(key, JSON.stringify(value)); } catch {}
    window.HafalanSync?.schedule();
}

// Simpan hanya N hari terakhir.
function prune(obj, days) {
    const cutoff = iso(new Date(Date.now() - days * DAY));
    return Object.fromEntries(Object.entries(obj).filter(([d]) => d >= cutoff));
}

// ── Tahap huruf ──────────────────────────────────────────────────────────────

export const LEVELS = {
    baru:  { label: 'Baru',  color: '#E2D5C8', text: '#8B6F5E', rank: 0 },
    kenal: { label: 'Kenal', color: '#F0D9A8', text: '#7A5B1E', rank: 1 },
    ingat: { label: 'Ingat', color: '#A9C7AC', text: '#2F5233', rank: 2 },
    hafal: { label: 'Hafal', color: '#5E8B63', text: '#FFFFFF', rank: 3 },
};

/** baru → kenal (pernah belajar) → ingat (2 ulang kaji berjaya) → hafal (selang ≥ 14 hari). */
export function levelOf(record) {
    if (!record) return 'baru';
    if ((record.interval ?? 0) >= 14) return 'hafal';
    if ((record.repetitions ?? 0) >= 2) return 'ingat';
    return 'kenal';
}

export function levels(sm2 = window.HafalanSRS.load()) {
    const out = {};
    for (const [id, r] of Object.entries(sm2)) out[id] = levelOf(r);
    return out;
}

// ── Sasaran mingguan ─────────────────────────────────────────────────────────

/** 7 hari minggu ini (Isnin–Ahad) dan sama ada setiap hari ada belajar. */
export function week() {
    const history = new Set(read(STREAK_KEY, {}).history || []);
    const now = new Date();
    const monday = new Date(now);
    monday.setHours(0, 0, 0, 0);
    monday.setDate(monday.getDate() - ((monday.getDay() + 6) % 7));

    const days = Array.from({ length: 7 }, (_, i) => {
        const d = new Date(monday.getTime() + i * DAY);
        return {
            label: ['I', 'S', 'R', 'K', 'J', 'S', 'A'][i],
            done: history.has(d.toDateString()),
            today: d.toDateString() === now.toDateString(),
            future: d > now,
        };
    });
    const done = days.filter(d => d.done).length;
    return { days, done, target: WEEKLY_TARGET, reached: done >= WEEKLY_TARGET };
}

// ── Sesi hari ini ────────────────────────────────────────────────────────────

export function sessionsToday() {
    return read(SESSIONS_KEY, {})[iso()] || 0;
}

export function addSession() {
    const s = prune(read(SESSIONS_KEY, {}), 30);
    s[iso()] = (s[iso()] || 0) + 1;
    write(SESSIONS_KEY, s);
    return s[iso()];
}

// ── Kelajuan mengingat ───────────────────────────────────────────────────────

export function recordSpeed(letterId, ms) {
    if (!(ms > 200 && ms < 30000)) return; // abaikan ketukan tersilap / tinggal skrin
    const log = prune(read(SPEED_KEY, {}), 28);
    const day = (log[iso()] ||= {});
    const list = (day[letterId] ||= []);
    list.push(Math.round(ms));
    if (list.length > 20) list.shift();
    write(SPEED_KEY, log);
}

function averages(fromDaysAgo, toDaysAgo) {
    const from = iso(new Date(Date.now() - fromDaysAgo * DAY));
    const to = iso(new Date(Date.now() - toDaysAgo * DAY));
    const sums = {};
    for (const [d, letters] of Object.entries(read(SPEED_KEY, {}))) {
        if (d < from || d > to) continue;
        for (const [id, list] of Object.entries(letters)) {
            const s = (sums[id] ||= [0, 0]);
            list.forEach(ms => { s[0] += ms; s[1]++; });
        }
    }
    return Object.fromEntries(Object.entries(sums).map(([id, [t, n]]) => [id, t / n]));
}

/**
 * Bukti kemajuan: huruf yang paling banyak bertambah pantas (7 hari ini vs sebelumnya),
 * dan purata keseluruhan. Pulangkan null jika data belum cukup.
 */
export function speedStats() {
    const recent = averages(6, 0);
    const before = averages(27, 7);
    let best = null;
    for (const [id, now] of Object.entries(recent)) {
        const then = before[id];
        if (!then) continue;
        const gain = (then - now) / then;
        if (gain >= 0.15 && (!best || gain > best.gain)) best = { id: Number(id), now, then, gain };
    }
    const avg = (o) => { const v = Object.values(o); return v.length ? v.reduce((a, b) => a + b, 0) / v.length : null; };
    return { best, recentAvg: avg(recent), beforeAvg: avg(before), recent, before };
}

export const SPEED_SYNC_KEY = SPEED_KEY;
export const SESSIONS_SYNC_KEY = SESSIONS_KEY;

window.Kemajuan = { LEVELS, WEEKLY_TARGET, levelOf, levels, week, sessionsToday, addSession, recordSpeed, speedStats };
