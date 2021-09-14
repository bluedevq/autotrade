@if(!isset($disableNavigation) || !$disableNavigation)
<header class="navbar navbar-expand-lg navbar-dark top-menu">
    <nav class="container-xxl flex-wrap flex-md-nowrap">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarNav">
            @php $currentRoute = Route::currentRouteName(); @endphp
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                {{--                <li class="nav-item">--}}
                {{--                    <a class="nav-link {{ $currentRoute == 'dashboard.index' ? 'active' : '' }}" href="{{ route('dashboard.index') }}"><span class="fas fa-home">&nbsp;</span>Trang chủ</a>--}}
                {{--                </li>--}}
                <li class="nav-item">
                    <a class="nav-link {{ $currentRoute == 'bot.index' ? 'active' : '' }}" href="{{ route('bot.index') }}"><span class="fas fa-robot">&nbsp;</span>AresBo Bot</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentRoute == 'bot.move.money' ? 'active' : '' }}" href="{{ route('bot.move.money') }}"><span class="fas fa-retweet">&nbsp;</span>Chuyển tiền</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link" data-bs-toggle="dropdown" href="javascript:void(0)" role="button" aria-expanded="false"><span class="fas fa-cog">&nbsp;</span>Cài đặt</a>
                    <ul class="dropdown-menu">
                        <li class="nav-item">
                            <a class="nav-link {{ $currentRoute == 'user.profile' ? 'active' : '' }}" href="{{ route('user.profile', backendGuard()->user()->id) }}"><span class="fas fa-user">&nbsp;</span>Thông tin tài khoản</a>
                        </li>
                        @if (backendGuard()->user()->role == \App\Helper\Common::getConfig('user_role.supper_admin'))
                            <li class="nav-item">
                                <a class="nav-link {{ $currentRoute == 'default.method.index' ? 'active' : '' }}" href="{{ route('default.method.index') }}"><span class="fas fa-list-ol">&nbsp;</span>Quản lý phương pháp</a>
                            </li>
                        @endif
                        @if (backendGuard()->user()->role != \App\Helper\Common::getConfig('user_role.normal'))
                            <li class="nav-item">
                                <a class="nav-link {{ $currentRoute == 'user.index' ? 'active' : '' }}" href="{{ route('user.index') }}"><span class="fas fa-users">&nbsp;</span>Quản lý người dùng</a>
                            </li>
                        @endif
                    </ul>
                </li>
            </ul>
            <ul class="navbar-nav user-info">
                <li class="nav-item">
                    <a class="nav-link" href="javascript:void(0)"><span>Xin chào,&nbsp;</span><span class="username">{{ backendGuard()->user()->name }}</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="javascript:void(0);" onclick="document.getElementById('form-logout').submit();"><span class="fas fa-sign-out-alt">&nbsp;</span>Đăng xuất</a>
                </li>
                <form id="form-logout" method="post" action="{{ route('backend.logout') }}" class="form-horizontal" enctype="multipart/form-data">
                    @csrf
                </form>
            </ul>
        </div>
    </nav>
</header>
@endif
