{{--
  Raw HTTP parser — client-side only. No traffic leaves the browser.
--}}
<div
    x-data="httpParser()"
    class="space-y-6"
    x-cloak
>
    <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-4 shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-blue-950">Runs locally in your browser</p>
                <p class="mt-1.5 max-w-3xl text-sm leading-relaxed text-blue-900">
                    Pasted HTTP is parsed with JavaScript on this page only. URLCV does not upload your request or response to the server or any third party.
                </p>
            </div>
            <div class="grid gap-2 text-xs text-blue-950 sm:grid-cols-2 md:min-w-[18rem]">
                <div class="rounded-xl border border-blue-200 bg-white/80 px-3 py-2">
                    <p class="font-semibold">Accepted input</p>
                    <p class="mt-1 text-blue-900">Burp raw messages, curl verbose headers, browser/devtools copies, or log excerpts with an intact HTTP start line.</p>
                </div>
                <div class="rounded-xl border border-blue-200 bg-white/80 px-3 py-2">
                    <p class="font-semibold">Keyboard flow</p>
                    <p class="mt-1 text-blue-900"><span class="font-medium">Ctrl/Cmd + Enter</span> parses. Copy buttons keep sensitive values masked in the UI.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <label for="hp-raw" class="block text-sm font-semibold text-gray-950">
                    Raw HTTP message
                </label>
                <p class="mt-1 text-sm text-gray-600">
                    Paste a full request or response. The parser is forgiving about wrapper lines, but it still expects a real HTTP start line somewhere in the paste.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600">
                <span class="rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1" x-text="inputStats()"></span>
                <span
                    x-show="isLargeInput()"
                    class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-amber-950"
                >
                    Large paste: expensive formatting is capped to keep the page responsive.
                </span>
            </div>
        </div>

        <textarea
            id="hp-raw"
            x-model="raw"
            x-ref="raw"
            rows="15"
            placeholder="Paste a full raw HTTP request or response…&#10;&#10;Examples:&#10;GET /api/resource HTTP/1.1&#10;HTTP/1.1 200 OK"
            @keydown.ctrl.enter.prevent="parse()"
            @keydown.meta.enter.prevent="parse()"
            class="mt-4 block min-h-[15rem] w-full resize-y rounded-2xl border border-gray-300 bg-white px-4 py-3 text-[15px] leading-relaxed text-gray-950 shadow-inner placeholder:text-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
        ></textarea>

        <div class="mt-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    @click="loadSampleRequest()"
                    class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-800 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                >
                    Sample request
                </button>
                <button
                    type="button"
                    @click="loadSampleResponse()"
                    class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-800 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                >
                    Sample response
                </button>
                <button
                    type="button"
                    @click="clearAll()"
                    class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                >
                    Clear
                </button>
            </div>

            <button
                type="button"
                @click="parse()"
                :disabled="isParsing || !raw.trim()"
                class="inline-flex items-center justify-center rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500/40 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <span x-show="!isParsing">Parse locally</span>
                <span x-show="isParsing">Parsing…</span>
            </button>
        </div>
    </div>

    <template x-if="parseError">
        <div class="rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-950 shadow-sm" role="alert">
            <span class="font-semibold">Could not parse:</span>
            <span x-text="parseError"></span>
        </div>
    </template>

    <template x-if="result && !parseError">
        <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <h2 class="text-sm font-semibold text-gray-950">Summary</h2>
                        <p class="mt-1 text-sm text-gray-600">Structured output and review notes generated entirely in the browser.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            @click="copyText(normalizedMessage, 'copy-normalized')"
                            class="inline-flex items-center rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-800 transition hover:bg-gray-100"
                            x-text="copyLabel('copy-normalized', 'Copy normalized message')"
                        ></button>
                        <button
                            type="button"
                            x-show="result.fullUrl"
                            @click="copyText(result.fullUrl, 'copy-url')"
                            class="inline-flex items-center rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-800 transition hover:bg-gray-100"
                            x-text="copyLabel('copy-url', 'Copy reconstructed URL')"
                        ></button>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500">Type</p>
                        <p class="mt-1 font-mono text-sm font-semibold text-gray-950" x-text="result.kind === 'request' ? 'HTTP request' : 'HTTP response'"></p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3" x-show="result.kind === 'request'">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500">Method</p>
                        <p class="mt-1 font-mono text-sm font-semibold text-gray-950" x-text="result.request.method"></p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3" x-show="result.kind === 'response'">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500">Status</p>
                        <p class="mt-1 font-mono text-sm font-semibold text-gray-950">
                            <span x-text="result.response.statusCode"></span>
                            <span class="text-gray-600" x-text="' ' + (result.response.reason || '')"></span>
                        </p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500">Headers</p>
                        <p class="mt-1 font-mono text-sm font-semibold text-gray-950" x-text="result.headers.length"></p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500">Body size</p>
                        <p class="mt-1 font-mono text-sm font-semibold text-gray-950" x-text="result.bodySizeLabel"></p>
                    </div>
                </div>

                <dl class="mt-4 grid gap-3 md:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 md:col-span-2">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="min-w-0">
                                <dt class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500">Start line</dt>
                                <dd class="mt-1 break-all font-mono text-xs text-gray-900" x-text="result.startLine"></dd>
                            </div>
                            <button
                                type="button"
                                @click="copyText(result.startLine, 'copy-start-line')"
                                class="shrink-0 text-xs font-medium text-primary-700 hover:text-primary-800"
                                x-text="copyLabel('copy-start-line', 'Copy')"
                            ></button>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 md:col-span-2" x-show="result.fullUrl">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="min-w-0">
                                <dt class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500">Reconstructed URL</dt>
                                <dd class="mt-1 break-all font-mono text-xs text-primary-700" x-text="result.fullUrl"></dd>
                            </div>
                            <button
                                type="button"
                                @click="copyText(result.fullUrl, 'copy-url-inline')"
                                class="shrink-0 text-xs font-medium text-primary-700 hover:text-primary-800"
                                x-text="copyLabel('copy-url-inline', 'Copy')"
                            ></button>
                        </div>
                    </div>
                </dl>

                <div class="mt-4 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3" x-show="result.notes.length > 0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500">Parser notes</p>
                    <ul class="mt-2 space-y-2 text-sm text-gray-700">
                        <template x-for="(note, idx) in result.notes" :key="'note-' + idx">
                            <li x-text="note"></li>
                        </template>
                    </ul>
                </div>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm" x-show="result.warnings.length > 0">
                <div class="flex flex-col gap-1">
                    <h2 class="text-sm font-semibold text-amber-950">Security triage</h2>
                    <p class="text-sm text-amber-900">
                        These checks only reflect what is visible in the pasted message. They are prompts for review, not proof of a vulnerability.
                    </p>
                </div>
                <ul class="mt-4 space-y-3">
                    <template x-for="(warning, idx) in result.warnings" :key="'warning-' + idx">
                        <li class="rounded-2xl border border-amber-200 bg-white/80 px-4 py-3">
                            <div class="text-sm font-semibold text-amber-950" x-text="warning.title"></div>
                            <div class="mt-1 text-sm text-gray-800" x-text="warning.detail"></div>
                            <div class="mt-1 text-xs text-gray-600" x-text="warning.why"></div>
                        </li>
                    </template>
                </ul>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm" x-show="result.kind === 'request'">
                <button
                    type="button"
                    @click="secPath = !secPath"
                    class="flex w-full items-center justify-between gap-4 text-left"
                    :aria-expanded="secPath"
                >
                    <div>
                        <h2 class="text-sm font-semibold text-gray-950">URL &amp; path</h2>
                        <p class="mt-1 text-sm text-gray-600">Path-only view plus the raw query string when one is present.</p>
                    </div>
                    <span class="shrink-0 rounded-full border border-gray-200 bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700" x-text="secPath ? 'Hide' : 'Show'"></span>
                </button>

                <div x-show="secPath" x-collapse class="mt-4 space-y-3">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500">Path</p>
                                <p class="mt-1 break-all font-mono text-xs text-gray-900" x-text="result.pathInfo.path"></p>
                            </div>
                            <button
                                type="button"
                                @click="copyText(result.pathInfo.path, 'copy-path')"
                                class="shrink-0 text-xs font-medium text-primary-700 hover:text-primary-800"
                                x-text="copyLabel('copy-path', 'Copy')"
                            ></button>
                        </div>
                    </div>

                    <template x-if="result.pathInfo.query">
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500">Query string</p>
                                    <pre class="mt-1 whitespace-pre-wrap break-all font-mono text-xs text-gray-900" x-text="result.pathInfo.query"></pre>
                                </div>
                                <button
                                    type="button"
                                    @click="copyText(result.pathInfo.query, 'copy-query')"
                                    class="shrink-0 text-xs font-medium text-primary-700 hover:text-primary-800"
                                    x-text="copyLabel('copy-query', 'Copy')"
                                ></button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm" x-show="result.kind === 'request' && result.pathInfo.params.length">
                <button
                    type="button"
                    @click="secQuery = !secQuery"
                    class="flex w-full items-center justify-between gap-4 text-left"
                    :aria-expanded="secQuery"
                >
                    <div>
                        <h2 class="text-sm font-semibold text-gray-950">Query parameters</h2>
                        <p class="mt-1 text-sm text-gray-600">Decoded key/value view. Duplicate names remain visible as separate rows.</p>
                    </div>
                    <span class="shrink-0 rounded-full border border-gray-200 bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700" x-text="secQuery ? 'Hide' : 'Show'"></span>
                </button>

                <div x-show="secQuery" x-collapse class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-left text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">
                                <th class="py-2 pr-4">Name</th>
                                <th class="py-2 pr-4">Value</th>
                                <th class="py-2 text-right">Copy</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, ri) in result.pathInfo.params" :key="'param-' + ri">
                                <tr class="border-b border-gray-100 align-top last:border-b-0">
                                    <td class="py-2 pr-4 font-mono text-xs text-gray-900" x-text="row.name"></td>
                                    <td class="py-2 pr-4 font-mono text-xs text-gray-800 break-all" x-text="row.displayValue"></td>
                                    <td class="py-2 text-right">
                                        <button
                                            type="button"
                                            @click="copyText(row.value, 'param-' + ri)"
                                            class="text-xs font-medium text-primary-700 hover:text-primary-800"
                                            x-text="copyLabel('param-' + ri, 'Copy')"
                                        ></button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <button
                    type="button"
                    @click="secHeaders = !secHeaders"
                    class="flex w-full items-center justify-between gap-4 text-left"
                    :aria-expanded="secHeaders"
                >
                    <div>
                        <h2 class="text-sm font-semibold text-gray-950">Headers</h2>
                        <p class="mt-1 text-sm text-gray-600"><span x-text="result.headers.length"></span> parsed header lines. Folded lines are merged into the previous header.</p>
                    </div>
                    <span class="shrink-0 rounded-full border border-gray-200 bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700" x-text="secHeaders ? 'Hide' : 'Show'"></span>
                </button>

                <div x-show="secHeaders" x-collapse class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <tbody>
                            <template x-for="(header, hi) in result.headers" :key="'header-' + hi">
                                <tr class="border-b border-gray-100 align-top last:border-b-0">
                                    <td class="py-2 pr-3 font-mono text-xs whitespace-nowrap text-gray-600" x-text="header.name + ':'"></td>
                                    <td class="py-2 pr-4 font-mono text-xs text-gray-900 break-all">
                                        <span x-html="header.displayValue"></span>
                                    </td>
                                    <td class="py-2 text-right">
                                        <button
                                            type="button"
                                            @click="copyText(header.rawValue, 'header-' + hi)"
                                            class="text-xs font-medium text-primary-700 hover:text-primary-800"
                                            x-text="copyLabel('header-' + hi, 'Copy')"
                                        ></button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm" x-show="result.cookies.length > 0">
                <button
                    type="button"
                    @click="secCookies = !secCookies"
                    class="flex w-full items-center justify-between gap-4 text-left"
                    :aria-expanded="secCookies"
                >
                    <div>
                        <h2 class="text-sm font-semibold text-gray-950" x-text="result.kind === 'request' ? 'Cookies' : 'Set-Cookie lines'"></h2>
                        <p class="mt-1 text-sm text-gray-600" x-text="result.kind === 'request' ? 'Cookie header split into individual pairs.' : 'Each Set-Cookie line with visible attributes and quick review notes.'"></p>
                    </div>
                    <span class="shrink-0 rounded-full border border-gray-200 bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700" x-text="secCookies ? 'Hide' : 'Show'"></span>
                </button>

                <div x-show="secCookies" x-collapse class="mt-4 space-y-3">
                    <template x-for="(cookie, ci) in result.cookies" :key="'cookie-' + ci">
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 font-mono text-xs text-gray-900">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0 break-all" x-html="cookie.display"></div>
                                <button
                                    type="button"
                                    @click="copyText(cookie.raw, 'cookie-' + ci)"
                                    class="shrink-0 text-xs font-medium text-primary-700 hover:text-primary-800"
                                    x-text="copyLabel('cookie-' + ci, 'Copy')"
                                ></button>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2" x-show="cookie.flags.length">
                                <template x-for="flag in cookie.flags" :key="flag">
                                    <span class="rounded-full border border-gray-200 bg-white px-2 py-1 text-[11px] font-medium text-gray-700" x-text="flag"></span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm" x-show="result.auth.length > 0">
                <div class="flex flex-col gap-1">
                    <h2 class="text-sm font-semibold text-gray-950">Authorization and auth-related headers</h2>
                    <p class="text-sm text-gray-600">Potentially sensitive headers stay masked in the UI. Use copy only when you need the original value.</p>
                </div>

                <ul class="mt-4 space-y-3 text-sm">
                    <template x-for="(authHeader, ai) in result.auth" :key="'auth-' + ai">
                        <li class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0 break-all font-mono text-xs text-gray-900">
                                    <span class="text-gray-500" x-text="authHeader.label + ': '"></span>
                                    <span x-html="authHeader.display"></span>
                                </div>
                                <button
                                    type="button"
                                    @click="copyText(authHeader.raw, 'auth-' + ai)"
                                    class="shrink-0 text-xs font-medium text-primary-700 hover:text-primary-800"
                                    x-text="copyLabel('auth-' + ai, 'Copy')"
                                ></button>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm" x-show="result.bodyPreview !== null">
                <button
                    type="button"
                    @click="secBody = !secBody"
                    class="flex w-full items-center justify-between gap-4 text-left"
                    :aria-expanded="secBody"
                >
                    <div>
                        <h2 class="text-sm font-semibold text-gray-950">Body</h2>
                        <p class="mt-1 text-sm text-gray-600">Preview, light decoding, and safe copy actions. Expensive formatting is skipped for very large payloads.</p>
                    </div>
                    <span class="shrink-0 rounded-full border border-gray-200 bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700" x-text="secBody ? 'Hide' : 'Show'"></span>
                </button>

                <div x-show="secBody" x-collapse class="mt-4 space-y-4">
                    <div class="flex flex-wrap gap-2 text-xs text-gray-600">
                        <span class="rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1" x-text="'Body size: ' + result.bodySizeLabel"></span>
                        <span class="rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1" x-show="result.contentType" x-text="'Content-Type: ' + result.contentType"></span>
                    </div>

                    <template x-if="result.bodyNotes.length">
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500">Body notes</p>
                            <ul class="mt-2 space-y-2 text-sm text-gray-700">
                                <template x-for="(note, idx) in result.bodyNotes" :key="'body-note-' + idx">
                                    <li x-text="note"></li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <template x-if="result.bodyJsonFormatted">
                        <div>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">JSON (formatted)</p>
                                <button
                                    type="button"
                                    @click="copyText(result.bodyJsonFormatted, 'copy-json')"
                                    class="text-xs font-medium text-primary-700 hover:text-primary-800"
                                    x-text="copyLabel('copy-json', 'Copy JSON')"
                                ></button>
                            </div>
                            <pre class="mt-2 max-h-96 overflow-auto rounded-2xl bg-gray-950 p-3 font-mono text-xs text-gray-100" x-html="result.bodyJsonHighlight"></pre>
                        </div>
                    </template>

                    <template x-if="result.bodyFormFields && result.bodyFormFields.length">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Form fields</p>
                            <div class="mt-2 overflow-x-auto rounded-2xl border border-gray-200 bg-gray-50 px-3 py-2">
                                <table class="min-w-full text-xs">
                                    <tbody>
                                        <template x-for="(field, fi) in result.bodyFormFields" :key="'field-' + fi">
                                            <tr class="border-b border-gray-200 align-top last:border-b-0">
                                                <td class="py-2 pr-3 font-mono text-gray-900" x-text="field.name"></td>
                                                <td class="py-2 pr-3 font-mono text-gray-800 break-all" x-text="field.display"></td>
                                                <td class="py-2 text-right">
                                                    <button
                                                        type="button"
                                                        @click="copyText(field.value, 'field-' + fi)"
                                                        class="text-xs font-medium text-primary-700 hover:text-primary-800"
                                                        x-text="copyLabel('field-' + fi, 'Copy')"
                                                    ></button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>

                    <div x-show="result.bodyPreview !== null">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500">Raw preview</p>
                            <button
                                type="button"
                                @click="copyText(result.bodyRaw, 'copy-body')"
                                class="text-xs font-medium text-primary-700 hover:text-primary-800"
                                x-text="copyLabel('copy-body', 'Copy body')"
                            ></button>
                        </div>
                        <pre class="mt-2 max-h-96 overflow-auto rounded-2xl border border-gray-200 bg-gray-50 p-3 font-mono text-xs text-gray-900 whitespace-pre-wrap break-all" x-text="result.bodyPreview"></pre>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template x-if="!result && !parseError && !raw.trim()">
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-4 py-10 text-center shadow-sm">
            <p class="text-sm font-semibold text-gray-900">Nothing parsed yet</p>
            <p class="mx-auto mt-2 max-w-2xl text-sm leading-relaxed text-gray-600">
                Paste a raw HTTP request or response, then click <span class="font-semibold text-gray-900">Parse locally</span>.
                The parser handles common copies from Burp, curl verbose output, browser/devtools, and logs as long as an HTTP start line is present.
            </p>
        </div>
    </template>
