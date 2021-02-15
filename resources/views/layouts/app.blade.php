<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}"
          rel="stylesheet">
</head>
<body>
<div>
    <x-navbar/>

    <main class="container py-4">
        <div class="row">
            @auth
            <x-sidebar/>
            @endauth

            <article class="@auth col-md-10 @else col-md-12 @endauth">
                <x-auth-info/>

                @yield('content')
            </article>
        </div>
    </main>
</div>
</body>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
@stack("scripts")
</html>
