<div class="col-sm-12">
    <a class="btn btn-info" href="{{ route('dashboard.index') }}">Trang chủ</a>
    <a class="btn btn-danger" href="{{ route('bot.index') }}">AresBo Bot</a>
    <a class="btn btn-warning" href="javascript:void(0);" onclick="document.getElementById('form-logout').submit();">Đăng xuất khỏi hệ thống</a>
</div>
<form id="form-logout" method="post" action="{{ route('backend.logout') }}" class="form-horizontal" enctype="multipart/form-data">
    @csrf
</form>