</div>

@push('scripts')
<script>
(function () {
    const REQ_LINE = /^(GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS|CONNECT|TRACE|PRI)\s+(\S+)\s+HTTP\/(\d(?:\.\d)?)\s*$/i;
    const STATUS_LINE = /^HTTP\/(\d(?:\.\d)?)\s+(\d{3})\s*(.*)$/i;
    const BODY_PREVIEW_LIMIT = 24000;
    const JSON_FORMAT_LIMIT = 250000;
    const FORM_PARSE_LIMIT = 150000;
    const SECRET_SCAN_LIMIT = 250000;
    const LARGE_INPUT_NOTICE = 750000;

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatBytes(bytes) {
        var units = ['B', 'KB', 'MB', 'GB'];
        var value = Math.max(bytes || 0, 0);
        var unitIndex = 0;

        while (value >= 1024 && unitIndex < units.length - 1) {
            value = value / 1024;
            unitIndex++;
        }

        return (unitIndex === 0 ? value : value.toFixed(value >= 10 ? 0 : 1)) + ' ' + units[unitIndex];
    }

    function textSize(s) {
        return new TextEncoder().encode(String(s || '')).length;
    }

    function truncateDisplay(s, max) {
        if (s.length <= max) return s;
        return s.slice(0, max) + '…';
    }

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
        var lower = name.toLowerCase();
        var header = headers.find(function (entry) {
            return entry.name.toLowerCase() === lower;
        });
        return header ? header.value : '';
    }

    function stripTransportPrefix(line) {
        if (/^[<>] ?$/.test(line)) return '';
        return /^[<>](?:\s|$)/.test(line) ? line.slice(1).replace(/^ /, '') : line;
    }

    function isPreludeNoise(line) {
        var trimmed = line.trim();
        if (!trimmed) return false;

        return (
            trimmed.startsWith('* ') ||
            /^curl(?:\s|$)/i.test(trimmed) ||
            /^wget(?:\s|$)/i.test(trimmed) ||
            /^echo(?:\s|$)/i.test(trimmed) ||
            /^\$?\s*curl(?:\s|$)/i.test(trimmed) ||
            /^\$?\s*wget(?:\s|$)/i.test(trimmed) ||
            /^note:/i.test(trimmed) ||
            /^warning:/i.test(trimmed) ||
            /^curl:/i.test(trimmed) ||
            /^[{}] \[\d+ bytes data\]$/i.test(trimmed)
        );
    }

    function findStartLine(lines) {
        for (var i = 0; i < lines.length; i++) {
            var candidate = stripTransportPrefix(lines[i]).replace(/\s+$/, '');
            if (REQ_LINE.test(candidate) || STATUS_LINE.test(candidate)) {
                return {
                    index: i,
                    line: candidate,
                };
            }
        }

        return null;
    }

    function parseHeadersBlock(lines, startIdx) {
        var headers = [];
        var invalidHeaderLines = [];
        var i = startIdx;

        for (; i < lines.length; i++) {
            var originalLine = lines[i];
            var line = stripTransportPrefix(originalLine);

            if (line === '') {
                i++;
                break;
            }

            if (/^[ \t]/.test(line) && headers.length > 0) {
                headers[headers.length - 1].value += ' ' + line.trim();
                continue;
            }

            var idx = line.indexOf(':');
            if (idx === -1) {
                invalidHeaderLines.push(line);
                continue;
            }

            var name = line.slice(0, idx).trim();
            if (!name) {
                invalidHeaderLines.push(line);
                continue;
            }

            headers.push({
                name: name,
                value: line.slice(idx + 1).trim(),
            });
        }

        return {
            headers: headers,
            nextIndex: i,
            invalidHeaderLines: invalidHeaderLines,
        };
    }

    function parseMessage(raw) {
        var text = String(raw || '')
            .replace(/\uFEFF/g, '')
            .replace(/\r\n/g, '\n')
            .replace(/\r/g, '\n');

        var lines = text.split('\n');
        var start = findStartLine(lines);

        if (!start) {
            throw new Error(
                'No HTTP start line found. Include a line like "GET /path HTTP/1.1" or "HTTP/1.1 200 OK".'
            );
        }

        var notes = [];
        var leadingLines = lines.slice(0, start.index).filter(function (line) {
            return line.trim() !== '';
        });
        if (leadingLines.length > 0) {
            notes.push('Skipped ' + leadingLines.length + ' line' + (leadingLines.length === 1 ? '' : 's') + ' before the HTTP start line.');
        }

        var ignorableCount = leadingLines.filter(isPreludeNoise).length;
        if (ignorableCount > 0) {
            notes.push('Ignored wrapper or transcript lines commonly added by curl, Burp, or shell output.');
        }

        if (stripTransportPrefix(lines[start.index]) !== lines[start.index]) {
            notes.push('Removed curl-style > / < transport prefixes from the start line and headers.');
        }

        var startLine = start.line;
        var kind = null;
        var request = null;
        var response = null;

        if (STATUS_LINE.test(startLine)) {
            kind = 'response';
            var statusMatch = startLine.match(STATUS_LINE);
            response = {
                httpVersion: statusMatch[1],
                statusCode: statusMatch[2],
                reason: (statusMatch[3] || '').trim(),
            };
        } else if (REQ_LINE.test(startLine)) {
            kind = 'request';
            var requestMatch = startLine.match(REQ_LINE);
            request = {
                method: requestMatch[1].toUpperCase(),
                target: requestMatch[2],
                httpVersion: requestMatch[3],
            };
        }

        var messageLines = lines.slice(start.index);
        var headerBlock = parseHeadersBlock(messageLines, 1);
        var headers = headerBlock.headers;
        var body = messageLines.slice(headerBlock.nextIndex).join('\n');

        if (headerBlock.invalidHeaderLines.length > 0) {
            notes.push(
                'Ignored ' +
                    headerBlock.invalidHeaderLines.length +
                    ' malformed header line' +
                    (headerBlock.invalidHeaderLines.length === 1 ? '' : 's') +
                    ' that did not look like "Header-Name: value".'
            );
        }

        return {
            kind: kind,
            startLine: startLine,
            request: request,
            response: response,
            headers: headers,
            body: body,
            notes: notes,
        };
    }

    function parseQueryFromTarget(target) {
        try {
            var absoluteUrl = new URL(target);
            var absoluteQuery = absoluteUrl.search.startsWith('?') ? absoluteUrl.search.slice(1) : absoluteUrl.search;
            return buildQueryData(absoluteUrl.pathname || '/', absoluteQuery, absoluteUrl.toString());
        } catch (e) {
            var qIdx = target.indexOf('?');
            if (qIdx === -1) {
                return buildQueryData(target, '', null);
            }

            return buildQueryData(target.slice(0, qIdx), target.slice(qIdx + 1), null);
        }
    }

    function buildQueryData(path, query, absoluteUrl) {
        var params = [];
        var counts = {};

        try {
            var searchParams = new URLSearchParams(query);
            searchParams.forEach(function (value, key) {
                params.push({ name: key, value: value });
                counts[key] = (counts[key] || 0) + 1;
            });
        } catch (e) {
            params = [];
        }

        return {
            path: path || '/',
            query: query,
            params: params,
            duplicateNames: Object.keys(counts).filter(function (key) {
                return counts[key] > 1;
            }),
            absoluteUrl: absoluteUrl,
        };
    }

    function maybeSensitiveValue(value) {
        if (value.length > 48) {
            return truncateDisplay(value, 24) + '… (truncated)';
        }

        return value;
    }

    function maskAuthValue(scheme, rest) {
        var normalizedScheme = (scheme || '').toLowerCase();
        if (normalizedScheme === 'bearer' && rest.length > 12) {
            return 'Bearer ' + rest.slice(0, 6) + '…' + rest.slice(-4) + ' (truncated)';
        }

        if (normalizedScheme === 'basic' && rest.length > 8) {
            return 'Basic … (truncated; copy for full value)';
        }

        if (rest.length > 40) {
            return (scheme ? scheme + ' ' : '') + truncateDisplay(rest, 20) + '… (truncated)';
        }

        return (scheme ? scheme + ' ' : '') + rest;
    }

    function parseAuthorizationDisplay(value) {
        var idx = value.indexOf(' ');
        if (idx === -1) {
            return { display: escapeHtml(maybeSensitiveValue(value)), raw: value };
        }

        var scheme = value.slice(0, idx).trim();
        var rest = value.slice(idx + 1).trim();

        return {
            display: escapeHtml(maskAuthValue(scheme, rest)),
            raw: value,
        };
    }

    function parseRequestCookies(cookieHeader) {
        if (!cookieHeader) return [];

        return cookieHeader
            .split(';')
            .map(function (part) {
                return part.trim();
            })
            .filter(Boolean)
            .map(function (part) {
                var eq = part.indexOf('=');
                if (eq === -1) {
                    return {
                        name: '',
                        raw: part,
                        display: escapeHtml(part),
                        flags: [],
                    };
                }

                var name = part.slice(0, eq).trim();
                var value = part.slice(eq + 1);

                return {
                    name: name,
                    raw: part,
                    display: '<span class="font-semibold text-primary-700">' + escapeHtml(name) + '</span>=' + escapeHtml(maybeSensitiveValue(value)),
                    flags: [],
                };
            });
    }

    function parseSetCookieEntries(headers) {
        var out = [];

        headers.forEach(function (header) {
            if (header.name.toLowerCase() !== 'set-cookie') return;

            var value = header.value;
            var flags = [];

            value.split(';').slice(1).forEach(function (segment) {
                var trimmed = segment.trim();
                if (!trimmed) return;

                var lower = trimmed.toLowerCase();
                if (
                    lower === 'secure' ||
                    lower === 'httponly' ||
                    lower.indexOf('samesite') === 0 ||
                    lower.indexOf('path') === 0 ||
                    lower.indexOf('domain') === 0 ||
                    lower.indexOf('max-age') === 0 ||
                    lower.indexOf('expires') === 0
                ) {
                    flags.push(trimmed);
                }
            });

            out.push({
                name: 'Set-Cookie',
                raw: value,
                display: escapeHtml(value.length > 220 ? value.slice(0, 220) + '…' : value),
                flags: flags,
            });
        });

        return out;
    }

    function cookieFlagTriage(setCookieObjs) {
        var warnings = [];

        setCookieObjs.forEach(function (cookie) {
            var lower = cookie.raw.toLowerCase();
            var namePart = cookie.raw.split(';')[0] || '';

            if (!/;\s*secure/i.test(lower) && !/^__(Host|Secure)-/i.test(namePart)) {
                warnings.push({
                    title: 'Set-Cookie is missing Secure',
                    detail: 'Cookie line: ' + truncateDisplay(namePart, 96),
                    why: 'Worth checking if this cookie should only travel over HTTPS.',
                });
            }

            if (!/;\s*httponly/i.test(lower)) {
                warnings.push({
                    title: 'Set-Cookie is missing HttpOnly',
                    detail: 'Cookie line: ' + truncateDisplay(namePart, 96),
                    why: 'Review recommended if this cookie carries a session or other sensitive state.',
                });
            }

            if (!/;\s*samesite=/i.test(lower)) {
                warnings.push({
                    title: 'Set-Cookie is missing SameSite',
                    detail: 'Cookie line: ' + truncateDisplay(namePart, 96),
                    why: 'Cross-site behaviour may still be acceptable, but it is worth comparing against your intended session design.',
                });
            }
        });

        return warnings;
    }

    function pathIdTriage(path) {
        var warnings = [];

        path.split('/').forEach(function (segment) {
            if (!segment) return;

            if (/^\d{6,}$/.test(segment)) {
                warnings.push({
                    title: 'Long numeric path segment',
                    detail: 'Segment: ' + segment,
                    why: 'Often an object identifier. Verify that authorization is enforced server-side.',
                });
            }

            if (/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(segment)) {
                warnings.push({
                    title: 'UUID-like path segment',
                    detail: 'Segment: ' + segment,
                    why: 'If this identifies a resource, review whether access control depends on more than obscurity.',
                });
            }
        });

        return warnings;
    }

    function bodySecretScan(body) {
        var warnings = [];
        if (!body || body.length < 10) return warnings;

        var scanWindow = body.slice(0, SECRET_SCAN_LIMIT);

        if (/eyJ[A-Za-z0-9_-]{10,}\.[A-Za-z0-9_-]{10,}\.[A-Za-z0-9_-]{10,}/.test(scanWindow)) {
            warnings.push({
                title: 'JWT-like token visible in the body',
                detail: 'A three-part base64url string resembling a JWT appears in the pasted content.',
                why: 'Pattern only. If it is live, treat it as sensitive and confirm expiry and scope before sharing the message.',
            });
        }

        if (/\bsk_(?:live|test)_[a-zA-Z0-9]{8,}/.test(scanWindow)) {
            warnings.push({
                title: 'API key-like secret pattern in the body',
                detail: 'A substring similar to a Stripe secret key is present.',
                why: 'Could be a real credential or a test fixture. Review recommended before pasting this elsewhere.',
            });
        }

        if (/\bAIza[0-9A-Za-z_-]{20,}/.test(scanWindow)) {
            warnings.push({
                title: 'Google API key-like pattern in the body',
                detail: 'An AIza-style key pattern appears in the pasted content.',
                why: 'Pattern only. Confirm whether the value is real and expected in this message.',
            });
        }

        return warnings;
    }

    function responseHeaderTriage(headers, contentType, cookies) {
        var warnings = [];
        var map = {};

        headers.forEach(function (header) {
            map[header.name.toLowerCase()] = header.value;
        });

        var isHtmlLike = /(text\/html|application\/xhtml\+xml|image\/svg\+xml)/i.test(contentType || '');
        var isRenderableText = isHtmlLike || /(json|javascript|css|xml)/i.test(contentType || '');
        var missing = [];

        if (cookies.length > 0 && !map['strict-transport-security']) {
            missing.push('Strict-Transport-Security');
        }

        if (isRenderableText && !map['x-content-type-options']) {
            missing.push('X-Content-Type-Options');
        }

        if (isHtmlLike && !map['content-security-policy']) {
            missing.push('Content-Security-Policy');
        }

        if (isHtmlLike && !map['referrer-policy']) {
            missing.push('Referrer-Policy');
        }

        if (isHtmlLike && !map['x-frame-options'] && !/frame-ancestors/i.test(map['content-security-policy'] || '')) {
            missing.push('X-Frame-Options or CSP frame-ancestors');
        }

        if (missing.length > 0) {
            warnings.push({
                title: 'Common browser hardening headers are absent here',
                detail: 'Not present in this pasted response: ' + missing.join(', '),
                why: 'Absence is not always a bug. Compare against the normal baseline for this endpoint and content type.',
            });
        }

        var cors = map['access-control-allow-origin'];
        if (cors === '*') {
            warnings.push({
                title: 'Access-Control-Allow-Origin is set to *',
                detail: 'Any origin may read this response in CORS terms.',
                why: 'Sometimes intentional for public APIs or assets. Review if the response contains user-specific or internal data.',
            });
        }

        var allowCredentials = map['access-control-allow-credentials'];
        if (cors === '*' && allowCredentials && allowCredentials.toLowerCase() === 'true') {
            warnings.push({
                title: 'CORS headers look inconsistent',
                detail: 'Access-Control-Allow-Origin: * appears alongside Access-Control-Allow-Credentials: true.',
                why: 'Browsers usually reject this combination. It is worth checking whether the response was copied accurately or the server is misconfigured.',
            });
        }

        var csp = map['content-security-policy'] || map['content-security-policy-report-only'];
        if (csp && /\bunsafe-inline\b/i.test(csp)) {
            warnings.push({
                title: 'CSP includes unsafe-inline',
                detail: 'Inline script or style execution appears to be allowed.',
                why: 'This may be a deliberate tradeoff, but review recommended if you expected a strict policy.',
            });
        }

        if (csp && /\bunsafe-eval\b/i.test(csp)) {
            warnings.push({
                title: 'CSP includes unsafe-eval',
                detail: 'Eval-like execution appears to be allowed.',
                why: 'This can weaken script execution controls. Review recommended for production traffic.',
            });
        }

        if (map['x-powered-by']) {
            warnings.push({
                title: 'X-Powered-By header is present',
                detail: map['x-powered-by'],
                why: 'Worth checking whether framework disclosure is intentional.',
            });
        }

        if (map['server'] && /\d+\.\d+/.test(map['server'])) {
            warnings.push({
                title: 'Server header appears to expose a version',
                detail: map['server'],
                why: 'Version strings can narrow research or fingerprinting. Confirm whether this disclosure is acceptable.',
            });
        }

        ['x-debug', 'x-debug-token', 'x-debug-token-link'].forEach(function (name) {
            if (map[name]) {
                warnings.push({
                    title: 'Debug-oriented response header is present',
                    detail: name + ': ' + map[name],
                    why: 'Worth checking whether this should be visible outside a local or authenticated debugging workflow.',
                });
            }
        });

        return warnings;
    }

    function requestHeaderTriage(headers, pathInfo) {
        var warnings = [];
        var lowerNames = new Set();

        headers.forEach(function (header) {
            lowerNames.add(header.name.toLowerCase());
        });

        var sensitiveHeaders = [];
        if (lowerNames.has('authorization')) sensitiveHeaders.push('Authorization');
        if (lowerNames.has('proxy-authorization')) sensitiveHeaders.push('Proxy-Authorization');
        if (lowerNames.has('cookie')) sensitiveHeaders.push('Cookie');

        if (sensitiveHeaders.length > 0) {
            warnings.push({
                title: 'Sensitive request headers are visible in the paste',
                detail: sensitiveHeaders.join(', '),
                why: 'Not necessarily a security issue in the application, but worth redacting before sharing captures or bug reports.',
            });
        }

        if (pathInfo.duplicateNames.length > 0) {
            warnings.push({
                title: 'Duplicate query parameter names',
                detail: pathInfo.duplicateNames.join(', '),
                why: 'Different servers and proxies resolve duplicates differently. Review if parameter smuggling or parser quirks matter for this target.',
            });
        }

        return warnings;
    }

    function dedupeWarnings(items) {
        var seen = new Set();
        return items.filter(function (item) {
            var key = item.title + '|' + item.detail;
            if (seen.has(key)) return false;
            seen.add(key);
            return true;
        });
    }

    function enrichParsed(parsed) {
        var kind = parsed.kind;
        var notes = parsed.notes ? parsed.notes.slice() : [];
        var headers = parsed.headers.map(function (header) {
            var displayHtml = escapeHtml(header.value);
            if (header.name.toLowerCase() === 'authorization' || header.name.toLowerCase() === 'proxy-authorization') {
                displayHtml = parseAuthorizationDisplay(header.value).display;
            }

            return {
                name: header.name,
                rawValue: header.value,
                displayValue: displayHtml,
            };
        });

        var contentType = headerLookup(parsed.headers, 'Content-Type');
        var host = headerLookup(parsed.headers, 'Host');
        var fullUrl = null;
        var pathInfo = {
            path: '',
            query: '',
            params: [],
            duplicateNames: [],
            absoluteUrl: null,
        };

        if (kind === 'request' && parsed.request) {
            var requestPath = parseQueryFromTarget(parsed.request.target);
            pathInfo.path = requestPath.path;
            pathInfo.query = requestPath.query;
            pathInfo.duplicateNames = requestPath.duplicateNames;
            pathInfo.absoluteUrl = requestPath.absoluteUrl;
            pathInfo.params = requestPath.params.map(function (param) {
                return {
                    name: param.name,
                    value: param.value,
                    displayValue: maybeSensitiveValue(param.value),
                };
            });

            if (requestPath.absoluteUrl) {
                fullUrl = requestPath.absoluteUrl;
            } else if (host) {
                fullUrl = 'https://' + host + (requestPath.path.startsWith('/') ? requestPath.path : '/' + requestPath.path);
                if (requestPath.query) {
                    fullUrl += '?' + requestPath.query;
                }
            }
        }

        var cookies = kind === 'request'
            ? parseRequestCookies(headerLookup(parsed.headers, 'Cookie'))
            : parseSetCookieEntries(parsed.headers);

        var auth = headers
            .filter(function (header) {
                var lower = header.name.toLowerCase();
                return lower === 'authorization' || lower === 'proxy-authorization';
            })
            .map(function (header) {
                return {
                    label: header.name,
                    display: header.displayValue,
                    raw: header.rawValue,
                };
            });

        var body = parsed.body || '';
        var bodySize = textSize(body);
        var bodyPreview = body.length ? body : null;
        var bodyJsonFormatted = null;
        var bodyJsonHighlight = null;
        var bodyFormFields = null;
        var bodyNotes = [];

        if (body && bodySize > BODY_PREVIEW_LIMIT) {
            bodyPreview = body.slice(0, BODY_PREVIEW_LIMIT) + '\n\n[Preview truncated after ' + formatBytes(BODY_PREVIEW_LIMIT) + '. Copy body for the full content.]';
            bodyNotes.push('Raw preview was truncated after ' + formatBytes(BODY_PREVIEW_LIMIT) + ' to keep the UI responsive.');
        }

        var isJson = /json/i.test(contentType) || /^[\s]*[{[]/.test(body);
        var isForm = /application\/x-www-form-urlencoded/i.test(contentType);

        if (body && isJson) {
            if (bodySize <= JSON_FORMAT_LIMIT) {
                try {
                    bodyJsonFormatted = JSON.stringify(JSON.parse(body), null, 2);
                    bodyJsonHighlight = highlightJson(bodyJsonFormatted);
                } catch (e) {
                    bodyNotes.push('Body looks JSON-like but could not be parsed cleanly. Showing the raw preview instead.');
                }
            } else {
                bodyNotes.push('Skipped JSON pretty-printing for a large body (' + formatBytes(bodySize) + ').');
            }
        }

        if (body && isForm && !bodyJsonFormatted) {
            if (bodySize <= FORM_PARSE_LIMIT) {
                try {
                    bodyFormFields = [];
                    new URLSearchParams(body).forEach(function (value, key) {
                        bodyFormFields.push({
                            name: key,
                            value: value,
                            display: maybeSensitiveValue(value),
                        });
                    });
                } catch (e2) {
                    bodyNotes.push('Form parsing was skipped because the body was not valid x-www-form-urlencoded content.');
                }
            } else {
                bodyNotes.push('Skipped form-field expansion for a large body (' + formatBytes(bodySize) + ').');
            }
        }

        if (bodySize > SECRET_SCAN_LIMIT) {
            bodyNotes.push('Secret-pattern checks only scanned the first ' + formatBytes(SECRET_SCAN_LIMIT) + ' of the body.');
        }

        var warnings = [];
        if (kind === 'response') {
            warnings = warnings.concat(responseHeaderTriage(parsed.headers, contentType, cookies));
            warnings = warnings.concat(cookieFlagTriage(cookies));
        } else {
            warnings = warnings.concat(requestHeaderTriage(parsed.headers, pathInfo));
            warnings = warnings.concat(pathIdTriage(pathInfo.path));
        }

        warnings = warnings.concat(bodySecretScan(body));
        warnings = dedupeWarnings(warnings);

        function buildNormalized() {
            var lines = [];

            if (kind === 'request' && parsed.request) {
                lines.push(parsed.request.method + ' ' + parsed.request.target + ' HTTP/' + parsed.request.httpVersion);
            } else if (kind === 'response' && parsed.response) {
                lines.push('HTTP/' + parsed.response.httpVersion + ' ' + parsed.response.statusCode + (parsed.response.reason ? ' ' + parsed.response.reason : ''));
            }

            parsed.headers.forEach(function (header) {
                lines.push(header.name + ': ' + header.value);
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
            bodyNotes: bodyNotes,
            bodySizeLabel: formatBytes(bodySize),
            warnings: warnings,
            notes: notes,
            normalizedMessage: buildNormalized(),
        };
    }

    window.httpParserLib = {
        parseMessage: parseMessage,
        enrich: enrichParsed,
        formatBytes: formatBytes,
        largeInputNotice: LARGE_INPUT_NOTICE,
    };
})();

function httpParser() {
    return {
        raw: '',
        parseError: null,
        result: null,
        normalizedMessage: '',
        isParsing: false,
        secPath: true,
        secQuery: true,
        secHeaders: true,
        secCookies: true,
        secBody: true,
        copiedKey: null,
        copyTimer: null,

        inputStats() {
            var chars = this.raw.length;
            var bytes = window.httpParserLib.formatBytes(new TextEncoder().encode(this.raw).length);
            var lines = this.raw ? this.raw.split(/\r\n|\r|\n/).length : 0;
            return chars + ' chars · ' + lines + ' lines · ' + bytes;
        },

        isLargeInput() {
            return this.raw.length >= window.httpParserLib.largeInputNotice;
        },

        loadSampleRequest() {
            this.raw =
                '> GET https://api.example.com/users/12345/orders?id=1&id=2&ref=abc HTTP/1.1\r\n' +
                '> Host: api.example.com\r\n' +
                '> User-Agent: Mozilla/5.0 (compatible; Research/1.0)\r\n' +
                '> Accept: application/json\r\n' +
                '> Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.signature_here\r\n' +
                '> Cookie: session=abc123; csrf=def456\r\n' +
                '>\r\n';
            this.parseError = null;
            this.result = null;
            this.$nextTick(() => this.$refs.raw.focus());
        },

        loadSampleResponse() {
            this.raw =
                '* Connected to api.example.com (203.0.113.10) port 443\r\n' +
                '< HTTP/1.1 200 OK\r\n' +
                '< Date: Mon, 01 Jan 2024 12:00:00 GMT\r\n' +
                '< Server: nginx/1.18.0\r\n' +
                '< X-Powered-By: Express/4.17.1\r\n' +
                '< Access-Control-Allow-Origin: *\r\n' +
                '< Set-Cookie: sid=opaque; Path=/; HttpOnly\r\n' +
                '< Set-Cookie: theme=dark; Path=/\r\n' +
                '< Content-Type: application/json; charset=utf-8\r\n' +
                '<\r\n' +
                '{"user":{"id":12345,"role":"admin"},"token":"eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxIn0.sig"}';
            this.parseError = null;
            this.result = null;
            this.$nextTick(() => this.$refs.raw.focus());
        },

        clearAll() {
            this.raw = '';
            this.parseError = null;
            this.result = null;
            this.normalizedMessage = '';
            this.$nextTick(() => this.$refs.raw.focus());
        },

        parse() {
            var self = this;
            if (!this.raw.trim() || this.isParsing) return;

            this.parseError = null;
            this.result = null;
            this.normalizedMessage = '';
            this.isParsing = true;

            var run = function () {
                try {
                    var parsed = window.httpParserLib.parseMessage(self.raw);
                    var enriched = window.httpParserLib.enrich(parsed);
                    self.normalizedMessage = enriched.normalizedMessage;
                    self.result = enriched;
                } catch (e) {
                    self.parseError = e.message || String(e);
                } finally {
                    self.isParsing = false;
                }
            };

            if (window.requestIdleCallback) {
                window.requestIdleCallback(run, { timeout: 120 });
            } else {
                window.setTimeout(run, 0);
            }
        },

        copyLabel(key, fallback) {
            return this.copiedKey === key ? 'Copied!' : fallback;
        },

        copyText(text, key) {
            var self = this;
            if (!text && text !== '') return;

            navigator.clipboard.writeText(text).then(function () {
                self.copiedKey = key;
                clearTimeout(self.copyTimer);
                self.copyTimer = setTimeout(function () {
                    self.copiedKey = null;
                }, 2000);
            });
        },
    };
}
</script>
@endpush
