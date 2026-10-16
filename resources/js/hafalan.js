// ── Arabic Audio Engine ──────────────────────────────────────────────────────
// Strategy: Laravel /tts proxy (primary) → Web Speech API (fallback)
// Proxy avoids CORS — server fetches Google TTS and streams audio/mpeg back.

let _audioEl = null;

function _getAudio() {
    if (!_audioEl) {
        _audioEl = new Audio();
        _audioEl.preload = 'none';
    }
    return _audioEl;
}

function _laravelTtsUrl(text) {
    return `/tts?text=${encodeURIComponent(text)}`;
}

function _webSpeechFallback(text) {
    if (!window.speechSynthesis) return;
    window.speechSynthesis.cancel();
    const utt = new SpeechSynthesisUtterance(text);
    utt.lang = 'ar-SA';
    utt.rate = 0.75;
    const voices = window.speechSynthesis.getVoices();
    const arVoice = voices.find(v => v.lang.startsWith('ar'));
    if (arVoice) utt.voice = arVoice;
    window.speechSynthesis.speak(utt);
}

// Rakaman guru → rakaman terbuka (Lingua Libre) → TTS. Peta disediakan oleh pelayan dalam layout.
function audioUrlFor(text) {
    return window.HAFALAN_AUDIO?.[text]?.url || _laravelTtsUrl(text);
}

window.audioUrlFor = audioUrlFor;

function speakArabic(text) {
    const audio = _getAudio();
    audio.pause();
    audio.currentTime = 0;
    audio.src = audioUrlFor(text);

    const playPromise = audio.play();
    if (playPromise !== undefined) {
        playPromise.catch(() => _webSpeechFallback(text));
    }
}

window.speakArabic = speakArabic;

// Preload Web Speech voices in background (used only as fallback)
if (window.speechSynthesis) {
    window.speechSynthesis.getVoices();
    window.speechSynthesis.onvoiceschanged = () => window.speechSynthesis.getVoices();
}

// ── Spaced Repetition (SM-2) ─────────────────────────────────────────────────
// Satu-satunya sumber kemajuan. Sesi hafalan, kuiz, statistik dan halaman utama
// semua baca/tulis di sini supaya angka "dah hafal" mencerminkan ingatan sebenar.

const SM2_KEY = 'arabic_sm2';
const STREAK_KEY = 'arabic_hafalan_streak';
const QUIZ_KEY = 'arabic_hafalan_quiz_history';
const LEGACY_PROGRESS_KEY = 'arabic_hafalan_progress';
const DAY = 86400000;

// Huruf dikira "hafal" selepas berjaya diingat dalam 2 ulang kaji berjarak (selang ≥ 6 hari).
const LEARNED_REPETITIONS = 2;

// Beritahu modul sync bahawa data berubah (modul ditakrif di hujung fail).
function syncSoon() {
    window.HafalanSync?.schedule();
}

function totalLetters() {
    return window.HAFALAN_TOTAL || 28;
}

function srsLoad() {
    try {
        return JSON.parse(localStorage.getItem(SM2_KEY) || '{}');
    } catch {
        return {};
    }
}

function srsSave(data) {
    localStorage.setItem(SM2_KEY, JSON.stringify(data));
    syncSoon();
}

function sm2Update(record, quality) {
    let { repetitions = 0, easiness = 2.5, interval = 1, lapses = 0 } = record || {};
    if (quality < 3) {
        repetitions = 0;
        interval = 1;
        lapses++;
    } else {
        if (repetitions === 0) interval = 1;
        else if (repetitions === 1) interval = 6;
        else interval = Math.round(interval * easiness);
        repetitions++;
    }
    easiness = Math.max(1.3, easiness + 0.1 - (5 - quality) * (0.08 + (5 - quality) * 0.02));
    // firstSeen: bila huruf mula dipelajari — untuk had huruf baharu sehari.
    return { repetitions, easiness, interval, lapses, firstSeen: record?.firstSeen ?? Date.now(), lastReview: Date.now(), nextReview: Date.now() + interval * DAY };
}

