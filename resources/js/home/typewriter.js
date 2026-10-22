// Typewriter pada judul hero beranda. Teks penuh tetap ada di h1 tapi disembunyikan (visibility),
// lalu tiap huruf dimunculkan di tempatnya sendiri; caret ada di lapisan overlay. Ukuran h1 tidak
// pernah berubah dan huruf tidak bergeser. (Lapisan teks terpisah bisa terbungkus beda karena
// pembulatan subpiksel, jadi huruf sengaja dipecah di dalam teks cadangan itu sendiri.)

const START_DELAY_MS = 400;
const TYPE_MS = 80;
const TYPE_JITTER_MS = 30;
const DELETE_MS = 55;
const HOLD_MS = 5000;
const CYCLE_A_PAUSE_MS = 300;
const CYCLE_B_PAUSE_MS = 600;
const REVEAL_STAGGER_MS = 120;
const CARET_GAP_EM = 0.04;
const CARET_HEIGHT_EM = 0.8;
// A: hapus kata terakhir lalu ketik ulang. B: hapus semua lalu ketik ulang seperti saat dimuat.
const PATTERN = ['A', 'A', 'B'];

const HALT = Symbol('halt');

export function initTypewriter(heading) {
    const layer = heading.querySelector('.tw-layer');
    const sizer = heading.querySelector('.tw-sizer');
    const text = sizer.textContent;
    const keep = text.lastIndexOf(' ') + 1;
    const reveals = [...document.querySelectorAll('[data-tw-reveal]')];

    const chars = [...text].map((ch) => {
        const span = document.createElement('span');
        span.className = 'tw-char';
        span.textContent = ch;
        return span;
    });
    const caret = document.createElement('span');
    caret.className = 'tw-caret';
    sizer.replaceChildren(...chars);
    layer.replaceChildren(caret);
    heading.classList.add('tw-on');
    document.documentElement.setAttribute('data-tw-ready', '');

    let shown = 0;
    let baseline = 0;
    let introDone = false;
    let cycle = 0;
    let running = false;
    let inView = false;
    let pending = null;

    const resizeObserver = new ResizeObserver(measure);
    const viewObserver = new IntersectionObserver(([entry]) => {
        inView = entry.isIntersecting;
        sync();
    });

    // Jarak dari tepi atas kotak huruf ke baseline, supaya kaki caret duduk di baseline.
    function measure() {
        const probe = document.createElement('span');
        probe.style.cssText = 'display:inline-block;width:0;height:0';
        sizer.insertBefore(probe, chars[0]);
        baseline = probe.getBoundingClientRect().bottom - chars[0].getBoundingClientRect().top;
        probe.remove();
        render();
    }

    // Caret berdiri di posisi huruf berikutnya, jadi saat baris terbungkus (375px)
    // caret pindah ke awal baris kedua sebelum "Rent" diketik.
    function render() {
        chars.forEach((span, i) => span.classList.toggle('is-on', i < shown));

        const box = layer.getBoundingClientRect();
        const em = parseFloat(getComputedStyle(heading).fontSize);
        const next = shown < chars.length ? chars[shown].getBoundingClientRect() : null;
        const rect = next ?? chars[chars.length - 1].getBoundingClientRect();
        const x = (next ? rect.left : rect.right) + (shown > 0 ? CARET_GAP_EM * em : 0);
        const y = rect.top + baseline - CARET_HEIGHT_EM * em;
        caret.style.transform = `translate(${x - box.left}px, ${y - box.top}px)`;
    }

    function setBusy(busy) {
        caret.classList.toggle('is-busy', busy);
    }

    function sleep(ms) {
        return new Promise((resolve, reject) => {
            pending = {
                timer: setTimeout(() => {
                    pending = null;
                    resolve();
                }, ms),
                reject,
            };
        });
    }

    function halt() {
        if (!pending) return;
        clearTimeout(pending.timer);
        pending.reject(HALT);
        pending = null;
    }

    async function typeTo(count) {
        setBusy(true);
        while (shown < count) {
            await sleep(TYPE_MS + (Math.random() * 2 - 1) * TYPE_JITTER_MS);
            shown++;
            render();
        }
        setBusy(false);
    }

    async function deleteTo(count) {
        setBusy(true);
        while (shown > count) {
            await sleep(DELETE_MS);
            shown--;
            render();
        }
        setBusy(false);
    }

    function finishIntro() {
        if (introDone) return;
        introDone = true;
        reveals.forEach((el, i) => {
            el.style.setProperty('--tw-delay', `${i * REVEAL_STAGGER_MS}ms`);
            el.classList.add('is-revealed');
        });
    }

    async function run() {
        try {
            if (!introDone) {
                await sleep(START_DELAY_MS);
                await typeTo(chars.length);
                finishIntro();
            }
            for (;;) {
                await sleep(HOLD_MS);
                const full = PATTERN[cycle % PATTERN.length] === 'B';
                await deleteTo(full ? 0 : keep);
                await sleep(full ? CYCLE_B_PAUSE_MS : CYCLE_A_PAUSE_MS);
                await typeTo(chars.length);
                cycle++;
            }
        } catch (error) {
            if (error !== HALT) throw error;
        }
    }

    // Jeda selalu berhenti di teks penuh; lanjut lagi dari teks penuh dengan jeda tahan 5 detik.
    function showFull() {
        halt();
        running = false;
        shown = chars.length;
        setBusy(false);
        render();
        finishIntro();
    }

    function sync() {
        const active = !document.hidden && inView;
        if (active && !running) {
            running = true;
            run();
        } else if (!active && (running || !introDone)) {
            showFull();
        }
    }

    measure();
    resizeObserver.observe(heading);
    document.fonts?.ready.then(measure);

    // Sengaja tetap jalan walau sistem meminta reduced motion (permintaan pemilik produk).
    viewObserver.observe(heading.closest('[data-hero]') ?? heading);
    document.addEventListener('visibilitychange', sync);
    if (document.hidden) sync();
}
