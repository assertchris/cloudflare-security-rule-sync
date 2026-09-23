# cloudflare-security-rule-sync

Your app has routes you want the world to hit and routes that should stay locked down. This package figures out the first group and tells Cloudflare to block everything else.

We collect your routes, Folio pages, public files, and any extra paths you configure, then build an allowlist expression and push it to Cloudflare's Rulesets API. Everything not on the list gets blocked at the edge before it even touches your server.

## Installation

```bash
composer require assertchris/cloudflare-security-rule-sync
```

Then publish the config:

```bash
php artisan vendor:publish --tag=cloudflare-security-rule-sync-config
```

## Getting a Cloudflare API Token

You'll need a token with permission to edit your zone's custom rules.

1. Go to [dash.cloudflare.com](https://dash.cloudflare.com) → **My Profile** → **API Tokens** → **Create Token**
2. Choose **Create Custom Token**
3. Under **Permissions**, add: **Zone** → **Custom Rules** → **Edit**
4. Under **Zone Resources**, set: **Include** → **Specific zone** → *(your zone)*
5. Create the token and copy it — you won't see it again

Here's something important: Cloudflare requires the custom ruleset to exist before the API can update it. Create at least one placeholder rule in the dashboard first. We'll find the rule matching your configured description and update it — or append a new one if it doesn't exist.

## Configuration

Add these to your `.env`:

```env
CF_API_TOKEN=your_token_here
CF_ZONE_ID=your_zone_id_here
```

Your zone ID is on the overview page for your domain in the Cloudflare dashboard.

The full config (after publishing) looks like this:

```php
return [
    'api_token' => env('CF_API_TOKEN'),
    'zone_id' => env('CF_ZONE_ID'),

    'rule' => [
        'description' => env('CF_RULE_DESCRIPTION', 'cloudflare-security-rule-sync'),
        'action' => env('CF_RULE_ACTION', 'block'),
        'ignorable_paths' => ['/_dusk/*', '/_boost/*'],
        'forced_allow_paths' => [],
        'hostnames' => [],
    ],
];
```

**`rule.description`** — we match this against the Cloudflare rule's description to find and update the existing rule rather than appending a new one on every sync. Keep it unique per app.

**`rule.action`** — what Cloudflare does when a request doesn't match. Defaults to `block`. Other options: `challenge`, `js_challenge`, `managed_challenge`, `log`, `bypass`.

**`rule.ignorable_paths`** — paths we exclude from the allowlist even if they show up in your routes. Wildcards work here. Dusk and Boost paths are already excluded by default.

**`rule.forced_allow_paths`** — paths we always include in the allowlist even if they don't show up in your routes. Wildcards work here too.

**`rule.hostnames`** — when set, we wrap the expression with an `http.host in {...}` check. Handy if your zone serves multiple apps.

## Usage

Let's preview the generated expression without pushing anything:

```bash
php artisan cloudflare:sync-security-rule
```

It'll print the rule description and the full expression — a good way to see what we'd push before it actually goes anywhere.

When you're happy, push it:

```bash
php artisan cloudflare:sync-security-rule --sync
```

The command collects paths from three places:

- **App routes** — your registered Laravel routes (via `route:list`)
- **Folio pages** — pages pulled via `folio:list`, if you've got `laravel/folio` installed
- **Public files** — files in your `public/` directory (e.g. `robots.txt`, `favicon.ico`)

Route parameters become wildcards — `/users/{id}/posts/{postId}` becomes `/users/*/posts/*`. We collapse duplicates, strip redundant entries, and if the expression hits Cloudflare's 4000-character limit, we condense it further by collapsing paths that are covered by their wildcard parents.

## Running on Deploy

The typical setup is a single line in your deployment pipeline:

```bash
php artisan cloudflare:sync-security-rule --sync
```

The sync is idempotent. If a rule with the matching description already exists, we update it. If not, we append a new one to your ruleset.

## Credits

Inspired by [nexxai/laravel-cfcache](https://github.com/nexxai/laravel-cfcache). This package does one thing — security rule sync — and uses the current Rulesets API instead of the deprecated Firewall Rules API.