const HafalanSRS = {
    load: srsLoad,

    review(id, quality) {
        const data = srsLoad();
        data[id] = sm2Update(data[id], quality);
        srsSave(data);
        return data[id];
    },

    // Jawapan salah dalam kuiz = huruf belum kukuh → jadualkan semula esok.
    lapse(id) {
        return this.review(id, 1);
    },

    previewInterval(id, quality) {
        return sm2Update(srsLoad()[id], quality).interval;
    },

    status(id, data = srsLoad()) {
        const r = data[id];
        if (!r) return 'new';
        return r.repetitions >= LEARNED_REPETITIONS ? 'learned' : 'learning';
    },

    isDue(id, data = srsLoad()) {
        const r = data[id];
        return !!r && r.nextReview <= Date.now();
    },

    stats(total = totalLetters()) {
        const data = srsLoad();
        const records = Object.values(data);
        const learned = records.filter(r => r.repetitions >= LEARNED_REPETITIONS).length;
        const learning = records.length - learned;
        const due = records.filter(r => r.nextReview <= Date.now()).length;
        return {
            total,
            learned,
            review: learning,
            due,
            newCount: Math.max(0, total - records.length),
            percentage: Math.min(100, Math.round((learned / total) * 100)),
        };
    },

    markStudyDay() {
        const today = new Date().toDateString();
        let streakData = {};
        try {
            streakData = JSON.parse(localStorage.getItem(STREAK_KEY) || '{}');
        } catch {}

        if (streakData.lastDate === today) return;

        const yesterday = new Date(Date.now() - DAY).toDateString();
        streakData.count = (streakData.lastDate === yesterday) ? (streakData.count || 0) + 1 : 1;
        streakData.lastDate = today;
        streakData.history = streakData.history || [];
        if (!streakData.history.includes(today)) streakData.history.push(today);
        if (streakData.history.length > 30) streakData.history.shift();

        localStorage.setItem(STREAK_KEY, JSON.stringify(streakData));
        syncSoon();
    },
};

window.HafalanSRS = HafalanSRS;

// ── Pelan 21 hari ────────────────────────────────────────────────────────────
// Kemajuan pelan ikut hari yang SIAP (kuiz hari itu dihabiskan), bukan ikut tarikh —
// jadi terlepas sehari tidak merosakkan pelan; anda sambung dari hari yang belum siap.

const PLAN_KEY = 'arabic_plan';
const PLAN_DONE_KEY = 'arabic_plan_done';

function isoDate(d) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

const HafalanPlan = {
    settings() {
        try {
            return JSON.parse(localStorage.getItem(PLAN_KEY) || 'null');
        } catch {
            return null;
        }
    },

    start(startDate, time) {
        localStorage.setItem(PLAN_KEY, JSON.stringify({ start: startDate, time }));
        syncSoon();
    },

    done() {
        try {
            return JSON.parse(localStorage.getItem(PLAN_DONE_KEY) || '{}');
        } catch {
            return {};
        }
    },

    markDone(day, percentage) {
        const done = this.done();
        const best = Math.max(percentage, done[day]?.percentage ?? 0);
        done[day] = { percentage: best, date: isoDate(new Date()) };
        localStorage.setItem(PLAN_DONE_KEY, JSON.stringify(done));
        syncSoon();
    },

    // Hari yang ada sasaran (ujian) hanya dikira siap bila sasaran dicapai.
    isDone(day, done = this.done()) {
        const d = done[day.day];
        return !!d && (!day.target || d.percentage >= day.target);
    },

    currentDay(days) {
        const done = this.done();
        return days.find(d => !this.isDone(d, done)) || null;
    },

    dateOf(dayNumber) {
        const s = this.settings();
        if (!s) return null;
        const d = new Date(s.start + 'T00:00:00');
        d.setDate(d.getDate() + dayNumber - 1);
        return d;
    },

    // Hari pelan mengikut kalendar (1 = tarikh mula).
    calendarDay() {
        const s = this.settings();
        if (!s) return null;
        const start = new Date(s.start + 'T00:00:00');
        const today = new Date(isoDate(new Date()) + 'T00:00:00');
        return Math.round((today - start) / DAY) + 1;
    },

    reset() {
        localStorage.removeItem(PLAN_KEY);
        localStorage.removeItem(PLAN_DONE_KEY);
    },

    today: () => isoDate(new Date()),
};

