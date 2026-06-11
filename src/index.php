<?php
declare(strict_types=1);

$organizationName = 'Literacy Volunteers of Monongalia and Preston Counties';
$facebookUrl = 'https://www.facebook.com/LVMPC/';
$facebookPageId = getenv('FACEBOOK_PAGE_ID') ?: '276842242392667';
$facebookAccessToken = getenv('FACEBOOK_PAGE_ACCESS_TOKEN') ?: 'EAAWP7kqyYJoBRriA5pZB53PV8AkguHcTZASCAeAGBsvEbhwkxxE68i5vfkyzQXx58B2u64A87376Xuaeg3QFmuvOgQ6HfRSmVSKuW5OmABTwpLUMrLZBwqITBpnZBYyFQEIQyh6eQEztBZAVOUhc5znVDIFjZAxy0Msp4xrKFwG7hzlUoWXUAfVkmnRX8b5b1SE5KcfFgQk7J4aGWeleaWgjZBLMkKZAcVTZCzI4W0gZDZD';
$facebookApiVersion = getenv('FACEBOOK_GRAPH_API_VERSION') ?: 'v23.0';
$facebookCacheTtlSeconds = (int) (getenv('FACEBOOK_CACHE_TTL_SECONDS') ?: 3600);
$paypalDonationUrl = 'https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=director%40lvmpc.org&item_name=Adult%20Literacy%20Fund&currency_code=USD';

$contact = [
    'email' => 'director@lvmpc.org',
    'phone' => '304-296-3400',
    'phone_href' => '+13042963400',
    'text' => '724-491-2007',
    'text_href' => '+17244912007',
    'address' => '235 High St, Ste 317, Morgantown, WV 26505',
];

$impactStats = [
    [
        'value' => '50',
        'label' => 'Tutor and learner pairs meeting weekly working on SMART goals',
    ],
    [
        'value' => '3',
        'label' => 'Zoom classes taught a week by a certified English teacher',
    ],
    [
        'value' => '25',
        'label' => 'Little, free libraries across Monongalia and Preston Counties.',
    ],
    [
        'value' => '300+',
        'label' => 'Children ages 0-12 receiving Family Literacy Club bookbags.',
    ],
];

$fallbackFacebookUpdates = [
    [
        'date' => '2023-01-25',
        'title' => 'New books for the bookmobile',
        'summary' => 'LVMPC thanked the Teletech Foundation for funding new bookmobile books selected from teacher requests.',
        'url' => 'https://www.facebook.com/LVMPC/posts/5878627902214045',
    ],
    [
        'date' => '2023-01-17',
        'title' => 'Community speaking visit',
        'summary' => 'LVMPC thanked the Morgantown Newcomers Club for inviting the organization to speak about local literacy work.',
        'url' => 'https://www.facebook.com/LVMPC/posts/5855213827888786',
    ],
    [
        'date' => '2022-12-04',
        'title' => 'Library gathering reminder',
        'summary' => 'Followers were invited to join LVMPC at the Morgantown Library on Saturday, December 10 at 11:00 AM.',
        'url' => 'https://www.facebook.com/LVMPC/posts/5717988628277974',
    ],
    [
        'date' => '2022-11-16',
        'title' => 'Adult spelling resources',
        'summary' => 'LVMPC announced new spelling books for adults from New Readers Press to support learner instruction.',
        'url' => 'https://www.facebook.com/LVMPC/posts/5666720580071446',
    ],
];

