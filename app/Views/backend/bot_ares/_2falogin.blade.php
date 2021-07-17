<div class="container p-2 border border-1 rounded-3" style="border-color: #303539">
    <div class="row pt-2 text-center col-12">
        <img height="50" src="{{ public_url('images/backend/logo.svg') }}" alt="Aresbo" style="height: 50px;">
        <h3>Xác minh bảo mật</h3>
    </div>
    <form method="post" action="{{ route('bot.token2fa') }}" class="form-horizontal" enctype="multipart/form-data">
        @csrf
        <div class="mt-5">
            <div class="row">
                <div class="col-md-3 col-sm-0">&nbsp;</div>
                <div class="col-md-3 col-sm-6">
                    <label for="code" class="form-label" aria-hidden="true">Mã Google Authentication</label>
                </div>
                <div class="col-md-4 col-sm-6">
                    <input type="text" name="code" class="form-control" id="code">
                </div>
                <div class="col-md-2 col-sm-0">&nbsp;</div>
            </div>
        </div>
        <div class="mt-5">
            <div class="row text-center">
                <div class="col-12">
                    <button class="btn btn-lg btn-danger btn-block col-md-2 col-sm-12" name="submit" type="submit">
                        <span class="ls-icon ls-icon-login" aria-hidden="true">Gửi</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
