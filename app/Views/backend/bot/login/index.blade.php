<div class="aresbo-login container custom-label">
    <div class="row mx-auto col-md-4 text-center col-12">
        <img height="50" src="{{ public_url('images/backend/logo.svg') }}" alt="Aresbo" style="height: 50px;">
    </div>
    <div class="row mt-4 mx-auto col-md-4 text-center col-12">
        <hr>
        <h3>Đăng nhập vào tài khoản ARESBO</h3>
    </div>
    <div class="row mt-2 mb-2 mx-auto col-md-4 d-flex align-items-center">
        <form method="post" class="form-horizontal" enctype="multipart/form-data">
            @csrf
            <div class="mt-4">
                <div class="row">
                    <div class="col-12">
                        <label for="email" class="form-label" aria-hidden="true">Địa chỉ Email *</label>
                    </div>
                    <div class="col-12">
                        <input type="text" name="email" class="form-control" id="email" value="{{ request()->get('email') }}">
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <div class="row">
                    <div class="col-12">
                        <label for="password" class="form-label" aria-hidden="true">Mật khẩu *</label>
                    </div>
                    <div class="col-12 input-group">
                        <input type="password" name="password" class="form-control" id="password" aria-label="password">
                        <span class="input-group-text password-hover" onclick="BotController.togglePassword(this)"><span class="fas fa-eye-slash show-hide-password"></span></span>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-lg btn-danger btn-block col-12" type="button" onclick="BotController.login()"><span class="fas fa-sign-in-alt">&nbsp;</span>Đăng nhập</button>
                        <input type="hidden" name="return_url" value="{{ request()->get('return_url') }}">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
