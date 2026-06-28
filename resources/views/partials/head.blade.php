<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="Add a short, catchy description of your website here (around 150-160 characters) for Google search results.">

<meta property="og:type" content="website">
<meta property="og:title" content="{{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}">
<meta property="og:description" content="{{ __('Togethernet is a :year-year-old organization founded and run by committed students at Stockholm Science & Innovation School to create fun events for all students.', ['year' => now()->subYears(2013)->format('y')]) }}">
<meta property="og:image" content="{{ config('app.url') }}/images/togethernet-feature.jpg">
<meta property="og:url" content="{{ config('app.url') }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Hem - Togethernet">
<meta name="twitter:description" content="Add a short, catchy description of your website here for X/Twitter.">
<meta name="twitter:image" content="https://yourwebsite.com/path-to-your-image.jpg">

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
