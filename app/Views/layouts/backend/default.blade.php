<!DOCTYPE html>
<html lang="ja">
@include('layouts.backend.structures.head')
<body class="{{ getBodyClass() }}">
@include('layouts.backend.structures.navigation')
<main class="container">
    @yield('content')
</main>
@include('layouts.backend.structures.footer')
@include('layouts.backend.elements.modal')
@stack('scripts')
</body>
</html>
