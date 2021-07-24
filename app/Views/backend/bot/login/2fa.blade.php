<div class="aresbo-login-with2fa container mt-2 hide">
    <div class="row pt-2 text-center col-12">
        <h3>Xác minh bảo mật</h3>
    </div>
    <div class="row mt-2 mb-2 mx-auto col-md-4 d-flex align-items-center">
        <form method="post" action="{{ route('bot.loginWith2FA') }}" class="form-horizontal" enctype="multipart/form-data">
            @csrf
            <div class="mt-4">
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
                        <button class="btn btn-lg btn-danger btn-block col-12" type="submit" onclick="showLoading()"><span class="fas fa-sign-in-alt">&nbsp;</span>Gửi</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
