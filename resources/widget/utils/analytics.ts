/* resources/widget/utils/analytics.ts */

export type ProviderGA4 = { enabled: boolean; measurementId: string };
export type ProviderFB  = { enabled: boolean; pixelId: string };
export type ProviderTT  = { enabled: boolean; pixelId: string };
export type ProviderGTM = { enabled: boolean; containerId: string };

export interface AnalyticsProviders {
    ga4?: ProviderGA4;
    fb?: ProviderFB;
    tt?: ProviderTT;
    gtm?: ProviderGTM;
}

export interface AnalyticsScripts {
    head?: string;     // довільний HTML/JS (обережно)
    bodyEnd?: string;  // довільний HTML/JS (обережно)
}

export interface AnalyticsEvents {
    impression?: string;
    start?: string;
    step?: string;
    leadView?: string;
    leadSubmit?: string;
}

export interface AnalyticsConfig {
    enabled: boolean;
    providers?: AnalyticsProviders;
    scripts?: AnalyticsScripts;
    events?: AnalyticsEvents;
    debug?: boolean;
}

/* ---------------- Internal state ---------------- */

let cfg: AnalyticsConfig | null = null;
let inited = false;

const DEFAULT_EVENTS: Required<AnalyticsEvents> = {
    impression: 'quiz_impression',
    start: 'quiz_start',
    step: 'quiz_step_view',
    leadView: 'lead_view',
    leadSubmit: 'lead_submit',
};

const win = (typeof window !== 'undefined' ? window : undefined) as any;

/* ---------------- Small utils ---------------- */

function log(...args: any[]) {
    if (cfg?.debug) console.log('[analytics]', ...args);
}

function onceFlag(key: string): boolean {
    const k = `__analytics_flag_${key}`;
    if (!win) return false;
    if (win[k]) return true;
    win[k] = true;
    return false;
}

function ensureScript(src: string, id?: string): Promise<void> {
    if (typeof document === 'undefined') return Promise.resolve();
    return new Promise<void>((resolve, reject) => {
        if (id && document.getElementById(id)) return resolve();
        const s = document.createElement('script');
        s.src = src;
        s.async = true;
        if (id) s.id = id;
        s.onload = () => resolve();
        s.onerror = () => reject(new Error(`Failed to load ${src}`));
        document.head.appendChild(s);
    });
}

/**
 * Безпечно інжектимо HTML зі <script>:
 * скрипти пересоздаємо, щоб вони виконались.
 */
function injectHTML(where: 'head'|'bodyEnd', html?: string) {
    if (!html || typeof document === 'undefined') return;

    const target = where === 'head' ? document.head : document.body;
    const tpl = document.createElement('template');
    tpl.innerHTML = html;

    const nodes = Array.from(tpl.content.childNodes);
    for (const node of nodes) {
        if (node.nodeName.toLowerCase() === 'script') {
            const old = node as HTMLScriptElement;
            const s = document.createElement('script');
            // перенесемо атрибути
            Array.from(old.attributes).forEach(attr => s.setAttribute(attr.name, attr.value));
            s.text = old.text || '';
            target.appendChild(s);
        } else {
            target.appendChild(node);
        }
    }
}

/* ---------------- Providers init ---------------- */

async function initGA4(p: ProviderGA4) {
    if (!p.enabled || !p.measurementId) return;
    if (onceFlag('ga4')) { log('GA4 already inited'); return; }

    win.dataLayer = win.dataLayer || [];
    function gtag(...args: any[]) { win.dataLayer.push(args); }
    win.gtag = gtag;

    await ensureScript(`https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(p.measurementId)}`);
    gtag('js', new Date());
    gtag('config', p.measurementId, { send_page_view: false });
    log('GA4 inited', p.measurementId);
}

async function initFB(p: ProviderFB) {
    if (!p.enabled || !p.pixelId) return;
    if (onceFlag('fb')) { log('FB Pixel already inited'); return; }

    (function (f: any, b: any, e: any, v: string, n?: any, t?: any, s?: any) {
        if (f.fbq) return;
        n = f.fbq = function () {
            (n!.callMethod) ? n!.callMethod.apply(n, arguments) : n!.queue.push(arguments);
        };
        if (!f._fbq) f._fbq = n;
        n.push = n;
        n.loaded = true;
        n.version = '2.0';
        n.queue = [];
        t = b.createElement(e);
        t.async = true;
        t.src = v;
        s = b.getElementsByTagName(e)[0];
        s?.parentNode?.insertBefore(t, s);
    })(win, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

    win.fbq('init', p.pixelId);
    // PageView руками — бо SPA
    try { win.fbq('track', 'PageView'); } catch {}
    log('FB Pixel inited', p.pixelId);
}

async function initTT(p: ProviderTT) {
    if (!p.enabled || !p.pixelId) return;
    if (onceFlag('tt')) { log('TikTok Pixel already inited'); return; }

    (function (w: any, d: Document, t: string) {
        w[t] = w[t] || [];
        const ttq = w[t];

        if (ttq.invoked) {
            console.error('ttq.js duplicated.');
            return;
        }

        ttq.invoked = true;
        ttq.methods = [
            'page','track','identify','instances','debug','on','off','once','ready',
            'alias','group','enableCookie','disableCookie'
        ];

        ttq.setAndDefer = function (obj: any, method: string) {
            obj[method] = function () {
                obj.push([method].concat(Array.prototype.slice.call(arguments, 0)));
            };
        };

        ttq.methods.forEach((m: string) => ttq.setAndDefer(ttq, m));

        ttq.instance = function (id: string) {
            const inst = ttq._i[id] || [];
            ttq.methods.forEach((m: string) => ttq.setAndDefer(inst, m));
            return inst;
        };

        ttq.load = function (e: string, n?: any) {
            const i = 'https://analytics.tiktok.com/i18n/pixel/events.js';
            ttq._i = ttq._i || {};
            ttq._i[e] = [];
            ttq._i[e]._u = i;
            ttq._t = ttq._t || {};
            ttq._t[e] = +new Date();
            ttq._o = ttq._o || {};
            ttq._o[e] = n || {};
            const o = d.createElement('script');
            o.type = 'text/javascript';
            o.async = true;
            o.src = i + '?sdkid=' + e + '&lib=' + t;
            const a = d.getElementsByTagName('script')[0];
            a?.parentNode?.insertBefore(o, a);
        };
    })(window as any, document, 'ttq');

    (win as any).ttq.load(p.pixelId);
    try { (win as any).ttq.page(); } catch {}
    log('TikTok Pixel inited', p.pixelId);
}

async function initGTM(p: ProviderGTM) {
    if (!p.enabled || !p.containerId) return;
    if (onceFlag('gtm')) { log('GTM already inited'); return; }

    win.dataLayer = win.dataLayer || [];
    win.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });

    const id = p.containerId;
    const src = `https://www.googletagmanager.com/gtm.js?id=${encodeURIComponent(id)}`;
    await ensureScript(src, 'gtm-js');
    log('GTM inited', id);

    // Якщо хочеш <noscript> iframe, додай у blade/layout — у SPA це зазвичай не потрібно.
}