$sponsorMapAreas = [
    [
        'coords' => '582,38,790,178',
        'alt' => 'Your Community Foundation of North Central West Virginia',
        'href' => 'http://ycfwv.org/',
    ],
    [
        'coords' => '82,199,502,290',
        'alt' => 'ProLiteracy',
        'href' => 'http://www.proliteracy.org/',
    ],
    [
        'coords' => '532,190,824,293',
        'alt' => 'Tucker Community Foundation',
        'href' => 'http://www.tuckerfoundation.net/',
    ],
    [
        'coords' => '85,314,515,389',
        'alt' => 'Dollar General Literacy Foundation',
        'href' => 'http://www.dgliteracy.org/',
    ],
    [
        'coords' => '568,314,812,491',
        'alt' => 'The Nora Roberts Foundation',
        'href' => 'http://www.norarobertsfoundation.org/',
    ],
    [
        'coords' => '50,630,455,697',
        'alt' => 'Truist Foundation',
        'href' => 'https://www.truist.com/purpose/truist-foundation',
    ],
    [
        'coords' => '38,722,512,818',
        'alt' => 'United Way of Monongalia and Preston Counties',
        'href' => 'https://www.unitedwaympc.org/',
    ],
    [
        'coords' => '586,674,795,803',
        'alt' => 'Milan Puskar Foundation',
        'href' => 'http://puskarfoundation.org/',
    ],
];

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function format_date(string $date): string
{
    $timestamp = strtotime($date);
    return $timestamp ? date('M j, Y', $timestamp) : $date;
}

function fetch_url(string $url): ?string
{
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_USERAGENT => 'LVMPC website feed reader',
        ]);
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return is_string($body) && $status >= 200 && $status < 300 ? $body : null;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: LVMPC website feed reader\r\n",
            'timeout' => 8,
        ],
    ]);
    $body = @file_get_contents($url, false, $context);

    return is_string($body) ? $body : null;
}

function text_excerpt(string $text, int $limit): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
    if (strlen($text) <= $limit) {
        return $text;
    }

    return rtrim(substr($text, 0, $limit - 3), " \t\n\r\0\x0B.,;:") . '...';
}

function facebook_title(array $post): string
{
    $message = trim((string) ($post['message'] ?? ''));
    $attachments = $post['attachments']['data'] ?? [];

    if ($message !== '') {
        $sentence = preg_split('/(?<=[.!?])\s+/', $message, 2)[0] ?? $message;
        return text_excerpt($sentence, 58);
    }

    foreach ($attachments as $attachment) {
        $title = trim((string) ($attachment['title'] ?? ''));
        if ($title !== '') {
            return text_excerpt($title, 58);
        }
    }

    return 'LVMPC Facebook update';
}

function normalize_facebook_posts(array $posts): array
{
    $updates = [];

    foreach ($posts as $post) {
        $message = trim((string) ($post['message'] ?? ''));
        $attachments = $post['attachments']['data'] ?? [];
        $attachmentSummary = '';

        foreach ($attachments as $attachment) {
            $attachmentSummary = trim((string) ($attachment['description'] ?? $attachment['title'] ?? ''));
            if ($attachmentSummary !== '') {
                break;
            }
        }

        $summary = $message !== '' ? $message : $attachmentSummary;
        if ($summary === '') {
            $summary = 'See the latest LVMPC update on Facebook.';
        }

        $created = (string) ($post['created_time'] ?? '');
        $updates[] = [
            'date' => substr($created, 0, 10) ?: date('Y-m-d'),
            'title' => facebook_title($post),
            'summary' => text_excerpt($summary, 185),
            'url' => (string) ($post['permalink_url'] ?? ('https://www.facebook.com/' . ($post['id'] ?? 'LVMPC'))),
        ];
    }

    return $updates;
}

function read_cached_facebook_updates(string $cacheFile): array
{
    if (!is_file($cacheFile)) {
        return [];
    }

    $cached = json_decode((string) file_get_contents($cacheFile), true);

    return is_array($cached) ? $cached : [];
}

function facebook_api_version_path(string $apiVersion): string
{
    $apiVersion = trim($apiVersion);
    if ($apiVersion === '') {
        return 'v23.0';
    }

    if ($apiVersion[0] !== 'v') {
        $apiVersion = 'v' . $apiVersion;
    }

    return preg_match('/^v\d+\.\d+$/', $apiVersion) === 1 ? $apiVersion : 'v23.0';
}