window.HafalanPlan = HafalanPlan;

// ── Perakam suara ────────────────────────────────────────────────────────────
// Untuk "Uji sebutan saya" (kekal dalam pelayar) dan Mod Guru (dimuat naik sebagai sebutan rasmi).

const TEACHER_KEY = 'hafalan_teacher_key';
const MAX_RECORD_MS = 4000;

window.voiceRecorder = function (letterId, arabic) {
    return {
        recording: false,
        blob: null,
        myUrl: null,
        error: '',
        audio: window.HAFALAN_AUDIO?.[arabic] || null,
        get hasOfficial() { return this.audio?.source === 'guru'; },
        teacherKey: (() => { try { return localStorage.getItem(TEACHER_KEY) || ''; } catch { return ''; } })(),
        saving: false,
        message: '',
        _recorder: null,
        _timer: null,

        get supported() {
            return !!(navigator.mediaDevices?.getUserMedia && window.MediaRecorder);
        },

        async start() {
            this.error = '';
            this.message = '';
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                const chunks = [];
                this._recorder = new MediaRecorder(stream);
                this._recorder.ondataavailable = (e) => e.data.size && chunks.push(e.data);
                this._recorder.onstop = () => {
                    stream.getTracks().forEach(t => t.stop());
                    this.blob = new Blob(chunks, { type: this._recorder.mimeType || 'audio/webm' });
                    if (this.myUrl) URL.revokeObjectURL(this.myUrl);
                    this.myUrl = URL.createObjectURL(this.blob);
                    this.recording = false;
                    this.playMine();
                };
                this._recorder.start();
                this.recording = true;
                this._timer = setTimeout(() => this.stop(), MAX_RECORD_MS);
            } catch {
                this.error = 'Mikrofon tidak dapat dibuka. Benarkan akses mikrofon untuk laman ini (perlu HTTPS di telefon).';
            }
        },

        stop() {
            clearTimeout(this._timer);
            if (this._recorder?.state === 'recording') this._recorder.stop();
        },

        playExample() {
            speakArabic(arabic);
        },

        playMine() {
            if (this.myUrl) new Audio(this.myUrl).play();
        },

        enableTeacher() {
            const key = prompt('Masukkan kunci Mod Guru (HAFALAN_AUDIO_KEY):');
            if (!key) return;
            try { localStorage.setItem(TEACHER_KEY, key); } catch {}
            this.teacherKey = key;
        },

        async _send(method, body) {
            const res = await fetch(`/api/letters/${letterId}/audio`, {
                method,
                body,
                headers: {
                    'X-Teacher-Key': this.teacherKey,
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                    Accept: 'application/json',
                },
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || 'Gagal (' + res.status + ')');
            return data;
        },

        async saveOfficial() {
            if (!this.blob) return;
            this.saving = true;
            this.message = '';
            try {
                const form = new FormData();
                const ext = this.blob.type.includes('mp4') ? 'm4a' : 'webm';
                form.append('audio', this.blob, `huruf-${letterId}.${ext}`);
                const data = await this._send('POST', form);
                const entry = { url: data.audio_url, source: 'guru', credit: 'Rakaman guru', page: null };
                window.HAFALAN_AUDIO = { ...(window.HAFALAN_AUDIO || {}), [arabic]: entry };
                this.audio = entry;
                this.message = '✓ Disimpan sebagai sebutan rasmi.';
            } catch (e) {
                this.message = e.message;
                if (e.message.includes('Kunci')) {
                    try { localStorage.removeItem(TEACHER_KEY); } catch {}
                    this.teacherKey = '';
                }
            } finally {
                this.saving = false;
            }
        },

        async removeOfficial() {
            if (!confirm('Padam rakaman rasmi dan kembali ke suara komputer?')) return;
            try {
                const data = await this._send('DELETE');
                if (data.fallback) window.HAFALAN_AUDIO[arabic] = data.fallback;
                else delete window.HAFALAN_AUDIO[arabic];
                this.audio = data.fallback || null;
                this.message = 'Rakaman rasmi dipadam.';
            } catch (e) {
                this.message = e.message;
            }
        },
    };
};

