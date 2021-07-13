<div class="container p-2 border border-1 rounded-3" style="border-color: #303539">
    <div class="row pt-2 text-center col-12">
        <img height="50" src="{{ public_url('images/backend/logo.601a65eb.svg') }}" alt="Aresbo" style="height: 50px;">
    </div>
    <form method="post" action="{{ route('bot.token') }}" class="form-horizontal" enctype="multipart/form-data">
        @csrf
        <div class="row mt-5">
            <div class="col-md-3 col-sm-0">&nbsp;</div>
            <div class="col-md-2 col-sm-4">
                <label for="email" class="form-label" aria-hidden="true">Địa chỉ Email *</label>
            </div>
            <div class="col-md-4 col-sm-8">
                <input type="email" name="email" class="form-control" id="email" value="{{ request()->get('email') }}">
            </div>
            <div class="col-md-3 col-sm-0">&nbsp;</div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3 col-sm-0">&nbsp;</div>
            <div class="col-md-2 col-sm-4">
                <label for="password" class="form-label" aria-hidden="true">Mật khẩu *</label>
            </div>
            <div class="col-md-4 col-sm-8">
                <input type="password" name="password" class="form-control" id="password">
            </div>
            <div class="col-md-3 col-sm-0">&nbsp;</div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3 col-sm-0">&nbsp;</div>
            <div class="col-md-2 col-sm-12">
                <button class="btn btn-lg btn-danger btn-block" name="submit" type="submit">
                    <span class="ls-icon ls-icon-login" aria-hidden="true">Đăng nhập</span>
                </button>
                <input type="hidden" name="return_url" value="{{ request()->get('return_url') }}">
            </div>
            <div class="col-md-7 col-sm-0">&nbsp;</div>
        </div>
    </form>
</div>
