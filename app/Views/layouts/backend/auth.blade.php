<!DOCTYPE html>
<html lang="ja">
@include('layouts.backend.structures.head')
<body class="{{getBodyClass()}}">
<div id="Wrap">
    <div class="container">
        @yield('content')
    </div>
</div>
@include('layouts.backend.structures.footer_js')
@stack('scripts')
</body>
</html>