// ── Latihan menulis ──────────────────────────────────────────────────────────
// Surih huruf di atas panduan, atau tulis dari ingatan. Markah = gabungan
// liputan (berapa banyak bentuk huruf ditutup) dan ketepatan (berapa banyak dakwat di dalam bentuk).

const PAD_FONT = 'Amiri';
const GUIDE_COLOR = 'rgba(201,168,130,0.35)';
const INK_COLOR = '#3D2C1E';
const PAD_BASELINE = 0.6; // kedudukan garis asas (nisbah tinggi kanvas)

window.writingPad = function (forms) {
    return {
        forms,                 // [{ label, text }]
        formIndex: 0,
        fromMemory: false,
        checked: false,
        score: null,
        _ctx: null,
        _size: 0,
        _drawing: false,
        _strokes: [],

        get text() { return this.forms[this.formIndex].text; },
        get verdict() {
            if (this.score === null) return '';
            if (this.score >= 75) return 'Cantik! Bentuk huruf tepat.';
            if (this.score >= 50) return 'Hampir — perhatikan bahagian yang tidak tertutup.';
            return 'Cuba lagi — surih panduan dahulu, kemudian cuba dari ingatan.';
        },

        async init() {
            const canvas = this.$refs.pad;
            const dpr = window.devicePixelRatio || 1;
            this._size = canvas.clientWidth;
            canvas.width = canvas.height = this._size * dpr;
            this._ctx = canvas.getContext('2d');
            this._ctx.scale(dpr, dpr);
            try { await document.fonts.load(`700 100px ${PAD_FONT}`); } catch {}
            this.redraw();
        },

        _font() {
            return `${Math.round(this._size * 0.55)}px ${PAD_FONT}`;
        },

        _drawGlyph(ctx, style, mode = 'fill', lineWidth = 0) {
            ctx.save();
            ctx.font = this._font();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'alphabetic';
            ctx.direction = 'rtl';
            const y = this._size * PAD_BASELINE;
            if (mode === 'stroke') {
                ctx.strokeStyle = style;
                ctx.lineWidth = lineWidth;
                ctx.lineJoin = 'round';
                ctx.strokeText(this.text, this._size / 2, y);
            }
            ctx.fillStyle = style;
            ctx.fillText(this.text, this._size / 2, y);
            ctx.restore();
        },

        _drawInk(ctx, color, width) {
            ctx.save();
            ctx.strokeStyle = color;
            ctx.lineWidth = width;
            ctx.lineCap = ctx.lineJoin = 'round';
            for (const stroke of this._strokes) {
                ctx.beginPath();
                stroke.forEach((p, i) => (i ? ctx.lineTo(p.x, p.y) : ctx.moveTo(p.x, p.y)));
                if (stroke.length === 1) ctx.lineTo(stroke[0].x + 0.1, stroke[0].y);
                ctx.stroke();
            }
            ctx.restore();
        },

        redraw() {
            const ctx = this._ctx;
            if (!ctx) return;
            ctx.clearRect(0, 0, this._size, this._size);
            // Garis asas membantu letak huruf yang turun di bawah garisan (ر ج ع م ...).
            ctx.save();
            ctx.strokeStyle = '#E2D5C8';
            ctx.setLineDash([6, 6]);
            ctx.beginPath();
            ctx.moveTo(12, this._size * PAD_BASELINE);
            ctx.lineTo(this._size - 12, this._size * PAD_BASELINE);
            ctx.stroke();
            ctx.restore();
            if (!this.fromMemory || this.checked) this._drawGlyph(ctx, GUIDE_COLOR);
            this._drawInk(ctx, INK_COLOR, Math.max(8, this._size * 0.035));
        },

        _point(e) {
            const r = this.$refs.pad.getBoundingClientRect();
            return { x: e.clientX - r.left, y: e.clientY - r.top };
        },
        down(e) {
            if (this.checked) this.clear();
            this._drawing = true;
            this.$refs.pad.setPointerCapture?.(e.pointerId);
            this._strokes.push([this._point(e)]);
            this.redraw();
        },
        move(e) {
            if (!this._drawing) return;
            this._strokes[this._strokes.length - 1].push(this._point(e));
            this.redraw();
        },
        up() {
            this._drawing = false;
        },

        clear() {
            this._strokes = [];
            this.checked = false;
            this.score = null;
            this.redraw();
        },
        undo() {
            this._strokes.pop();
            this.redraw();
        },
        setForm(i) {
            this.formIndex = i;
            this.clear();
        },
        toggleMemory() {
            this.fromMemory = !this.fromMemory;
            this.clear();
        },

        // Bandingkan dakwat dengan bentuk huruf pada kanvas tersembunyi.
        check() {
            if (!this._strokes.length) return;
            const n = Math.round(this._size);
            const layer = (draw) => {
                const c = document.createElement('canvas');
                c.width = c.height = n;
                const ctx = c.getContext('2d');
                draw(ctx);
                return ctx.getImageData(0, 0, n, n).data;
            };
            const tolerance = this._size * 0.06;
            const glyph = layer(ctx => this._drawGlyph(ctx, '#000'));
            const glyphLoose = layer(ctx => this._drawGlyph(ctx, '#000', 'stroke', tolerance));
            const inkThick = layer(ctx => this._drawInk(ctx, '#000', tolerance * 1.6));
            const ink = layer(ctx => this._drawInk(ctx, '#000', Math.max(8, this._size * 0.035)));

            let g = 0, gCovered = 0, k = 0, kInside = 0;
            for (let i = 3; i < glyph.length; i += 4 * 2) {
                if (glyph[i] > 128) { g++; if (inkThick[i] > 128) gCovered++; }
                if (ink[i] > 128) { k++; if (glyphLoose[i] > 128) kInside++; }
            }
            const coverage = g ? gCovered / g : 0;
            const precision = k ? kInside / k : 0;
            this.score = Math.round((0.6 * coverage + 0.4 * precision) * 100);
            this.checked = true;
            this.redraw();
            HafalanSRS.markStudyDay();
        },
    };
};

