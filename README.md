# urlcv/http-parser

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Browser-side raw HTTP request and response parser with structured breakdown and lightweight security triage. Powers the free tool at **[urlcv.com/tools/http-parser](https://urlcv.com/tools/http-parser)** on **[URLCV](https://urlcv.com)**.

---

## What it does

Paste a **raw HTTP request** or **raw HTTP response** (e.g. from Burp Suite, browser devtools, logs, or a `curl -v` transcript). Parsing runs **entirely in the browser** — nothing is uploaded.

You get:

- **Requests:** method, path, query string, version, host, headers, cookies, authorization (display-masked), content type, body, JSON or form fields when applicable  
- **Responses:** status line and code, headers, `Set-Cookie` lines, content type, body preview, JSON when applicable  
- **Heuristic triage:** cookie flags, CORS/CSP hints, token-like patterns in the body, duplicate query parameters, long numeric or UUID-like path segments, version leakage via headers, and a small set of context-sensitive missing-header checks — phrased cautiously (*worth checking*, *review recommended*)
- **Soft failure handling:** folded headers, invalid header lines ignored with notes, wrapper lines before the HTTP start line skipped when possible
- **Large paste safeguards:** preview truncation and skipped pretty-printing when payloads are big enough to hurt responsiveness

This is **not** a scanner, fuzzer, or exploit tool — only a parser and manual review aid.

---

## SEO / discovery

Useful for people searching for: raw HTTP parser, HTTP request parser, HTTP response parser, parse raw request headers, inspect HTTP request online, security header check from raw response.

---

## Requirements

- PHP **8.2+** (for the Laravel tool adapter only; parsing is JavaScript in the browser)

---

## Installation (Laravel / URLCV)

```bash
composer require urlcv/http-parser
php artisan tools:sync
```

Register the tool class in `config/tools.php` if not auto-discovered (see URLCV’s tool docs).

---

## Development

The package provides:

- `URLCV\HttpParser\Laravel\HttpParserTool` — implements `ToolInterface` (`mode`: `frontend`)
- `URLCV\HttpParser\Laravel\HttpParserServiceProvider` — loads the Blade view namespace `http-parser::`

---

## Privacy

All parsing happens client-side in the user’s browser. The UI promise should stay accurate: no pasted traffic is sent to URLCV or third parties. Do not paste live secrets into shared machines; clear the page when finished.

---

## Links

- Live tool: [https://urlcv.com/tools/http-parser](https://urlcv.com/tools/http-parser)  
- URLCV: [https://urlcv.com](https://urlcv.com)
