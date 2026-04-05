{{--
  Raw HTTP parser — client-side only. No traffic leaves the browser.
--}}
<div
    x-data="httpParser()"
    class="space-y-6"
    x-cloak
>
    <div class="rounded-xl border border-blue-200 bg-blue-50 dark:border-blue-900/50 dark:bg-blue-950/40 px-4 py-3 text-sm text-blue-900 dark:text-blue-100">
        <p class="font-medium">Runs locally in your browser</p>
        <p class="mt-1 text-blue-800/90 dark:text-blue-200/90">
            Pasted HTTP is parsed with JavaScript on this page only — it is not uploaded to URLCV or any third party. Clear sensitive data when you are done.
        </p>
    </div>

    <div>
        <label for="hp-raw" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
            Raw HTTP message
        </label>
        <textarea
            id="hp-raw"
            x-model="raw"
            rows="14"
            placeholder="Paste a full raw HTTP request or response…&#10;&#10;Example start lines:&#10;GET /api/resource HTTP/1.1&#10;HTTP/1.1 200 OK"
            class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm font-mono bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-y min-h-[12rem]"
        ></textarea>
        <div class="mt-3 flex flex-wrap gap-2">
            <button
                type="button"
                @click="loadSampleRequest()"
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
            >
                Sample request
            </button>
            <button
                type="button"
                @click="loadSampleResponse()"
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
            >
                Sample response
            </button>
            <button
                type="button"
                @click="clearAll()"
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
            >
                Clear
            </button>
            <button
                type="button"
                @click="parse()"
                class="inline-flex items-center px-4 py-1.5 text-sm font-semibold rounded-lg bg-primary-600 text-white hover:bg-primary-700 focus:ring-2 focus:ring-primary-500"
            >
                Parse
            </button>
        </div>
    </div>

    <template x-if="parseError">
        <div class="rounded-lg border border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-950/50 px-4 py-3 text-sm text-amber-900 dark:text-amber-100" role="alert">
            <span class="font-semibold">Could not parse:</span>
            <span x-text="parseError"></span>
        </div>
    </template>

    <template x-if="result && !parseError">
        <div class="space-y-6">
            {{-- Summary --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/50 p-4">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Summary</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                    <template x-if="result.kind === 'request'">
                        <div class="flex flex-col sm:flex-row sm:gap-2">
                            <dt class="text-gray-500 dark:text-gray-400 shrink-0">Type</dt>
                            <dd class="font-mono text-gray-900 dark:text-gray-100">HTTP request</dd>
                        </div>
                    </template>
                    <template x-if="result.kind === 'response'">
                        <div class="flex flex-col sm:flex-row sm:gap-2">
                            <dt class="text-gray-500 dark:text-gray-400 shrink-0">Type</dt>
                            <dd class="font-mono text-gray-900 dark:text-gray-100">HTTP response</dd>
                        </div>
                    </template>
                    <template x-if="result.kind === 'request'">
                        <div class="flex flex-col sm:flex-row sm:gap-2">
                            <dt class="text-gray-500 dark:text-gray-400 shrink-0">Method</dt>
                            <dd class="font-mono font-semibold text-gray-900 dark:text-gray-100" x-text="result.request.method"></dd>
                        </div>
                    </template>
                    <template x-if="result.kind === 'response'">
                        <div class="flex flex-col sm:flex-row sm:gap-2">
                            <dt class="text-gray-500 dark:text-gray-400 shrink-0">Status</dt>
                            <dd class="font-mono text-gray-900 dark:text-gray-100">
                                <span x-text="result.response.statusCode"></span>
                                <span class="text-gray-600 dark:text-gray-300" x-text="' ' + (result.response.reason || '')"></span>
                            </dd>
                        </div>
                    </template>
                    <template x-if="result.kind === 'request'">
                        <div class="flex flex-col sm:flex-row sm:gap-2 sm:col-span-2">
                            <dt class="text-gray-500 dark:text-gray-400 shrink-0">Start line</dt>
                            <dd class="font-mono text-xs break-all text-gray-800 dark:text-gray-200" x-text="result.startLine"></dd>
                        </div>
                    </template>
                    <template x-if="result.kind === 'response'">
                        <div class="flex flex-col sm:flex-row sm:gap-2 sm:col-span-2">
                            <dt class="text-gray-500 dark:text-gray-400 shrink-0">Start line</dt>
                            <dd class="font-mono text-xs break-all text-gray-800 dark:text-gray-200" x-text="result.startLine"></dd>
                        </div>
                    </template>
                    <div class="flex flex-col sm:flex-row sm:gap-2 sm:col-span-2" x-show="result.fullUrl">
                        <dt class="text-gray-500 dark:text-gray-400 shrink-0">Reconstructed URL</dt>
                        <dd class="flex flex-wrap items-center gap-2 min-w-0">
                            <span class="font-mono text-xs break-all text-primary-700 dark:text-primary-300" x-text="result.fullUrl"></span>
                            <button type="button" @click="copyText(result.fullUrl)" class="text-xs text-primary-600 hover:underline shrink-0" x-text="copyLabel"></button>
                        </dd>
                    </div>
                </dl>
                <div class="mt-4 flex flex-wrap gap-2">
                    <button
                        type="button"
                        @click="copyText(normalizedMessage)"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200"
                        x-text="'Copy normalized ' + (result.kind === 'request' ? 'request' : 'response')"
                    ></button>
                </div>
            </div>

            {{-- Triage --}}
            <div class="rounded-xl border border-amber-200 dark:border-amber-900/60 bg-amber-50/80 dark:bg-amber-950/30 p-4" x-show="warnings.length > 0">
                <h2 class="text-sm font-semibold text-amber-950 dark:text-amber-100 mb-2">Security triage (heuristic)</h2>
                <p class="text-xs text-amber-900/80 dark:text-amber-200/80 mb-3">
                    Flags are based only on what appears in your paste. Wording is intentionally cautious — review recommended where noted.
                </p>
                <ul class="space-y-3">
                    <template x-for="(w, idx) in warnings" :key="idx">
                        <li class="text-sm border-l-2 border-amber-400 dark:border-amber-600 pl-3">
                            <div class="font-medium text-amber-950 dark:text-amber-50" x-text="w.title"></div>
                            <div class="text-amber-900/90 dark:text-amber-100/90 mt-0.5" x-text="w.detail"></div>
                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1 italic" x-text="w.why"></div>
                        </li>
                    </template>
                </ul>
            </div>

            {{-- URL / path --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4" x-show="result.kind === 'request'">
                <button type="button" @click="secPath = !secPath" class="flex w-full items-center justify-between text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                    <span>URL &amp; path</span>
                    <span class="text-gray-400" x-text="secPath ? '▼' : '▶'"></span>
                </button>
                <div x-show="secPath" x-collapse class="mt-3 space-y-2 text-sm">
                    <div class="grid grid-cols-1 gap-1">
                        <span class="text-gray-500 dark:text-gray-400 text-xs">Path</span>
                        <div class="flex flex-wrap items-center gap-2 font-mono text-xs break-all">
                            <span x-text="result.pathInfo.path"></span>
                            <button type="button" @click="copyText(result.pathInfo.path)" class="text-primary-600 text-xs" x-text="copyLabel"></button>
                        </div>
                    </div>
                    <template x-if="result.pathInfo.query">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">Query string</span>
                            <pre class="mt-1 p-2 rounded bg-gray-50 dark:bg-gray-900 text-xs font-mono overflow-x-auto whitespace-pre-wrap break-all" x-text="result.pathInfo.query"></pre>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Query parameters --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4" x-show="result.kind === 'request' && result.pathInfo.params.length">
                <button type="button" @click="secQuery = !secQuery" class="flex w-full items-center justify-between text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                    <span>Query parameters</span>
                    <span class="text-gray-400" x-text="secQuery ? '▼' : '▶'"></span>
                </button>
                <div x-show="secQuery" x-collapse class="mt-3 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
                                <th class="py-2 pr-4 font-medium">Name</th>
                                <th class="py-2 font-medium">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, ri) in result.pathInfo.params" :key="ri">
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-1.5 pr-4 font-mono text-xs align-top" x-text="row.name"></td>
                                    <td class="py-1.5 font-mono text-xs break-all align-top">
                                        <span x-text="row.displayValue"></span>
                                        <button type="button" class="ml-2 text-primary-600 shrink-0" @click="copyText(row.value)">Copy</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Headers --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <button type="button" @click="secHeaders = !secHeaders" class="flex w-full items-center justify-between text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                    <span>Headers (<span x-text="result.headers.length"></span>)</span>
                    <span class="text-gray-400" x-text="secHeaders ? '▼' : '▶'"></span>
                </button>
                <div x-show="secHeaders" x-collapse class="mt-3 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <tbody>
                            <template x-for="(h, hi) in result.headers" :key="hi">
                                <tr class="border-b border-gray-100 dark:border-gray-800 align-top">
                                    <td class="py-1.5 pr-3 font-mono text-xs text-gray-600 dark:text-gray-300 whitespace-nowrap" x-text="h.name + ':'"></td>
                                    <td class="py-1.5 font-mono text-xs break-all">
                                        <span x-html="h.displayValue"></span>
                                        <button type="button" class="ml-2 text-primary-600" @click="copyText(h.rawValue)">Copy</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Cookies --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4" x-show="result.cookies.length > 0">
                <button type="button" @click="secCookies = !secCookies" class="flex w-full items-center justify-between text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                    <span x-text="result.kind === 'request' ? 'Cookies (Cookie header)' : 'Set-Cookie lines'"></span>
                    <span class="text-gray-400" x-text="secCookies ? '▼' : '▶'"></span>
                </button>
                <div x-show="secCookies" x-collapse class="mt-3 space-y-3">
                    <template x-for="(c, ci) in result.cookies" :key="ci">
                        <div class="rounded-lg bg-gray-50 dark:bg-gray-900/80 p-3 text-xs font-mono break-all">
                            <div x-html="c.display"></div>
                            <template x-if="c.flags.length">
                                <div class="mt-2 flex flex-wrap gap-1">
                                    <template x-for="f in c.flags" :key="f">
                                        <span class="px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-[10px]" x-text="f"></span>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Auth --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4" x-show="result.auth.length > 0">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Authorization &amp; auth-related headers</h2>
                <ul class="space-y-2 text-sm">
                    <template x-for="(a, ai) in result.auth" :key="ai">
                        <li class="font-mono text-xs break-all border-l-2 border-primary-400 pl-3">
                            <span class="text-gray-600 dark:text-gray-400" x-text="a.label + ': '"></span>
                            <span x-html="a.display"></span>
                            <button type="button" class="ml-2 text-primary-600" @click="copyText(a.raw)">Copy</button>
                        </li>
                    </template>
                </ul>
            </div>

            {{-- Body --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4" x-show="result.bodyPreview !== null">
                <button type="button" @click="secBody = !secBody" class="flex w-full items-center justify-between text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                    <span>Body</span>
                    <span class="text-gray-400" x-text="secBody ? '▼' : '▶'"></span>
                </button>
                <div x-show="secBody" x-collapse class="mt-3 space-y-3">
                    <div class="text-xs text-gray-500 dark:text-gray-400" x-show="result.contentType">
                        Content-Type: <span class="font-mono text-gray-700 dark:text-gray-300" x-text="result.contentType"></span>
                    </div>
                    <template x-if="result.bodyJsonFormatted">
                        <div>
                            <div class="text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">JSON (formatted)</div>
                            <pre class="p-3 rounded-lg bg-gray-900 text-gray-100 text-xs font-mono overflow-x-auto max-h-96 overflow-y-auto whitespace-pre" x-html="result.bodyJsonHighlight"></pre>
                            <button type="button" @click="copyText(result.bodyJsonFormatted)" class="mt-2 text-xs text-primary-600">Copy JSON</button>
                        </div>
                    </template>
                    <template x-if="result.bodyFormFields && result.bodyFormFields.length">
                        <div>
                            <div class="text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Form fields</div>
                            <table class="min-w-full text-xs">
                                <tbody>
                                    <template x-for="(f, fi) in result.bodyFormFields" :key="fi">
                                        <tr class="border-b border-gray-100 dark:border-gray-800">
                                            <td class="py-1 font-mono pr-2" x-text="f.name"></td>
                                            <td class="py-1 font-mono break-all">
                                                <span x-text="f.display"></span>
                                                <button type="button" class="ml-2 text-primary-600" @click="copyText(f.value)">Copy</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                    <template x-if="!result.bodyJsonFormatted">
                        <div>
                            <div class="text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Raw preview</div>
                            <pre class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900 text-xs font-mono overflow-x-auto max-h-96 overflow-y-auto whitespace-pre-wrap break-all" x-text="result.bodyPreview"></pre>
                            <button type="button" @click="copyText(result.bodyRaw)" class="mt-2 text-xs text-primary-600">Copy body</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <template x-if="!result && !parseError && !raw.trim()">
        <div class="text-center py-10 px-4 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 text-sm">
            <p class="font-medium text-gray-700 dark:text-gray-300 mb-2">Nothing parsed yet</p>
            <p>Paste a raw HTTP request or response, then click <strong>Parse</strong>. Works with typical exports from Burp, browser devtools, and <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded">curl</code> conversions.</p>
        </div>
    </template>
</div>

@push('scripts')
<script>
(function () {
    const REQ_LINE = /^(GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS|CONNECT|TRACE|PRI)\s+(\S+)\s+HTTP\/(\d(?:\.\d)?)\s*$/i;
    const STATUS_LINE = /^HTTP\/(\d(?:\.\d)?)\s+(\d{3})\s*(.*)$/i;

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function truncateDisplay(s, max) {
        if (s.length <= max) return s;
        return s.slice(0, max) + '…';
    }

    /** Simple JSON syntax colouring — strings, keys, numbers, literals */
    function highlightJson(jsonStr) {
        const esc = escapeHtml(jsonStr);
        return esc.replace(
            /("(\\u[a-fA-F0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d+)?(?:[eE][+\-]?\d+)?)/g,
            function (match) {
                let cls = 'text-sky-300';
                if (/^"/.test(match)) {
                    cls = /:$/.test(match) ? 'text-violet-300' : 'text-emerald-300';
                } else if (/true|false|null/.test(match)) {
                    cls = 'text-amber-300';
                } else if (/^-?\d/.test(match)) {
                    cls = 'text-orange-300';
                }
                return '<span class="' + cls + '">' + match + '</span>';
            }
        );
    }

    function headerLookup(headers, name) {
        const lower = name.toLowerCase();
        const h = headers.find(function (x) { return x.name.toLowerCase() === lower; });
        return h ? h.value : '';
    }

    function parseHeadersBlock(lines, startIdx) {
        const headers = [];
        let i = startIdx;
        for (; i < lines.length; i++) {
            var line = lines[i];
            if (line === '') {
                i++;
                break;
            }
            if (/^[ \t]/.test(line) && headers.length > 0) {
                headers[headers.length - 1].value += ' ' + line.trim();
                continue;
            }
            var idx = line.indexOf(':');
            if (idx === -1) continue;
            headers.push({
                name: line.slice(0, idx).trim(),
                value: line.slice(idx + 1).trim(),
            });
        }
        return { headers: headers, nextIndex: i };
    }

    function parseMessage(raw) {
        var text = raw.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
        var lines = text.split('\n');
        var i = 0;
        while (i < lines.length && lines[i].trim() === '') i++;

        while (i < lines.length) {
            var L0 = lines[i].trim();
            if (L0.startsWith('* ') || L0.startsWith('> ') || /^curl\s/i.test(L0) || L0.startsWith('echo ') || L0.startsWith('wget ')) {
                i++;
                continue;
            }
            break;
        }

        if (i >= lines.length) {
            throw new Error('Empty input after trimming.');
        }

        var startLine = lines[i].replace(/\s+$/, '');
        var kind = null;
        var request = null;
        var response = null;

        if (STATUS_LINE.test(startLine)) {
            kind = 'response';
            var ms = startLine.match(STATUS_LINE);
            response = { httpVersion: ms[1], statusCode: ms[2], reason: (ms[3] || '').trim() };
        } else if (REQ_LINE.test(startLine)) {
            kind = 'request';
            var mr = startLine.match(REQ_LINE);
            request = { method: mr[1].toUpperCase(), target: mr[2], httpVersion: mr[3] };
        } else {
            throw new Error(
                'First line does not look like HTTP. Expected something like "GET /path HTTP/1.1" or "HTTP/1.1 200 OK".'
            );
        }

        i++;
        var hb = parseHeadersBlock(lines, i);
        var headers = hb.headers;
        i = hb.nextIndex;
        var body = lines.slice(i).join('\n');

        return {
            kind: kind,
            startLine: startLine,
            request: request,
            response: response,
            headers: headers,
            body: body,
        };
    }

    function parseQueryFromTarget(target) {
        var qIdx = target.indexOf('?');
        if (qIdx === -1) {
            return { path: target, query: '', params: [], duplicateNames: [] };
        }
        var path = target.slice(0, qIdx);
        var query = target.slice(qIdx + 1);
        var params = [];
        var counts = {};
        try {
            var sp = new URLSearchParams(query);
            sp.forEach(function (v, k) {
                params.push({ name: k, value: v });
                counts[k] = (counts[k] || 0) + 1;
            });
        } catch (e) {
            params = [];
        }
        var duplicateNames = Object.keys(counts).filter(function (k) { return counts[k] > 1; });
        return { path: path, query: query, params: params, duplicateNames: duplicateNames };
    }

    function maybeSensitiveValue(v) {
        if (v.length > 48) return truncateDisplay(v, 24) + '… (truncated)';
        return v;
    }

    function maskAuthValue(scheme, rest) {
        var s = (scheme || '').toLowerCase();
        if (s === 'bearer' && rest.length > 12) {
            return 'Bearer ' + rest.slice(0, 6) + '…' + rest.slice(-4) + ' (truncated)';
        }
        if (s === 'basic' && rest.length > 8) {
            return 'Basic … (truncated; copy for full value)';
        }
        if (rest.length > 40) return truncateDisplay(rest, 20) + '… (truncated)';
        return (scheme ? scheme + ' ' : '') + rest;
    }

    function parseAuthorizationDisplay(value) {
        var idx = value.indexOf(' ');
        if (idx === -1) return { display: escapeHtml(maybeSensitiveValue(value)), raw: value };
        var scheme = value.slice(0, idx).trim();
        var rest = value.slice(idx + 1).trim();
        return {
            display: escapeHtml(maskAuthValue(scheme, rest)),
            raw: value,
        };
    }

    function parseRequestCookies(cookieHeader) {
        if (!cookieHeader) return [];
        var parts = cookieHeader.split(';');
        var out = [];
        parts.forEach(function (p) {
            var t = p.trim();
            if (!t) return;
            var eq = t.indexOf('=');
            if (eq === -1) {
                out.push({ name: '', raw: t, display: escapeHtml(t), flags: [] });
                return;
            }
            var name = t.slice(0, eq).trim();
            var val = t.slice(eq + 1);
            out.push({
                name: name,
                raw: t,
                display: '<span class="text-primary-600 dark:text-primary-400">' + escapeHtml(name) + '</span>=' + escapeHtml(maybeSensitiveValue(val)),
                flags: [],
            });
        });
        return out;
    }

    function parseSetCookieEntries(headers) {
        var out = [];
        headers.forEach(function (h) {
            if (h.name.toLowerCase() !== 'set-cookie') return;
            var v = h.value;
            var semi = v.indexOf(';');
            var first = semi === -1 ? v : v.slice(0, semi);
            var rest = semi === -1 ? '' : v.slice(semi + 1);
            var flags = [];
            rest.split(';').forEach(function (x) {
                var t = x.trim();
                if (!t) return;
                var up = t.toLowerCase();
                if (['secure', 'httponly'].indexOf(up) !== -1) flags.push(t);
                else if (up.indexOf('samesite') === 0) flags.push(t);
                else if (up.indexOf('path') === 0) flags.push(t);
                else if (up.indexOf('domain') === 0) flags.push(t);
                else if (up.indexOf('max-age') === 0 || up.indexOf('expires') === 0) flags.push(t);
            });
            out.push({
                name: 'Set-Cookie',
                raw: v,
                display: escapeHtml(v.length > 200 ? v.slice(0, 200) + '…' : v),
                flags: flags,
            });
        });
        return out;
    }

    function cookieFlagTriage(setCookieObjs) {
        var warns = [];
        setCookieObjs.forEach(function (c) {
            var lower = c.raw.toLowerCase();
            var namePart = c.raw.split(';')[0] || '';
            if (!/;\s*secure/i.test(lower) && !/^__(Host|Secure)-/i.test(namePart)) {
                warns.push({
                    title: 'Set-Cookie without Secure flag (possible issue)',
                    detail: 'Cookie may be sent over cleartext HTTP: ' + truncateDisplay(c.raw.split(';')[0], 80),
                    why: 'Worth checking whether the site should enforce HTTPS-only cookies.',
                });
            }
            if (!/;\s*httponly/i.test(lower)) {
                warns.push({
                    title: 'Set-Cookie without HttpOnly (worth checking)',
                    detail: truncateDisplay(c.raw.split(';')[0], 80),
                    why: 'Session cookies without HttpOnly may be reachable from scripts — review recommended if this is a session token.',
                });
            }
            if (!/samesite=/i.test(lower)) {
                warns.push({
                    title: 'Set-Cookie without SameSite (review recommended)',
                    detail: truncateDisplay(c.raw.split(';')[0], 80),
                    why: 'Cross-site behaviour depends on browser defaults; confirm it matches your threat model.',
                });
            }
        });
        return warns;
    }

    function pathIdTriage(path) {
        var warns = [];
        path.split('/').forEach(function (seg) {
            if (!seg) return;
            if (/^\d{6,}$/.test(seg)) {
                warns.push({
                    title: 'Long numeric path segment (worth checking)',
                    detail: 'Segment: ' + seg,
                    why: 'Numeric identifiers may be enumerable — verify authorization on the server, not only obscurity.',
                });
            }
            if (/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(seg)) {
                warns.push({
                    title: 'UUID-like path segment',
                    detail: 'Segment: ' + seg,
                    why: 'Often an object id — ensure access control applies to this resource.',
                });
            }
        });
        return warns;
    }

    function bodySecretScan(body) {
        var warns = [];
        if (!body || body.length < 10) return warns;
        if (/eyJ[A-Za-z0-9_-]{10,}\.[A-Za-z0-9_-]{10,}\.[A-Za-z0-9_-]{10,}/.test(body)) {
            warns.push({
                title: 'JWT-like string in body (possible issue)',
                detail: 'A three-part base64 segment resembling a JWT was found.',
                why: 'If this is a live token, treat it as sensitive; verify scope and expiry in your testing workflow.',
            });
        }
        if (/\bsk_live_[a-zA-Z0-9]{8,}/.test(body) || /\bsk_test_[a-zA-Z0-9]{8,}/.test(body)) {
            warns.push({
                title: 'Stripe-style secret pattern (possible issue)',
                detail: 'Substring similar to sk_live_ / sk_test_ detected.',
                why: 'Review recommended — these often indicate API keys if not redacted.',
            });
        }
        if (/\bAIza[0-9A-Za-z_-]{20,}/.test(body)) {
            warns.push({
                title: 'Google API key-like pattern (worth checking)',
                detail: 'An AIza…-style substring was found.',
                why: 'Confirm whether this is a real key and whether it should appear in responses.',
            });
        }
        return warns;
    }

    function responseHeaderTriage(headers) {
        var warns = [];
        var map = {};
        headers.forEach(function (h) {
            map[h.name.toLowerCase()] = h.value;
        });

        var secHeaders = [
            { key: 'strict-transport-security', name: 'Strict-Transport-Security' },
            { key: 'x-content-type-options', name: 'X-Content-Type-Options' },
            { key: 'x-frame-options', name: 'X-Frame-Options' },
            { key: 'content-security-policy', name: 'Content-Security-Policy' },
            { key: 'referrer-policy', name: 'Referrer-Policy' },
            { key: 'permissions-policy', name: 'Permissions-Policy' },
        ];
        secHeaders.forEach(function (sh) {
            if (!map[sh.key]) {
                warns.push({
                    title: 'Missing ' + sh.name + ' (worth checking)',
                    detail: 'This header was not present in the pasted response.',
                    why: 'Absence is not always wrong, but these headers often harden browsers — compare with your baseline.',
                });
            }
        });

        var cors = map['access-control-allow-origin'];
        if (cors === '*') {
            warns.push({
                title: 'Permissive CORS: Access-Control-Allow-Origin: *',
                detail: 'Any origin may read responses in CORS terms.',
                why: 'Review recommended when credentials or private data are involved — combine with other signals.',
            });
        }

        var acac = map['access-control-allow-credentials'];
        if (cors === '*' && acac && acac.toLowerCase() === 'true') {
            warns.push({
                title: 'Unusual CORS combination (worth checking)',
                detail: 'Allow-Origin * with Allow-Credentials true is typically invalid; browsers may ignore — verify actual behaviour.',
                why: 'May indicate misconfiguration or a copy artefact.',
            });
        }

        var csp = map['content-security-policy'] || map['content-security-policy-report-only'];
        if (csp) {
            if (/\bunsafe-inline\b/i.test(csp)) {
                warns.push({
                    title: 'CSP contains unsafe-inline (possible issue)',
                    detail: 'Allows inline scripts or styles depending on context.',
                    why: 'Worth checking if you expected a strict CSP.',
                });
            }
            if (/\bunsafe-eval\b/i.test(csp)) {
                warns.push({
                    title: 'CSP contains unsafe-eval (worth checking)',
                    detail: 'May weaken XSS protections.',
                    why: 'Review recommended for production apps.',
                });
            }
        }

        var xp = map['x-powered-by'];
        if (xp) {
            warns.push({
                title: 'X-Powered-By present (version leakage)',
                detail: xp,
                why: 'Framework/version hints can assist attackers — worth checking if disclosure is acceptable.',
            });
        }
        var srv = map['server'];
        if (srv && /\d+\.\d+/.test(srv)) {
            warns.push({
                title: 'Server header may expose version (worth checking)',
                detail: srv,
                why: 'Version strings can narrow exploit research — review recommended.',
            });
        }

        var debugHints = ['x-debug', 'x-backend', 'x-envoy-upstream-service-time'];
        debugHints.forEach(function (dh) {
            if (map[dh]) {
                warns.push({
                    title: 'Debug / routing header present: ' + dh,
                    detail: map[dh],
                    why: 'May reveal infrastructure — confirm intended for external responses.',
                });
            }
        });

        return warns;
    }

    function requestHeaderTriage(headers, pathInfo) {
        var warns = [];
        var map = {};
        headers.forEach(function (h) {
            map[h.name.toLowerCase()] = h.value;
        });
        if (map['authorization']) {
            warns.push({
                title: 'Authorization header present',
                detail: 'Credentials or tokens may appear in this message.',
                why: 'Treat as sensitive in reports and avoid sharing raw captures publicly.',
            });
        }
        if (map['cookie']) {
            warns.push({
                title: 'Cookie header present',
                detail: 'Session or CSRF tokens may be included.',
                why: 'Review recommended before sharing logs.',
            });
        }
        if (pathInfo.duplicateNames.length) {
            warns.push({
                title: 'Duplicate query parameter names',
                detail: pathInfo.duplicateNames.join(', '),
                why: 'Servers may use first/last/merge rules — worth checking for cache or WAF bypass quirks.',
            });
        }
        return warns;
    }

    function dedupeWarns(arr) {
        var seen = new Set();
        return arr.filter(function (w) {
            var k = w.title + '|' + w.detail;
            if (seen.has(k)) return false;
            seen.add(k);
            return true;
        });
    }

    function enrichParsed(parsed) {
        var kind = parsed.kind;
        var headers = parsed.headers.map(function (h) {
            var html = escapeHtml(h.value);
            if (h.name.toLowerCase() === 'authorization') {
                var ad = parseAuthorizationDisplay(h.value);
                html = ad.display;
            }
            return { name: h.name, rawValue: h.value, displayValue: html };
        });

        var contentType = '';
        headers.forEach(function (h) {
            if (h.name.toLowerCase() === 'content-type') contentType = h.rawValue;
        });

        var host = headerLookup(parsed.headers, 'Host');
        var fullUrl = null;
        var pathInfo = { path: '', query: '', params: [], duplicateNames: [] };

        if (kind === 'request' && parsed.request) {
            var pi = parseQueryFromTarget(parsed.request.target);
            pathInfo.path = pi.path;
            pathInfo.query = pi.query;
            pathInfo.duplicateNames = pi.duplicateNames;
            pathInfo.params = pi.params.map(function (p) {
                return {
                    name: p.name,
                    value: p.value,
                    displayValue: maybeSensitiveValue(p.value),
                };
            });
            var scheme = 'https';
            if (host && parsed.request.target.indexOf('http://') === 0) {
                fullUrl = parsed.request.target;
            } else if (host) {
                fullUrl = scheme + '://' + host + (pi.path.startsWith('/') ? pi.path : '/' + pi.path);
                if (pi.query) fullUrl += '?' + pi.query;
            }
        }

        var cookies = [];
        if (kind === 'request') {
            cookies = parseRequestCookies(headerLookup(parsed.headers, 'Cookie'));
        } else {
            cookies = parseSetCookieEntries(parsed.headers);
        }

        var auth = [];
        headers.forEach(function (h) {
            var ln = h.name.toLowerCase();
            if (ln === 'authorization' || ln === 'proxy-authorization') {
                auth.push({ label: h.name, display: h.displayValue, raw: h.rawValue });
            }
        });

        var body = parsed.body;
        var bodyPreview = body.length ? body : null;
        var bodyJsonFormatted = null;
        var bodyJsonHighlight = null;
        var bodyFormFields = null;

        var isJson = /json/i.test(contentType) || /^[\s]*[{[]/.test(body);
        var isForm = /application\/x-www-form-urlencoded/i.test(contentType);

        if (body && isJson) {
            try {
                var obj = JSON.parse(body);
                bodyJsonFormatted = JSON.stringify(obj, null, 2);
                bodyJsonHighlight = highlightJson(bodyJsonFormatted);
            } catch (e) {
                bodyJsonFormatted = null;
            }
        }
        if (body && isForm && !bodyJsonFormatted) {
            try {
                var sp2 = new URLSearchParams(body);
                bodyFormFields = [];
                sp2.forEach(function (v, k) {
                    bodyFormFields.push({ name: k, value: v, display: maybeSensitiveValue(v) });
                });
            } catch (e2) {
                bodyFormFields = null;
            }
        }

        var warnings = [];
        if (kind === 'response') {
            warnings = warnings.concat(responseHeaderTriage(parsed.headers));
            warnings = warnings.concat(cookieFlagTriage(cookies));
        } else {
            warnings = warnings.concat(requestHeaderTriage(parsed.headers, pathInfo));
            warnings = warnings.concat(pathIdTriage(pathInfo.path));
        }
        warnings = warnings.concat(bodySecretScan(body));
        warnings = dedupeWarns(warnings);

        function buildNormalized() {
            var lines = [];
            if (kind === 'request' && parsed.request) {
                lines.push(
                    parsed.request.method + ' ' + parsed.request.target + ' HTTP/' + parsed.request.httpVersion
                );
            } else if (kind === 'response' && parsed.response) {
                lines.push(
                    'HTTP/' +
                        parsed.response.httpVersion +
                        ' ' +
                        parsed.response.statusCode +
                        ' ' +
                        (parsed.response.reason || '')
                );
            }
            parsed.headers.forEach(function (h) {
                lines.push(h.name + ': ' + h.value);
            });
            lines.push('');
            lines.push(body);
            return lines.join('\r\n');
        }

        return {
            kind: kind,
            startLine: parsed.startLine,
            request: parsed.request,
            response: parsed.response,
            headers: headers,
            contentType: contentType,
            fullUrl: fullUrl,
            pathInfo: pathInfo,
            cookies: cookies,
            auth: auth,
            bodyPreview: bodyPreview,
            bodyRaw: body,
            bodyJsonFormatted: bodyJsonFormatted,
            bodyJsonHighlight: bodyJsonHighlight,
            bodyFormFields: bodyFormFields,
            warnings: warnings,
            normalizedMessage: buildNormalized(),
        };
    }

    window.httpParserLib = {
        parseMessage: parseMessage,
        enrich: enrichParsed,
    };
})();

function httpParser() {
    return {
        raw: '',
        parseError: null,
        result: null,
        warnings: [],
        normalizedMessage: '',
        secPath: true,
        secQuery: true,
        secHeaders: true,
        secCookies: true,
        secBody: true,
        copyLabel: 'Copy',
        copyTimer: null,

        loadSampleRequest() {
            this.raw =
                'GET /api/users/12345/orders?id=1&id=2&ref=abc HTTP/1.1\r\n' +
                'Host: api.example.com\r\n' +
                'User-Agent: Mozilla/5.0 (compatible; Research/1.0)\r\n' +
                'Accept: application/json\r\n' +
                'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.signature_here\r\n' +
                'Cookie: session=abc123; csrf=def456\r\n' +
                '\r\n';
            this.parseError = null;
            this.result = null;
        },

        loadSampleResponse() {
            this.raw =
                'HTTP/1.1 200 OK\r\n' +
                'Date: Mon, 01 Jan 2024 12:00:00 GMT\r\n' +
                'Server: nginx/1.18.0\r\n' +
                'X-Powered-By: Express/4.17.1\r\n' +
                'Access-Control-Allow-Origin: *\r\n' +
                'Set-Cookie: sid=opaque; Path=/; HttpOnly\r\n' +
                'Set-Cookie: theme=dark; Path=/\r\n' +
                'Content-Type: application/json; charset=utf-8\r\n' +
                '\r\n' +
                '{"user":{"id":12345,"role":"admin"},"token":"eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxIn0.sig"}';
            this.parseError = null;
            this.result = null;
        },

        clearAll() {
            this.raw = '';
            this.parseError = null;
            this.result = null;
            this.warnings = [];
        },

        parse() {
            this.parseError = null;
            this.result = null;
            this.warnings = [];
            try {
                var parsed = window.httpParserLib.parseMessage(this.raw);
                var enriched = window.httpParserLib.enrich(parsed);
                this.warnings = enriched.warnings;
                this.normalizedMessage = enriched.normalizedMessage;
                this.result = enriched;
            } catch (e) {
                this.parseError = e.message || String(e);
            }
        },

        copyText(text) {
            var self = this;
            if (!text && text !== '') return;
            navigator.clipboard.writeText(text).then(function () {
                self.copyLabel = 'Copied!';
                clearTimeout(self.copyTimer);
                self.copyTimer = setTimeout(function () {
                    self.copyLabel = 'Copy';
                }, 2000);
            });
        },
    };
}
</script>
@endpush