// ── Nasihat belajar ──────────────────────────────────────────────────────────
// Dipaparkan sebagai peringatan; bertukar setiap hari, ikut konteks halaman.

const TIPS = [
    { icon: '🗣️', title: 'Sebut dengan kuat', tags: ['sesi', 'home'],
      text: 'Sebut nama huruf dengan kuat sebelum balik kad. Mata, mulut dan telinga bekerja bersama — ingatan jadi lebih kuat.' },
    { icon: '📆', title: 'Sedikit, tapi setiap hari', tags: ['home', 'plan'],
      text: '10 minit setiap hari lebih berkesan daripada sejam seminggu sekali. Jangan langkau hari.' },
    { icon: '⏰', title: 'Masa yang tetap', tags: ['home', 'plan'],
      text: 'Belajar pada masa yang sama setiap hari, contohnya selepas Isyak. Tabiat lebih mudah terbentuk bila ada waktu tetap.' },
    { icon: '📱', title: 'Telefon & laptop', tags: ['home', 'plan'],
      text: 'Kemajuan disimpan dalam pelayar ini. Guna lebih dari satu peranti? Aktifkan "Selaraskan Peranti" di halaman Statistik.' },
    { icon: '🤔', title: 'Jujur bila menilai', tags: ['sesi'],
      text: 'Kalau teragak-agak walau sesaat, tekan "Ragu-ragu". Penilaian yang jujur buat jadual ulang kaji lebih tepat.' },
    { icon: '✍️', title: 'Tulis dengan tangan', tags: ['sesi', 'home'],
      text: 'Selepas sesi, tulis setiap huruf hari ini 3 kali di atas kertas sambil sebut namanya. Menulis mengukuhkan bentuk dalam ingatan.' },
    { icon: '👀', title: 'Perhatikan titik', tags: ['sesi', 'kuiz', 'home'],
      text: 'Banyak huruf hanya berbeza pada titik. Setiap kali melihat huruf, tanya: berapa titik, di atas atau di bawah?' },
    { icon: '❌', title: 'Salah itu membantu', tags: ['kuiz'],
      text: 'Jawapan salah bukan kegagalan — otak paling banyak belajar ketika membetulkan kesilapan. Huruf itu akan diulang esok.' },
    { icon: '🔁', title: 'Habiskan yang salah', tags: ['kuiz'],
      text: 'Tekan "Latih huruf yang salah sahaja" sehingga semuanya betul sebelum berhenti.' },
    { icon: '😴', title: 'Tidur mengukuhkan ingatan', tags: ['home', 'plan', 'sesi'],
      text: 'Ingatan dikukuhkan semasa tidur. Belajar pada waktu malam dan ulang kaji keesokan harinya sangat berkesan.' },
    { icon: '🤲', title: 'Mulakan dengan doa', tags: ['home', 'plan'], arabic: 'رَّبِّ زِدْنِي عِلْمًا',
      text: '"Ya Tuhanku, tambahkanlah ilmuku." (Taha: 114). Belajar huruf al-Quran pada usia berapa pun adalah satu kemuliaan.' },
];

