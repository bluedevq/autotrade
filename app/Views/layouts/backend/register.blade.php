<!DOCTYPE html>
<html lang="ja">
@include('layouts.backend.structures.head')
<body class="{{ getBodyClass() }}">
<main class="container custom-label">
    @yield('content')
</main>
@include('layouts.backend.structures.footer')
@include('layouts.backend.elements.modal')
@stack('scripts')
</body>
</html>
