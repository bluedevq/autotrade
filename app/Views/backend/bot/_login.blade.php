<form method="post" action="{{ route('bot.token') }}" class="form-horizontal" enctype="multipart/form-data">
    @csrf
    <h3>Vui lòng đăng nhập AresBO trước khi sử dụng bot</h3>
    <div class="container mt-3">
        <div class="row col-md-6">
            <div class="col-md-4">
                <label for="email" class="form-label" aria-hidden="true">Email</label>
            </div>
            <div class="col-md-8">
                <input type="email" name="email" class="form-control" id="email" value="{{ request()->get('email') }}">
            </div>
        </div>
    </div>
    <div class="container mt-1">
        <div class="row col-md-6">
            <div class="col-md-4">
                <label for="password" class="form-label" aria-hidden="true">Password</label>
            </div>
            <div class="col-md-8">
                <input type="password" name="password" class="form-control" id="password">
            </div>
        </div>
    </div>
    <div class="container mt-1">
        <div class="row col-md-6">
            <button class="btn btn-lg btn-danger btn-block col-md-4 col-sm-12" name="submit" type="submit">
                <span class="ls-icon ls-icon-login" aria-hidden="true">Đăng nhập</span>
            </button>
            <input type="hidden" name="return_url" value="{{ request()->get('return_url') }}">
        </div>
    </div>
</form>
