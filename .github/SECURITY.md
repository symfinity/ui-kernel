# Security Policy

## Supported Versions

| Version | Supported |
|---------|-----------|
| 0.1.x   | Yes       |

## Reporting a Vulnerability

If you discover a security vulnerability, **do not** open a public issue. Email **dev@symfinity.net** with:

- Type of vulnerability
- Full paths of source file(s) related to the issue
- The location of the affected code (tag, branch, commit, or URL)
- Step-by-step reproduction instructions
- Proof-of-concept or exploit code (if possible)
- Impact and plausible attack scenario

We aim to acknowledge within 48 hours and provide a detailed response within 7 days.

## Security best practices

UI Kernel generates theme CSS and exposes theme preference endpoints when enabled:

1. Keep Symfony and dependencies updated
2. Treat `user_tokens` overrides as trusted configuration — do not pass untrusted input into `--ui-*` keys
3. When using theme preference cookies or PATCH endpoints, apply the same CSRF and session policies as the rest of your app

## Security contact

**dev@symfinity.net**
