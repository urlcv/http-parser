<?php

declare(strict_types=1);

namespace URLCV\HttpParser\Laravel;

use App\Tools\Contracts\ToolInterface;

class HttpParserTool implements ToolInterface
{
    public function slug(): string
    {
        return 'http-parser';
    }

    public function name(): string
    {
        return 'HTTP Parser';
    }

    public function summary(): string
    {
        return 'Paste a raw HTTP request or response for local parsing, structured breakdowns, and cautious security review notes.';
    }

    public function descriptionMd(): ?string
    {
        return <<<'MD'
## Raw HTTP parser and triage

Paste traffic copied from **Burp**, **browser devtools**, **curl**, or logs. This tool parses it **locally in your browser** — nothing is sent to our servers.

### What you get

- **Requests:** method, path, query string, HTTP version, host, headers, cookies, auth headers, body, JSON or form fields when applicable
- **Responses:** status line and code, headers, `Set-Cookie` lines, content type, body preview, JSON fields when applicable
- **Security triage:** only what is visible in the pasted message — cookie flags, CORS/CSP hints, auth material, duplicate parameters, ID-like segments, version leakage, and similar — phrased as *worth checking*, not certainties

### What this is not

Not a scanner, fuzzer, or exploit tool — only a parser and checklist-style hints for manual review.

### SEO / discovery

Useful for: raw HTTP parser, HTTP request parser, HTTP response parser, parse raw request headers, inspect HTTP request online, security header check from raw response.
MD;
    }

    public function categories(): array
    {
        return ['links', 'productivity'];
    }

    public function tags(): array
    {
        return [
            'http',
            'security',
            'headers',
            'pentest',
            'burp',
            'triage',
            'api',
        ];
    }

    public function inputSchema(): array
    {
        return [];
    }

    public function run(array $input): array
    {
        return [];
    }

    public function mode(): string
    {
        return 'frontend';
    }

    public function isAsync(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function frontendView(): ?string
    {
        return 'http-parser::http-parser';
    }

    public function rateLimitPerMinute(): int
    {
        return 120;
    }

    public function cacheTtlSeconds(): int
    {
        return 0;
    }

    public function sortWeight(): int
    {
        return 48;
    }
}