function write_cached_facebook_updates(string $cacheFile, array $updates): void
{
    $cacheDir = dirname($cacheFile);
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0775, true);
    }

    if (is_dir($cacheDir) && is_writable($cacheDir)) {
        @file_put_contents($cacheFile, json_encode($updates, JSON_PRETTY_PRINT), LOCK_EX);
    }
}

function load_facebook_updates(
    string $pageId,
    string $accessToken,
    string $apiVersion,
    int $cacheTtlSeconds,
    array $fallbackUpdates
): array {
    $cacheFile = __DIR__ . '/cache/facebook-posts.json';
    $cachedUpdates = read_cached_facebook_updates($cacheFile);
    $cacheIsFresh = is_file($cacheFile) && filemtime($cacheFile) > (time() - max(300, $cacheTtlSeconds));

    if ($cacheIsFresh && $cachedUpdates !== []) {
        return array_slice($cachedUpdates, 0, 4);
    }

    if (trim($accessToken) !== '') {
        $url = 'https://graph.facebook.com/' . rawurlencode(facebook_api_version_path($apiVersion)) . '/' . rawurlencode($pageId) . '/posts?' . http_build_query([
            'fields' => 'id,created_time,message,permalink_url,attachments{title,description,url,unshimmed_url}',
            'limit' => 4,
            'access_token' => $accessToken,
        ]);
        $body = fetch_url($url);
        $payload = $body !== null ? json_decode($body, true) : null;
        $updates = is_array($payload['data'] ?? null) ? normalize_facebook_posts($payload['data']) : [];

        if ($updates !== []) {
            write_cached_facebook_updates($cacheFile, $updates);
            return array_slice($updates, 0, 4);
        }
    }

    return array_slice($cachedUpdates !== [] ? $cachedUpdates : $fallbackUpdates, 0, 4);
}

