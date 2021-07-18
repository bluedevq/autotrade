<div class="row">
    <div class="col-md-2 col-12">
        <a class="btn btn-info col-12" href="{{ route('dashboard.index') }}"><span class="fas fa-home">&nbsp;</span>Trang chủ</a>
    </div>
    <div class="col-md-2 col-12">
        <a class="btn btn-danger col-12" href="{{ route('bot.index') }}"><span class="fas fa-robot">&nbsp;</span>AresBo Bot</a>
    </div>
    <div class="col-md-3 col-12">
        <a class="btn btn-primary col-12" href="{{ route('method-trade.index') }}"><span class="fas fa-journal-whills">&nbsp;</span>Phương pháp</a>
    </div>
    <div class="col-md-4 col-12">
        <a class="btn btn-warning col-12" href="javascript:void(0);" onclick="document.getElementById('form-logout').submit();"><span class="fas fa-sign-out-alt">&nbsp;</span>Đăng xuất khỏi hệ thống</a>
    </div>
    <div class="col-md-1 col-0"></div>
</div>
<form id="form-logout" method="post" action="{{ route('backend.logout') }}" class="form-horizontal" enctype="multipart/form-data">
    @csrf
</form>
