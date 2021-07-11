<!DOCTYPE html>
<html lang="ja">
@include('layout.structures.head')
<body class="{{ getBodyClass() }}">
<div id="Wrap">
    <div class="container">
        @include('layout.elements.messages')
        @yield('content')
        @include('layout.structures.footer')
        @include('layout.elements.modal')
    </div>
</div>
@stack('scripts')
</body>
</html>
