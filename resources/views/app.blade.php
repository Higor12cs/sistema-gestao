<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    <meta name="lang" content="{{ app()->getLocale() }}" />

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <style>
        #nprogress {
            z-index: 9999 !important;
        }

        #nprogress .bar {
            z-index: 9999 !important;
        }

        #nprogress .peg {
            z-index: 9999 !important;
        }
    </style>

    @routes
    @vite('resources/js/app.js')
    @inertiaHead
</head>

<body class="layout-fixed layout-navbar-fixed sidebar-mini">
    @inertia
</body>

</html>
