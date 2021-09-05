<nav class="navbar navbar-expand-lg navbar-dark top-menu">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button"><span class="navbar-toggler-icon"></span></button>
        <div class="navbar-collapse hide not-active">
            @php $currentRoute = Route::currentRouteName(); @endphp
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ $currentRoute == 'dashboard.index' ? 'active' : '' }}" href="{{ route('dashboard.index') }}"><span class="fas fa-home">&nbsp;</span>Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentRoute == 'bot.index' ? 'active' : '' }}" href="{{ route('bot.index') }}"><span class="fas fa-robot">&nbsp;</span>AresBo Bot</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentRoute == 'bot.move.money' ? 'active' : '' }}" href="{{ route('bot.move.money') }}"><span class="fas fa-retweet">&nbsp;</span>Chuyển tiền</a>
                </li>
                @if (backendGuard()->user()->role != \App\Helper\Common::getConfig('user_role.normal'))
                <li class="nav-item">
                    <a class="nav-link {{ $currentRoute == 'default.method.index' ? 'active' : '' }}" href="{{ route('default.method.index') }}"><span class="fas fa-list-ol">&nbsp;</span>Quản lý phương pháp mặc định</a>
                </li>
                    <li class="nav-item">
                    <a class="nav-link {{ $currentRoute == 'user.index' ? 'active' : '' }}" href="{{ route('user.index') }}"><span class="fas fa-users">&nbsp;</span>Quản lý người dùng</a>
                </li>
                @endif
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="javascript:void(0)"><span>Xin chào&nbsp;</span><span class="text-info">{{ backendGuard()->user()->name }}</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="javascript:void(0);" onclick="document.getElementById('form-logout').submit();"><span class="fas fa-sign-out-alt">&nbsp;</span>Đăng xuất</a>
                </li>
                <form id="form-logout" method="post" action="{{ route('backend.logout') }}" class="form-horizontal" enctype="multipart/form-data">
                    @csrf
                </form>
            </ul>
        </div>
    </div>
</nav>
