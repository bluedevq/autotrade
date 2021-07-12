<!DOCTYPE html>
<html lang="ja">
@include('layouts.backend.structures.head')
<body class="{{ getBodyClass() }}">
<div id="Wrap">
    <div class="container">
        @include('layouts.backend.elements.messages')
        @yield('content')
        @include('layouts.backend.structures.footer')
        @include('layouts.backend.elements.modal')
    </div>
</div>
@stack('scripts')
</body>
</html>
