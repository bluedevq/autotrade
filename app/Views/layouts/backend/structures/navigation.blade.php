<div class="row top-menu">
    <div class="col-md-3 col-12">
        <a class="btn btn-info col-12" href="{{ route('dashboard.index') }}"><span class="fas fa-home">&nbsp;</span>Trang chủ</a>
    </div>
    <div class="col-md-3 col-12 mt-sp-2">
        <a class="btn btn-warning col-12" href="{{ route('bot.index') }}"><span class="fas fa-robot">&nbsp;</span>AresBo Bot</a>
    </div>
    <div class="col-md-3 col-12 mt-sp-2">
        <a class="btn btn-danger col-12" href="javascript:void(0);" onclick="document.getElementById('form-logout').submit();"><span class="fas fa-sign-out-alt">&nbsp;</span>Đăng xuất</a>
    </div>
    <div class="col-md-3 col-0"></div>
</div>
<form id="form-logout" method="post" action="{{ route('backend.logout') }}" class="form-horizontal" enctype="multipart/form-data">
    @csrf
</form>