const HafalanTips = {
    forTag(tag) {
        return tag ? TIPS.filter(t => t.tags.includes(tag)) : TIPS;
    },

    // Nasihat hari ini; offset untuk butang "nasihat lain".
    pick(tag, offset = 0) {
        const list = this.forTag(tag);
        const dayNumber = Math.floor(Date.now() / DAY);
        return list[(dayNumber + offset) % list.length];
    },
};

window.HafalanTips = HafalanTips;

function resetProgress() {
    // Reset hanya peranti ini — putuskan sync supaya data lama tidak ditarik semula.
    window.HafalanSync?.unlink();
    HafalanPlan.reset();
    localStorage.removeItem(SM2_KEY);
    localStorage.removeItem(LEGACY_PROGRESS_KEY);
    localStorage.removeItem(STREAK_KEY);
    localStorage.removeItem(QUIZ_KEY);
    localStorage.removeItem('arabic_speed');
    localStorage.removeItem('arabic_sessions');
}

function getStreak() {
    try {
        const streakData = JSON.parse(localStorage.getItem(STREAK_KEY) || '{}');
        const yesterday = new Date(Date.now() - DAY).toDateString();
        const alive = streakData.lastDate === new Date().toDateString() || streakData.lastDate === yesterday;
        return alive ? (streakData.count || 0) : 0;
    } catch {
        return 0;
    }
}

function getStreakDays() {
    let streakData = {};
    try {
        streakData = JSON.parse(localStorage.getItem(STREAK_KEY) || '{}');
    } catch {}
    const history = streakData.history || [];
    const days = [];
    for (let i = 6; i >= 0; i--) {
        const d = new Date(Date.now() - i * DAY);
        days.push({ date: d.toLocaleDateString('ms-MY', { weekday: 'short' }), active: history.includes(d.toDateString()) });
    }
    return days;
}

function saveQuizResult(result) {
    let history = [];
    try {
        history = JSON.parse(localStorage.getItem(QUIZ_KEY) || '[]');
    } catch {}
    history.push({ ...result, date: Date.now() });
    if (history.length > 10) history.shift();
    localStorage.setItem(QUIZ_KEY, JSON.stringify(history));
    syncSoon();
}

