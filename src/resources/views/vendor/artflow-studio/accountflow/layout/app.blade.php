<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Artflow ERP'))</title>

    {{-- Bootstrap CSS (CDN) --}}
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

    {{-- Optional: FontAwesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- Page specific styles --}}
    @stack('styles')

    <style>
        /* Small default helpers for the accounts layout */
        .app-navbar-brand { font-weight: 600; }
        main.content { flex: 1 0 auto; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    {{-- Top navigation --}}
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand app-navbar-brand" href="{{ url('/') }}">
                <i class="fa fa-chart-pie me-1"></i>
                @yield('brand', config('app.name', 'Artflow ERP'))
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#accountsNavbar" aria-controls="accountsNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="accountsNavbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    {{-- Example nav items. Replace or @stack a nav if you need dynamic items. --}}
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/accounts') }}">Accounts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/accounts/transactions') }}">Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/accounts/reports') }}">Reports</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- Main content area --}}
    <main class="content py-4 pt-0">
        {{-- Session flash / status messages --}}
        @if(session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-auto bg-light border-top">
        <div class="container py-3 small text-muted d-flex justify-content-between">
            <div>&copy; {{ date('Y') }} {{ config('app.name', 'Artflow ERP') }}</div>
            <div>
                <a href="#" class="text-decoration-none">Help</a>
                <span class="mx-2">&middot;</span>
                <a href="#" class="text-decoration-none">Contact</a>
            </div>
        </div>
    </footer>

    {{-- Bootstrap Bundle with Popper (CDN) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-..." crossorigin="anonymous"></script>

    {{-- Page specific scripts --}}
    @stack('scripts')

</body>
</html>