/* ---------------- Public API: init ---------------- */

export async function initAnalytics(config: AnalyticsConfig) {
    cfg = config;
    if (!cfg?.enabled || inited) {
        log('Analytics disabled or already inited');
        return;
    }

    console.log('Loaded quiz:', config)

    // Кастомні скрипти (обережно з безпекою — додай свою санітизацію за потреби)
    injectHTML('head', cfg.scripts?.head);
    injectHTML('bodyEnd', cfg.scripts?.bodyEnd);

    // Провайдери
    const p = cfg.providers || {};
    await Promise.all([
        initGA4(p.ga4 ?? { enabled: false, measurementId: '' }),
        initFB(p.fb ?? { enabled: false, pixelId: '' }),
        initTT(p.tt ?? { enabled: false, pixelId: '' }),
        initGTM(p.gtm ?? { enabled: false, containerId: '' }),
    ]);

    inited = true;
    log('Analytics initialized');
}

/* ---------------- Helpers to send events ---------------- */

function evName(key: keyof AnalyticsEvents): string {
    return (cfg?.events?.[key] || DEFAULT_EVENTS[key])!;
}

function sendAll(event: string, params?: Record<string, any>) {
    if (!cfg?.enabled) return;

    // GA4
    try { win?.gtag?.('event', event, params || {}); } catch {}

    // FB
    try {
        // стандартні: PageView / Purchase / Lead ...
        // все інше — через trackCustom
        if (event === 'PageView') {
            win?.fbq?.('track', 'PageView');
        } else if (event === 'Lead') {
            win?.fbq?.('track', 'Lead', params || {});
        } else {
            win?.fbq?.('trackCustom', event, params || {});
        }
    } catch {}

    // TikTok
    try {
        if (event === 'PageView') {
            win?.ttq?.page();
        } else {
            win?.ttq?.track(event, params || {});
        }
    } catch {}

    // GTM
    try {
        win.dataLayer = win.dataLayer || [];
        win.dataLayer.push({ event, ...(params || {}) });
    } catch {}

    log('event →', event, params || {});
}

/* ---------------- Public trackers ---------------- */

export function trackImpression(meta?: Record<string, any>) {
    console.log('trackImpression', meta);
    sendAll(evName('impression'), meta);
}

export function trackStart(meta?: Record<string, any>) {
    console.log('trackStart', meta);
    sendAll(evName('start'), meta);
}

export function trackStep(step: { index: number; id?: string | number; title?: string } & Record<string, any>) {
    console.log('trackStep', step);
    const { index, id, title, ...rest } = step || ({} as any);
    sendAll(evName('step'), { step_index: index, step_id: id, step_title: title, ...rest });
}

export function trackLeadView(meta?: Record<string, any>) {
    console.log('trackLeadView', meta);
    sendAll(evName('leadView'), meta);
}

export function trackLeadSubmit(meta?: Record<string, any>) {
    // Для FB можна продублювати стандартною "Lead"
    console.log('trackLeadSubmit', meta);
    sendAll(evName('leadSubmit'), meta);
    try { win?.fbq?.('track', 'Lead', meta || {}); } catch {}
}

/* ---------------- Optional: page_view helper ---------------- */

export function trackPageView(path?: string) {
    // GA4 page_view
    try { win?.gtag?.('event', 'page_view', path ? { page_path: path } : {}); } catch {}
    // FB PageView
    try { win?.fbq?.('track', 'PageView'); } catch {}
    // TikTok page
    try { win?.ttq?.page(); } catch {}
    // GTM
    try {
        win.dataLayer = win.dataLayer || [];
        win.dataLayer.push({ event: 'page_view', page_path: path });
    } catch {}
    log('page_view', path);
}

/* ---------------- Example integration hints ----------------
 * 1) Після отримання конфігу квіза: await initAnalytics(quiz.settings.analytics)
 * 2) На першому показі віджета: trackImpression({ quiz_id })
 * 3) Клік "Почати": trackStart({ quiz_id })
 * 4) При кожному переході кроку: trackStep({ index, id, title })
 * 5) Показ форми: trackLeadView({ quiz_id })
 * 6) Успішний сабміт: trackLeadSubmit({ quiz_id, discount_percent, ... })
 * ----------------------------------------------------------*/
