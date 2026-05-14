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

function speakArabic(text) {
    const audio = _getAudio();
    audio.pause();
    audio.currentTime = 0;
    audio.src = _laravelTtsUrl(text);

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

const STORAGE_KEY = 'arabic_hafalan_progress';
const STREAK_KEY = 'arabic_hafalan_streak';
const QUIZ_KEY = 'arabic_hafalan_quiz_history';

function getProgress() {
    try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
    } catch {
        return {};
    }
}

function saveLetter(id, status) {
    const progress = getProgress();
    progress[id] = {
        status,
        lastSeen: Date.now(),
        attempts: (progress[id]?.attempts || 0) + 1,
        nextReview: Date.now() + (status === 'learned' ? 3 * 86400000 : 86400000),
    };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(progress));
}

function getLetterStatus(id) {
    return getProgress()[id]?.status || 'new';
}

function getStats() {
    const progress = getProgress();
    const learned = Object.values(progress).filter(v => v.status === 'learned').length;
    const review = Object.values(progress).filter(v => v.status === 'review').length;
    const newCount = 28 - learned - review;
    return { total: 28, learned, review, newCount, percentage: Math.round((learned / 28) * 100) };
}

function resetProgress() {
    localStorage.removeItem(STORAGE_KEY);
    localStorage.removeItem(STREAK_KEY);
    localStorage.removeItem(QUIZ_KEY);
}

function saveStreak() {
    const today = new Date().toDateString();
    let streakData = {};
    try {
        streakData = JSON.parse(localStorage.getItem(STREAK_KEY) || '{}');
    } catch {}

    if (streakData.lastDate === today) return;

    const yesterday = new Date(Date.now() - 86400000).toDateString();
    streakData.count = (streakData.lastDate === yesterday) ? (streakData.count || 0) + 1 : 1;
    streakData.lastDate = today;
    streakData.history = streakData.history || [];
    if (!streakData.history.includes(today)) streakData.history.push(today);
    if (streakData.history.length > 30) streakData.history.shift();

    localStorage.setItem(STREAK_KEY, JSON.stringify(streakData));
}

function getStreak() {
    try {
        return JSON.parse(localStorage.getItem(STREAK_KEY) || '{}').count || 0;
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
        const d = new Date(Date.now() - i * 86400000);
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
}

function getQuizHistory() {
    try {
        return JSON.parse(localStorage.getItem(QUIZ_KEY) || '[]');
    } catch {
        return [];
    }
}

function getLettersDueToday() {
    const progress = getProgress();
    const now = Date.now();
    return Object.entries(progress)
        .filter(([, v]) => v.nextReview && v.nextReview <= now)
        .map(([id]) => parseInt(id));
}

function updateSchedule(id, wasCorrect) {
    const progress = getProgress();
    if (progress[id]) {
        progress[id].nextReview = Date.now() + (wasCorrect ? 3 * 86400000 : 86400000);
        localStorage.setItem(STORAGE_KEY, JSON.stringify(progress));
    }
}

function animateCorrect(el) {
    el.classList.add('ring-2', 'ring-sage');
    setTimeout(() => el.classList.remove('ring-2', 'ring-sage'), 600);
}

function animateWrong(el) {
    el.classList.add('animate-shake');
    setTimeout(() => el.classList.remove('animate-shake'), 600);
}

window.hafalanData = function () {
    return {
        progress: getProgress(),
        stats: getStats(),
        streak: getStreak(),
        streakDays: getStreakDays(),
        quizHistory: getQuizHistory(),
        currentQuestion: 0,
        selectedAnswer: null,
        answered: false,
        quizScore: { correct: 0, wrong: 0 },
        wrongAnswers: [],

        markLearned(id) {
            saveLetter(id, 'learned');
            saveStreak();
            this.stats = getStats();
            this.progress = getProgress();
        },
        markReview(id) {
            saveLetter(id, 'review');
            this.stats = getStats();
            this.progress = getProgress();
        },
        getStatus(id) {
            return getLetterStatus(id);
        },
        reset() {
            if (confirm('Reset semua progress hafalan?')) {
                resetProgress();
                this.stats = getStats();
                this.progress = getProgress();
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
