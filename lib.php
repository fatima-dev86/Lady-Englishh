<?php
// Shared helpers for the Lady English website.
const DATA_DIR = __DIR__ . '/data';
const FALLBACK_RATE = 47.7;   // used only if the live exchange rate cannot be loaded

function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Courses: [name, original price in USD per level]. Change prices here.
function courses(): array {
    return [['English', 140], ['French', 180], ['Spanish', 180], ['Turkish', 180], ['Italian', 180]];
}

function ensure_data_dir(): void {
    if (!is_dir(DATA_DIR)) { @mkdir(DATA_DIR, 0755, true); }
}

// Offer settings (changed by the owner in admin.php).
function settings(): array {
    $s = ['offerActive' => true, 'offerPercent' => 30];
    $f = DATA_DIR . '/settings.json';
    if (is_file($f)) {
        $j = json_decode((string)@file_get_contents($f), true);
        if (is_array($j)) {
            $s['offerActive'] = !empty($j['offerActive']);
            $p = (int)($j['offerPercent'] ?? 30);
            if ($p >= 1 && $p <= 90) { $s['offerPercent'] = $p; }
        }
    }
    return $s;
}

function save_settings(bool $on, int $pct): bool {
    ensure_data_dir();
    $json = json_encode(['offerActive' => $on, 'offerPercent' => $pct]);
    return @file_put_contents(DATA_DIR . '/settings.json', $json, LOCK_EX) !== false;
}

function price_now(int $base, array $s): int {
    return $s['offerActive'] ? (int)round($base * (100 - $s['offerPercent']) / 100) : $base;
}

function http_get(string $url): ?string {
    if (function_exists('curl_init')) {
        $c = curl_init($url);
        curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_FOLLOWLOCATION => true]);
        $r = curl_exec($c);
        curl_close($c);
        return is_string($r) ? $r : null;
    }
    $ctx = stream_context_create(['http' => ['timeout' => 4]]);
    $r = @file_get_contents($url, false, $ctx);
    return is_string($r) ? $r : null;
}

// Returns [USD to TRY rate, true if the rate is real (live or cached), false if it is the fallback].
function usd_try(): array {
    $f = DATA_DIR . '/rate.json';
    $cached = null;
    if (is_file($f)) {
        $j = json_decode((string)@file_get_contents($f), true);
        if (is_array($j) && isset($j['rate'], $j['time']) && is_numeric($j['rate'])) {
            $cached = (float)$j['rate'];
            if (time() - (int)$j['time'] < 6 * 3600) { return [$cached, true]; }
        }
    }
    $raw = http_get('https://api.frankfurter.app/latest?from=USD&to=TRY');
    $d = $raw ? json_decode($raw, true) : null;
    $r = is_array($d) ? ($d['rates']['TRY'] ?? null) : null;
    if (is_numeric($r) && $r > 1 && $r < 1000) {
        ensure_data_dir();
        @file_put_contents($f, json_encode(['rate' => (float)$r, 'time' => time()]), LOCK_EX);
        return [(float)$r, true];
    }
    if ($cached !== null) { return [$cached, true]; }
    return [FALLBACK_RATE, false];
}