function getQuizHistory() {
    try {
        return JSON.parse(localStorage.getItem(QUIZ_KEY) || '[]');
    } catch {
        return [];
    }
}

window.hafalanData = function () {
    return {
        sm2: srsLoad(),
        stats: HafalanSRS.stats(),
        streak: getStreak(),
        streakDays: getStreakDays(),
        quizHistory: getQuizHistory(),

        getStatus(id) {
            return HafalanSRS.status(id, this.sm2);
        },
        reset() {
            if (confirm('Reset semua progress hafalan?')) {
                resetProgress();
                this.sm2 = {};
                this.stats = HafalanSRS.stats();
                this.streak = 0;
                this.streakDays = getStreakDays();
                this.quizHistory = [];
            }
        },
        saveQuiz(result) {
            saveQuizResult(result);
            this.quizHistory = getQuizHistory();
        },
    };
};

// ── Selaras peranti ──────────────────────────────────────────────────────────
// Kod rahsia 12 aksara menghubungkan peranti. Setiap peranti tarik data pelayan,
// gabung dengan data sendiri (tiada kemajuan hilang), kemudian hantar hasil gabungan.

const SYNC_CODE_KEY = 'hafalan_sync_code';
const SYNC_AT_KEY = 'hafalan_sync_at';
const SYNC_KEYS = [SM2_KEY, STREAK_KEY, QUIZ_KEY, PLAN_KEY, PLAN_DONE_KEY, 'arabic_baris', 'arabic_speed', 'arabic_sessions'];

function readJson(key) {
    try { return JSON.parse(localStorage.getItem(key) || 'null'); } catch { return null; }
}

function laterRecord(a, b) {
    const t = r => r?.lastReview ?? (r?.nextReview ?? 0) - (r?.interval ?? 0) * DAY;
    return t(b) > t(a) ? b : a;
}

const mergers = {
    // Setiap huruf: ambil rekod yang paling baru diulang kaji.
    [SM2_KEY]: (a, b) => {
        const out = { ...a };
        for (const [id, r] of Object.entries(b)) out[id] = out[id] ? laterRecord(out[id], r) : r;
        return out;
    },
    // Setiap hari pelan / pelajaran baris: ambil markah terbaik.
    [PLAN_DONE_KEY]: (a, b) => {
        const out = { ...a };
        for (const [day, d] of Object.entries(b)) {
            if (!out[day] || d.percentage > out[day].percentage) out[day] = d;
        }
        return out;
    },
    arabic_baris: (a, b) => {
        const out = { ...a };
        for (const [k, v] of Object.entries(b)) out[k] = Math.max(v, out[k] ?? 0);
        return out;
    },
    [STREAK_KEY]: (a, b) => {
        const history = [...new Set([...(a.history || []), ...(b.history || [])])]
            .sort((x, y) => new Date(x) - new Date(y)).slice(-30);
        const latest = new Date(a.lastDate || 0) >= new Date(b.lastDate || 0) ? a : b;
        return { ...latest, count: Math.max(a.count || 0, b.count || 0), history };
    },
    // Log kelajuan: gabung ikut hari & huruf (ambil senarai yang lebih panjang).
    arabic_speed: (a, b) => {
        const out = { ...a };
        for (const [day, letters] of Object.entries(b)) {
            out[day] = { ...(out[day] || {}) };
            for (const [id, list] of Object.entries(letters)) {
                if (!out[day][id] || list.length > out[day][id].length) out[day][id] = list;
            }
        }
        return out;
    },
    arabic_sessions: (a, b) => {
        const out = { ...a };
        for (const [day, n] of Object.entries(b)) out[day] = Math.max(n, out[day] ?? 0);
        return out;
    },
    [QUIZ_KEY]: (a, b) => {
        const byDate = new Map([...a, ...b].map(q => [q.date, q]));
        return [...byDate.values()].sort((x, y) => x.date - y.date).slice(-10);
    },
    // Tetapan pelan: kekalkan yang ada di peranti ini.
    [PLAN_KEY]: (a) => a,
};

