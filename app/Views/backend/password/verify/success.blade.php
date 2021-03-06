<div class="row mx-auto col-md-4 d-flex align-items-center">
    <form method="post" action="{{ route('backend.password.new.valid') }}" class="form-horizontal mt-5 register" enctype="multipart/form-data">
        @csrf
        <div class="row text-center">
            <h3>Vui lòng nhập mật khẩu mới</h3>
        </div>
        <div class="mt-4">
            <div class="row">
                <div class="col-12">
                    <label for="password" class="form-label" aria-hidden="true">Mật khẩu mới *</label>
                </div>
                <div class="col-12 input-group">
                    <div class="input-group-addon form-login"><i class="fas fa-unlock-alt"></i></div>
                    <input type="password" name="password" class="form-control" id="password" aria-label="password" placeholder="Nhập mật khẩu" maxlength="20">
                    <span class="input-group-text password-hover" onclick="AdminController.togglePassword(this)"><span class="fas fa-eye-slash show-hide-password"></span></span>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <div class="row">
                <div class="col-12">
                    <input type="hidden" name="email" class="form-control" id="email" aria-label="email" value="{{ request()->get('email') }}">
                    <button class="btn btn-danger btn-block col-12" type="submit" onclick="showLoading()">Đổi mật khẩu</button>
                </div>
            </div>
        </div>
    </form>
</div>
