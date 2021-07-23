<div class="container mt-2 border border-1 rounded-3" style="border-color: #303539">
    <div class="row pt-2 text-center col-12">
        <h3>Đăng nhập vào tài khoản ARESBO</h3>
    </div>
    <div class="row mt-2 mb-2">
        <form method="post" action="{{ route('bot.token') }}" class="form-horizontal" enctype="multipart/form-data">
            @csrf
            <div class="row mt-2">
                <div class="col-md-3 pc">&nbsp;</div>
                <div class="col-md-2">
                    <label for="email" class="form-label" aria-hidden="true">Địa chỉ Email *</label>
                </div>
                <div class="col-md-4">
                    <input type="email" name="email" class="form-control" id="email" value="{{ request()->get('email') }}">
                </div>
                <div class="col-md-3 pc">&nbsp;</div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3 pc">&nbsp;</div>
                <div class="col-md-2">
                    <label for="password" class="form-label" aria-hidden="true">Mật khẩu *</label>
                </div>
                <div class="col-md-4">
                    <input type="password" name="password" class="form-control" id="password">
                </div>
                <div class="col-md-3 pc">&nbsp;</div>
            </div>
            <div class="row mt-2">
                <div class="col-md-5 col-0">&nbsp;</div>
                <div class="col-md-4 col-12">
                    <button class="btn btn-lg btn-danger btn-block col-12" type="submit" onclick="showLoading()">
                        <span class="fas fa-sign-in-alt" aria-hidden="true">&nbsp;</span>Đăng nhập
                    </button>
                </div>
                <div class="col-md-3 col-0">&nbsp;</div>
            </div>
        </form>
    </div>
</div>