function mergeSnapshots(local, remote) {
    const merged = {};
    for (const key of SYNC_KEYS) {
        const a = local[key], b = remote[key];
        merged[key] = a == null ? b ?? null : b == null ? a : mergers[key](a, b);
    }
    return merged;
}

const HafalanSync = {
    _timer: null,
    _busy: false,

    code() {
        try { return localStorage.getItem(SYNC_CODE_KEY); } catch { return null; }
    },
    lastSynced() {
        try { return Number(localStorage.getItem(SYNC_AT_KEY)) || null; } catch { return null; }
    },
    formatCode(code) {
        return (code || '').replace(/(.{4})(?=.)/g, '$1-');
    },

    snapshot() {
        return Object.fromEntries(SYNC_KEYS.map(k => [k, readJson(k)]));
    },

    async _request(method, url, body) {
        const res = await fetch(url, {
            method,
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
            },
            body: body ? JSON.stringify(body) : undefined,
            keepalive: method === 'PUT',
        });
        if (!res.ok) {
            const err = new Error(res.status === 404 ? 'Kod tidak dijumpai.' : 'Sync gagal (' + res.status + ').');
            err.status = res.status;
            throw err;
        }
        return res.json();
    },

    async create() {
        const { code } = await this._request('POST', '/api/sync');
        localStorage.setItem(SYNC_CODE_KEY, code);
        await this.syncNow();
        return code;
    },

    async link(rawCode) {
        const code = rawCode.toUpperCase().replace(/[^A-Z0-9]/g, '');
        await this._request('GET', `/api/sync/${code}`); // pastikan kod wujud
        localStorage.setItem(SYNC_CODE_KEY, code);
        return this.syncNow();
    },

    unlink() {
        try {
            localStorage.removeItem(SYNC_CODE_KEY);
            localStorage.removeItem(SYNC_AT_KEY);
        } catch {}
    },

    // Pulangkan true jika data peranti ini berubah hasil gabungan.
    async syncNow() {
        const code = this.code();
        if (!code || this._busy || !navigator.onLine) return false;
        this._busy = true;
        try {
            const local = this.snapshot();
            const { data: remote } = await this._request('GET', `/api/sync/${code}`);
            const merged = mergeSnapshots(local, remote || {});

            const changed = JSON.stringify(merged) !== JSON.stringify(local);
            if (changed) {
                for (const [k, v] of Object.entries(merged)) {
                    if (v == null) localStorage.removeItem(k);
                    else localStorage.setItem(k, JSON.stringify(v));
                }
            }
            const remoteNorm = Object.fromEntries(SYNC_KEYS.map(k => [k, remote?.[k] ?? null]));
            if (JSON.stringify(merged) !== JSON.stringify(remoteNorm)) {
                await this._request('PUT', `/api/sync/${code}`, { data: merged });
            }
            localStorage.setItem(SYNC_AT_KEY, String(Date.now()));
            return changed;
        } catch (e) {
            if (e.status === 404) this.unlink();
            throw e;
        } finally {
            this._busy = false;
        }
    },

    schedule() {
        if (!this.code()) return;
        clearTimeout(this._timer);
        this._timer = setTimeout(() => this.syncNow().catch(() => {}), 2000);
    },
};

window.HafalanSync = HafalanSync;

// Bila halaman dibuka: tarik kemajuan peranti lain. Jika ada perubahan, muat semula sekali
// supaya paparan guna data terkini.
if (HafalanSync.code()) {
    HafalanSync.syncNow().then((changed) => {
        const guard = 'hafalan_sync_reloaded';
        if (changed && !sessionStorage.getItem(guard)) {
            sessionStorage.setItem(guard, '1');
            location.reload();
        } else {
            sessionStorage.removeItem(guard);
        }
    }).catch(() => {});

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') HafalanSync.syncNow().catch(() => {});
    });
}
