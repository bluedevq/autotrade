<!DOCTYPE html>
<html lang="ja">
@include('layout.backend.structures.head')
<body class="{{ getBodyClass() }}">
<div id="Wrap">
    <div class="container">
        @include('layout.backend.elements.messages')
        @yield('content')
        @include('layout.backend.structures.footer')
        @include('layout.backend.elements.modal')
    </div>
</div>
@stack('scripts')
</body>
</html>
