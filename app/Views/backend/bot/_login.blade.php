<div class="container mt-5" style="background-color: #171b2c;border-color: #303539;border-radius: 20px;color: #fff">
    <div class="row pt-2 text-center col-md-12">
        <img height="50" src="{{ public_url('images/backend/logo.601a65eb.svg') }}" alt="Aresbo" style="height: 50px;">
        <h3 class="mt-5">Đăng nhập vào Tài khoản của bạn</h3>
    </div>
    <form method="post" action="{{ route('bot.token') }}" class="form-horizontal" enctype="multipart/form-data">
        @csrf
        <div class="container mt-5">
            <div class="row col-md-12">
                <div class="col-md-3 col-sm-0">&nbsp;</div>
                <div class="col-md-2 col-sm-4">
                    <label for="email" class="form-label" aria-hidden="true">Địa chỉ Email *</label>
                </div>
                <div class="col-md-4 col-sm-8">
                    <input type="email" name="email" class="form-control" id="email" value="{{ request()->get('email') }}">
                </div>
                <div class="col-md-3 col-sm-0">&nbsp;</div>
            </div>
        </div>
        <div class="container mt-2">
            <div class="row col-md-12">
                <div class="col-md-3 col-sm-0">&nbsp;</div>
                <div class="col-md-2 col-sm-4">
                    <label for="password" class="form-label" aria-hidden="true">Mật khẩu *</label>
                </div>
                <div class="col-md-4 col-sm-8">
                    <input type="password" name="password" class="form-control" id="password">
                </div>
                <div class="col-md-3 col-sm-0">&nbsp;</div>
            </div>
        </div>
        <div class="container mt-2 pb-2">
            <div class="row col-md-12">
                <div class="col-md-3 col-sm-0">&nbsp;</div>
                <div class="col-md-2 col-sm-12">
                    <button class="btn btn-lg btn-danger btn-block" name="submit" type="submit">
                        <span class="ls-icon ls-icon-login" aria-hidden="true">Đăng nhập</span>
                    </button>
                    <input type="hidden" name="return_url" value="{{ request()->get('return_url') }}">
                </div>
                <div class="col-md-7 col-sm-0">&nbsp;</div>
            </div>
        </div>
    </form>
</div>
