<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="A student-run organization hosting various events for SSIS students.">

<meta property="og:type" content="website">
<meta property="og:site_name" content="Togethernet">
<meta property="og:title" content="{{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}">
<meta property="og:description" content="A student-run organization hosting various events for SSIS students.">
<meta property="og:image" content="{{ config('app.url') }}/images/togethernet-feature.jpg">
<meta property="og:url" content="{{ config('app.url') }}">

<meta name="twitter:card" content="{{ config('app.url') }}/images/togethernet-feature.jp">
<meta name="twitter:title" content="{{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
">
<meta name="twitter:description" content="A student-run organization hosting various events for SSIS students.">
<meta name="twitter:image" content="{{ config('app.url') }}/images/togethernet-feature.jpg">

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon-light.ico" media="(prefers-color-scheme: light)" sizes="any">
<link rel="icon" href="/favicon-dark.ico" media="(prefers-color-scheme: dark)" sizes="any">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
<style>
    /* Prevents the main content from collapsing when editors try to calculate width */
    flux-main { min-width: 0; flex: 1 1 0%; display: flex; flex-direction: column; }
</style>
@stack('head_scripts')