$facebookUpdates = load_facebook_updates(
    $facebookPageId,
    $facebookAccessToken,
    $facebookApiVersion,
    $facebookCacheTtlSeconds,
    $fallbackFacebookUpdates
);
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-WQC14TX4FT"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-WQC14TX4FT');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($organizationName) ?></title>
    <meta name="description" content="Free, confidential adult literacy instruction for Monongalia and Preston Counties.">
    <link rel="icon" href="images/lvmpc_logo_32x32.ico">
    <style>
        :root {
            --green: #667a62;
            --green-dark: #314a38;
            --coral: #e05d41;
            --gold: #f4c431;
            --ink: #20302d;
            --muted: #5c6864;
            --paper: #fbfbf6;
            --white: #ffffff;
            --line: #dfe7dd;
            --shadow: 0 20px 45px rgba(32, 48, 45, 0.14);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            color: var(--ink);
            background: var(--paper);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 17px;
            line-height: 1.6;
        }

        a {
            color: inherit;
        }

        img {
            display: block;
            max-width: 100%;
            height: auto;
        }

        .skip-link {
            position: absolute;
            top: -80px;
            left: 16px;
            z-index: 100;
            padding: 10px 14px;
            color: var(--white);
            background: var(--ink);
            border-radius: 6px;
            transition: top 0.2s ease;
        }

        .skip-link:focus {
            top: 16px;
        }

        .container {
            width: min(1120px, calc(100% - 40px));
            margin: 0 auto;
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255, 255, 255, 0.94);
            border-bottom: 1px solid var(--line);
            backdrop-filter: blur(14px);
        }

        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 78px;
            gap: 20px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            min-width: 220px;
            gap: 12px;
            text-decoration: none;
            font-weight: 800;
            line-height: 1.15;
        }

        .brand img {
            width: 54px;
            height: 50px;
            object-fit: contain;
        }

        .brand span {
            max-width: 320px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 8px 18px;
            font-size: 0.92rem;
            font-weight: 700;
        }

        .nav-links a {
            padding: 8px 0;
            text-decoration: none;
        }

        .nav-links a:hover,
        .nav-links a:focus {
            color: var(--coral);
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 10px 18px;
            color: var(--white);
            background: var(--coral);
            border: 2px solid var(--coral);
            border-radius: 8px;
            font-weight: 800;
            text-decoration: none;
            line-height: 1.15;
            transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease;
        }

        .button:hover,
        .button:focus {
            background: var(--green-dark);
            border-color: var(--green-dark);
            transform: translateY(-1px);
        }

        .button.secondary {
            color: var(--ink);
            background: var(--gold);
            border-color: var(--gold);
        }

        .button.secondary:hover,
        .button.secondary:focus {
            color: var(--white);
            background: var(--green-dark);
            border-color: var(--green-dark);
        }

        .hero {
            color: var(--white);
            background:
                linear-gradient(90deg, rgba(32, 48, 45, 0.92), rgba(32, 48, 45, 0.64) 56%, rgba(32, 48, 45, 0.34)),
                url("images/slider1_books-5.png") center / cover no-repeat;
        }

        .hero-inner {
            display: grid;
            grid-template-columns: minmax(0, 1.12fr) minmax(260px, 0.62fr);
            gap: 42px;
            align-items: center;
            padding: 88px 0 72px;
        }

        .eyebrow {
            margin: 0 0 14px;
            color: var(--gold);
            font-size: 0.83rem;
            font-weight: 900;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        h1,
        h2,
        h3 {
            margin: 0;
            line-height: 1.08;
        }

        h1 {
            max-width: 820px;
            font-size: 4.6rem;
        }

        h2 {
            font-size: 2.75rem;
        }

        h3 {
            font-size: 1.25rem;
        }

        .lead {
            max-width: 730px;
            margin: 22px 0 0;
            color: rgba(255, 255, 255, 0.92);
            font-size: 1.26rem;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 32px;
        }

        .hero-logo {
            justify-self: center;
            width: min(270px, 72vw);
            padding: 22px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.58);
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .quick-contact {
            background: var(--green-dark);
            color: var(--white);
        }

        .quick-contact .container {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1px;
            background: rgba(255, 255, 255, 0.18);
        }

        .quick-contact a,
        .quick-contact span {
            display: block;
            min-height: 78px;
            padding: 17px 18px;
            background: var(--green-dark);
            text-decoration: none;
        }

        .quick-contact strong {
            display: block;
            color: var(--gold);
            font-size: 0.78rem;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .section {
            padding: 76px 0;
        }

        .section.white {
            background: var(--white);
        }

        .section-header {
            display: grid;
            grid-template-columns: minmax(0, 0.8fr) minmax(280px, 0.7fr);
            gap: 38px;
            align-items: end;
            margin-bottom: 34px;
        }

        .section-header p {
            margin: 0;
            color: var(--muted);
        }

        .about-grid {
            display: grid;
            grid-template-columns: minmax(0, 0.96fr) minmax(300px, 0.74fr);
            gap: 40px;
            align-items: start;
        }

        .about-copy p {
            margin: 0 0 18px;
        }

        .service-list {
            display: grid;
            gap: 12px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .service-list li {
            padding: 16px 18px;
            background: var(--white);
            border: 1px solid var(--line);
            border-left: 6px solid var(--coral);
            border-radius: 8px;
            font-weight: 700;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .stat {
            min-height: 192px;
            padding: 24px;
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 14px 30px rgba(32, 48, 45, 0.08);
        }

        .stat strong {
            display: block;
            color: var(--green-dark);
            font-size: 3.4rem;
            line-height: 1;
        }

        .stat span {
            display: block;
            margin-top: 14px;
            color: var(--muted);
        }

        .donation-panel {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 30px;
            align-items: center;
            padding: 34px;
            color: var(--white);
            background: var(--green-dark);
            border-radius: 8px;
        }

        .donation-panel p {
            max-width: 720px;
            margin: 14px 0 0;
            color: rgba(255, 255, 255, 0.84);
        }

        .sponsor-frame {
            padding: 18px;
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .sponsor-frame img {
            width: 100%;
            border-radius: 4px;
        }

        .updates-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .update {
            display: flex;
            min-height: 260px;
            flex-direction: column;
            padding: 22px;
            background: var(--white);
            border: 1px solid var(--line);
            border-top: 6px solid var(--gold);
            border-radius: 8px;
            box-shadow: 0 14px 30px rgba(32, 48, 45, 0.08);
        }

        .update time {
            color: var(--coral);
            font-size: 0.86rem;
            font-weight: 900;
        }

        .update h3 {
            margin-top: 10px;
        }

        .update p {
            margin: 14px 0 22px;
            color: var(--muted);
        }

        .text-link {
            margin-top: auto;
            color: var(--green-dark);
            font-weight: 900;
            text-decoration: none;
        }

        .text-link:hover,
        .text-link:focus {
            color: var(--coral);
            text-decoration: underline;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: minmax(0, 0.76fr) minmax(320px, 0.62fr);
            gap: 34px;
            align-items: start;
        }

        .contact-methods {
            display: grid;
            gap: 14px;
            margin-top: 24px;
        }

        .contact-methods a,
        .contact-methods address {
            display: block;
            padding: 18px 20px;
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--ink);
            text-decoration: none;
            font-style: normal;
            font-weight: 700;
        }

        .contact-methods a:hover,
        .contact-methods a:focus {
            border-color: var(--coral);
        }

        .contact-note {
            padding: 26px;
            color: var(--white);
            background: var(--coral);
            border-radius: 8px;
        }

        .contact-note p {
            margin: 12px 0 0;
        }

        .footer {
            padding: 32px 0;
            color: rgba(255, 255, 255, 0.78);
            background: var(--ink);
            font-size: 0.95rem;
        }

        .footer .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .footer a {
            color: var(--white);
            font-weight: 800;
            text-decoration: none;
        }

        .footer a:hover,
        .footer a:focus {
            color: var(--gold);
        }

        @media (max-width: 980px) {
            h1 {
                font-size: 3.5rem;
            }

            h2 {
                font-size: 2.35rem;
            }

            .lead {
                font-size: 1.18rem;
            }

            .nav {
                align-items: flex-start;
                flex-direction: column;
                padding: 14px 0;
            }

            .nav-links {
                justify-content: flex-start;
            }

            .hero-inner,
            .about-grid,
            .section-header,
            .contact-grid,
            .donation-panel {
                grid-template-columns: 1fr;
            }

            .hero-logo {
                justify-self: start;
                width: 220px;
            }

            .quick-contact .container,
            .stats-grid,
            .updates-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            h1 {
                font-size: 2.45rem;
            }

            h2 {
                font-size: 2rem;
            }

            .lead {
                font-size: 1.08rem;
            }

            .stat strong {
                font-size: 2.85rem;
            }

            .container {
                width: min(100% - 28px, 1120px);
            }

            .brand {
                min-width: 0;
                font-size: 0.95rem;
            }

            .brand img {
                width: 46px;
                height: 42px;
            }

            .nav-links {
                gap: 6px 13px;
                font-size: 0.88rem;
            }

            .hero-inner {
                padding: 56px 0 48px;
            }

            .quick-contact .container,
            .stats-grid,
            .updates-grid {
                grid-template-columns: 1fr;
            }

            .section {
                padding: 54px 0;
            }

            .donation-panel {
                padding: 24px;
            }
        }
    </style>
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>

    <header class="site-header">
        <div class="container nav" aria-label="Main navigation">
            <a class="brand" href="#top" aria-label="<?= e($organizationName) ?> home">
                <img src="images/lvmpc_logo.png" alt="" width="199" height="183">
                <span><?= e($organizationName) ?></span>
            </a>
            <nav class="nav-links">
                <a href="#about">About</a>
                <a href="#impact">Impact</a>
                <a href="#sponsors">Sponsors</a>
                <a href="#updates">Events</a>
                <a href="#contact">Contact</a>
                <a href="<?= e($facebookUrl) ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
                <a class="button" href="<?= e($paypalDonationUrl) ?>" target="_blank" rel="noopener noreferrer">Donate</a>
            </nav>
        </div>
    </header>

    <main id="main">
        <section class="hero" id="top">
            <div class="container hero-inner">
                <div>
                    <p class="eyebrow">Free adult literacy services</p>
                    <h1><?= e($organizationName) ?></h1>
                    <p class="lead">
                        Confidential, research-driven instruction for adults who want to strengthen reading, writing,
                        speaking, and listening skills in Monongalia and Preston Counties.
                    </p>
                    <div class="hero-actions">
                        <a class="button secondary" href="#contact">Get in touch</a>
                        <a class="button" href="<?= e($paypalDonationUrl) ?>" target="_blank" rel="noopener noreferrer">Donate with PayPal</a>
                    </div>
                </div>
                <div class="hero-logo" aria-hidden="true">
                    <img src="images/lvmpc_logo.png" alt="" width="199" height="183">
                </div>
            </div>
        </section>

        <section class="quick-contact" aria-label="Quick contact">
            <div class="container">
                <span><strong>Languages</strong> Hablamos Espa&ntilde;ol</span>
                <a href="tel:<?= e($contact['phone_href']) ?>"><strong>Call</strong> <?= e($contact['phone']) ?></a>
                <a href="sms:<?= e($contact['text_href']) ?>"><strong>Text</strong> <?= e($contact['text']) ?></a>
                <a href="mailto:<?= e($contact['email']) ?>"><strong>Email</strong> <?= e($contact['email']) ?></a>
            </div>
        </section>

        <section class="section" id="about">
            <div class="container about-grid">
                <div class="about-copy">
                    <p class="eyebrow">What we do</p>
                    <h2>Literacy support for adults, families, and local communities.</h2>
                    <p>
                        Services are available to native English speakers and English language learners. LVMPC works
                        with adult learners to build practical reading, writing, speaking, and listening skills.
                    </p>
                    <p>
                        Instruction is free, confidential, and grounded in proven literacy practices. Learners can
                        ask about tutoring, learning materials, English language support, and available class options.
                    </p>
                </div>
                <ul class="service-list" aria-label="Services">
                    <li>Adult reading and writing instruction</li>
                    <li>English language learner support</li>
                    <li>Tutoring, classes, and learning materials</li>
                    <li>Community literacy partnerships</li>
                </ul>
            </div>
        </section>

        <section class="section white" id="impact">
            <div class="container">
                <div class="section-header">
                    <div>
                        <p class="eyebrow">Impact</p>
                        <h2>Small goals add up to life-changing progress.</h2>
                    </div>
                    <p>
                        Learners highlight milestones such as earning licenses, voting, participating in family school activities, and achieving ProLiteracy-defined goals.
                    </p>
                </div>
                <div class="stats-grid">
                    <?php foreach ($impactStats as $stat): ?>
                        <article class="stat">
                            <strong><?= e($stat['value']) ?></strong>
                            <span><?= e($stat['label']) ?></span>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container donation-panel">
                <div>
                    <p class="eyebrow">Support the work</p>
                    <h2>Help keep adult literacy services free.</h2>
                    <p>
                        Donations support learner materials, tutoring resources, classes, and community programs
                        across Monongalia and Preston Counties.
                    </p>
                </div>
                <a class="button secondary" href="<?= e($paypalDonationUrl) ?>" target="_blank" rel="noopener noreferrer">Donate with PayPal</a>
            </div>
        </section>

        <section class="section white" id="sponsors">
            <div class="container">
                <div class="section-header">
                    <div>
                        <p class="eyebrow">Sponsors</p>
                        <h2>Community partners make the work possible.</h2>
                    </div>
                    <p>
                        Thank you to the businesses, foundations, and local organizations that invest in literacy.
                    </p>
                </div>
                <div class="sponsor-frame">
                    <img src="images/lvmpc_sponsors_2025v2-1.webp" alt="LVMPC sponsor logos" usemap="#sponsor-map">
                    <map name="sponsor-map" id="sponsor-map">
                        <?php foreach ($sponsorMapAreas as $area): ?>
                            <area
                                shape="rect"
                                coords="<?= e($area['coords']) ?>"
                                data-original-coords="<?= e($area['coords']) ?>"
                                alt="<?= e($area['alt']) ?>"
                                href="<?= e($area['href']) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                        <?php endforeach; ?>
                    </map>
                </div>
            </div>
        </section>

        <section class="section" id="updates">
            <div class="container">
                <div class="section-header">
                    <div>
                        <p class="eyebrow">Facebook highlights</p>
                        <h2>Recent updates from the Facebook feed.</h2>
                    </div>
                    <p>
                        Follow LVMPC on Facebook for current events, class announcements, volunteer needs, and
                        community updates.
                    </p>
                </div>
                <div class="updates-grid">
                    <?php foreach ($facebookUpdates as $update): ?>
                        <article class="update">
                            <time datetime="<?= e($update['date']) ?>"><?= e(format_date($update['date'])) ?></time>
                            <h3><?= e($update['title']) ?></h3>
                            <p><?= e($update['summary']) ?></p>
                            <a class="text-link" href="<?= e($update['url']) ?>" target="_blank" rel="noopener noreferrer">View on Facebook</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section white" id="contact">
            <div class="container contact-grid">
                <div>
                    <p class="eyebrow">Contact</p>
                    <h2>Ready to learn, volunteer, partner, or donate?</h2>
                    <p>
                        Contact LVMPC by email, text, or phone. Include the best way to reach you and whether you are
                        interested in learning, volunteering, sponsoring, or another partnership.
                    </p>
                    <div class="contact-methods">
                        <a href="mailto:<?= e($contact['email']) ?>">Email: <?= e($contact['email']) ?></a>
                        <a href="tel:<?= e($contact['phone_href']) ?>">Call: <?= e($contact['phone']) ?></a>
                        <a href="sms:<?= e($contact['text_href']) ?>">Text: <?= e($contact['text']) ?></a>
                        <address><?= e($contact['address']) ?></address>
                    </div>
                </div>
                <aside class="contact-note">
                    <h3>Hablamos Espa&ntilde;ol</h3>
                    <p>
                        Services are free and confidential. Learners, families, volunteers, and community partners are
                        welcome to reach out.
                    </p>
                    <p>
                        <a class="button secondary" href="<?= e($facebookUrl) ?>" target="_blank" rel="noopener noreferrer">Visit Facebook Page</a>
                    </p>
                </aside>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <span>&copy; <?= date('Y') ?> <?= e($organizationName) ?>. All rights reserved.</span>
            <a href="<?= e($paypalDonationUrl) ?>" target="_blank" rel="noopener noreferrer">Donate</a>
        </div>
    </footer>
    <script>
        (function () {
            var image = document.querySelector('img[usemap="#sponsor-map"]');
            var areas = document.querySelectorAll('#sponsor-map area');

            if (!image || !areas.length) {
                return;
            }

            function resizeSponsorMap() {
                if (!image.naturalWidth || !image.naturalHeight) {
                    return;
                }

                var widthScale = image.clientWidth / image.naturalWidth;
                var heightScale = image.clientHeight / image.naturalHeight;

                areas.forEach(function (area) {
                    var originalCoords = area.dataset.originalCoords.split(',').map(Number);
                    var scaledCoords = originalCoords.map(function (coord, index) {
                        return Math.round(coord * (index % 2 === 0 ? widthScale : heightScale));
                    });

                    area.coords = scaledCoords.join(',');
                });
            }

            if (image.complete) {
                resizeSponsorMap();
            } else {
                image.addEventListener('load', resizeSponsorMap);
            }

            window.addEventListener('resize', resizeSponsorMap);
        })();
    </script>
</body>
</html>
