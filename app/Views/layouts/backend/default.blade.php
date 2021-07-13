<!DOCTYPE html>
<html lang="ja">
@include('layouts.backend.structures.head')
<body class="{{ getBodyClass() }}">
<div class="container">
    @include('layouts.backend.elements.navigation')
    @yield('content')
    @include('layouts.backend.structures.footer')
    @include('layouts.backend.elements.modal')
</div>
@stack('scripts')
</body>
</html>
