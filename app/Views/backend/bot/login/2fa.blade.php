<div class="aresbo-login-with2fa container mt-2 custom-label hide">
    <div class="row mx-auto col-md-4 text-center col-12">
        <img height="50" src="{{ public_url('images/backend/logo.svg') }}" alt="Aresbo" style="height: 50px;">
    </div>
    <div class="row mt-4 mx-auto col-md-4 text-center col-12">
        <hr>
        <h3>Xác minh bảo mật</h3>
    </div>
    <div class="row mt-2 mb-2 mx-auto col-md-4 d-flex align-items-center">
        <form method="post" action="{{ route('bot.loginWith2FA') }}" class="form-horizontal" enctype="multipart/form-data">
            @csrf
            <div class="mt-4 aresbo-login-verify-device hide">
                <div class="row">
                    <div class="col-12">
                        <label for="td_code" class="form-label" aria-hidden="true">Mã Xác nhận Email</label>
                    </div>
                    <div class="col-8">
                        <input type="text" name="td_code" class="form-control" id="td_code">
                    </div>
                    <div class="col-4">
                        <button class="btn btn-danger col-12 request-code" type="button" onclick="BotController.requestCode()">Gửi Mã</button>
                    </div>
                </div>
            </div>
            <div class="mt-4 aresbo-login-authentication">
                <div class="row">
                    <div class="col-12">
                        <label for="code" class="form-label" aria-hidden="true">Mã Google Authentication</label>
                    </div>
                    <div class="col-12">
                        <input type="text" name="code" class="form-control" id="code">
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <div class="row">
                    <div class="col-12">
                        <input type="hidden" name="require_2fa" class="form-control" id="require_2fa" value="1">
                        <button class="btn btn-lg btn-danger btn-block col-12" type="submit" onclick="showLoading()"><span class="fas fa-sign-in-alt">&nbsp;</span>Gửi</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
