// Medan glyph yang mengikuti kursor di seluruh beranda (gaya giga.ai/cli). Grid menempel ke dokumen,
// jadi glyph ikut bergulir bersama konten. Hanya aktif untuk mouse; sengaja tetap jalan walau
// sistem meminta reduced motion (permintaan pemilik produk).

const CELL_W = 18;
const CELL_H = 22;
const RADIUS = 190;
const GLYPHS = '/-|0138*+.#<>=$';
const FONT = '400 13px "JetBrains Mono", monospace';
const COLOR = '#F4B3D3';
const MAX_ALPHA = 0.2;
const FOLLOW = 0.18;
const RISE = 0.12;
const FALL = 0.06;
const ACTIVE_MS = 900;
const SLEEP_BELOW = 0.003;
const MAX_DPR = 2;

export function initGlyphField(canvas) {
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
    let field = null;

    const sync = () => {
        const wanted = finePointer.matches;
        if (wanted && !field) {
            field = startField(canvas);
        } else if (!wanted && field) {
            field.destroy();
            field = null;
        }
    };

    finePointer.addEventListener('change', sync);
    sync();
}

// Hash deterministik per sel: separuh sel selalu kosong, sisanya punya glyph tetap.
function cellGlyph(col, row) {
    let h = Math.imul(col, 0x27d4eb2d) ^ Math.imul(row, 0x165667b1);
    h = Math.imul(h ^ (h >>> 15), 0x85ebca6b);
    h = (h ^ (h >>> 13)) >>> 0;
    return h & 1 ? '' : GLYPHS[(h >>> 1) % GLYPHS.length];
}

function startField(canvas) {
    const ctx = canvas.getContext('2d');
    const events = new AbortController();
    const listen = { passive: true, signal: events.signal };

    let width = 0;
    let height = 0;
    let mouseX = 0;
    let mouseY = 0;
    let circleX = 0;
    let circleY = 0;
    let intensity = 0;
    let lastMove = -Infinity;
    let raf = 0;

    function resize() {
        const dpr = Math.min(window.devicePixelRatio || 1, MAX_DPR);
        width = canvas.clientWidth;
        height = canvas.clientHeight;
        canvas.width = Math.round(width * dpr);
        canvas.height = Math.round(height * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        ctx.font = FONT;
        ctx.fillStyle = COLOR;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        wake();
    }

    function wake() {
        if (!raf && intensity > 0 && !events.signal.aborted) raf = requestAnimationFrame(frame);
    }

    function frame(now) {
        raf = 0;
        const moving = now - lastMove < ACTIVE_MS;
        intensity += ((moving ? 1 : 0) - intensity) * (moving ? RISE : FALL);
        circleX += (mouseX + window.scrollX - circleX) * FOLLOW;
        circleY += (mouseY + window.scrollY - circleY) * FOLLOW;

        ctx.clearRect(0, 0, width, height);
        if (!moving && intensity < SLEEP_BELOW) {
            intensity = 0;
            return;
        }
        draw();
        raf = requestAnimationFrame(frame);
    }

    function draw() {
        const scrollX = window.scrollX;
        const scrollY = window.scrollY;
        const firstCol = Math.floor((circleX - RADIUS) / CELL_W);
        const lastCol = Math.ceil((circleX + RADIUS) / CELL_W);
        const firstRow = Math.floor((circleY - RADIUS) / CELL_H);
        const lastRow = Math.ceil((circleY + RADIUS) / CELL_H);

        for (let row = firstRow; row <= lastRow; row++) {
            const y = row * CELL_H + CELL_H / 2;
            for (let col = firstCol; col <= lastCol; col++) {
                const glyph = cellGlyph(col, row);
                if (!glyph) continue;
                const x = col * CELL_W + CELL_W / 2;
                const d = Math.hypot(x - circleX, y - circleY);
                if (d >= RADIUS) continue;
                const falloff = 1 - d / RADIUS;
                ctx.globalAlpha = MAX_ALPHA * intensity * falloff * falloff;
                ctx.fillText(glyph, x - scrollX, y - scrollY);
            }
        }
    }

    window.addEventListener('pointermove', (e) => {
        if (e.pointerType !== 'mouse') return;
        mouseX = e.clientX;
        mouseY = e.clientY;
        if (intensity === 0) {
            circleX = mouseX + window.scrollX;
            circleY = mouseY + window.scrollY;
            intensity = SLEEP_BELOW;
        }
        lastMove = performance.now();
        wake();
    }, listen);
    // Kursor keluar jendela: langsung mulai memudar.
    document.addEventListener('pointerout', (e) => {
        if (!e.relatedTarget && e.pointerType === 'mouse') lastMove = -Infinity;
    }, listen);
    window.addEventListener('scroll', wake, listen);
    window.addEventListener('resize', resize, listen);

    canvas.hidden = false;
    resize();
    document.fonts?.load(FONT).then(wake);

    return {
        destroy() {
            events.abort();
            cancelAnimationFrame(raf);
            raf = 0;
            intensity = 0;
            canvas.hidden = true;
            canvas.width = 0;
            canvas.height = 0;
        },
    };
}
