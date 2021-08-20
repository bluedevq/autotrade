<div class="row mt-2">
    <form action="{{ route('user.valid') }}" method="POST" class="form-horizontal" enctype="multipart/form-data" show-loading="1">
        @csrf
        <div class="row mt-2">
            <div class="col-2">
                <label for="email" class="form-label fw-bold" aria-hidden="true">Email</label>
            </div>
            <div class="col-4">
                <input type="text" name="email" class="form-control" id="email" value="{{ $entity->email }}">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-2">
                <label for="name" class="form-label fw-bold" aria-hidden="true">Tên người dùng</label>
            </div>
            <div class="col-4">
                <input type="text" name="name" class="form-control" id="name" value="{{ $entity->name }}">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-2">
                <label for="twitch_chanel" class="form-label fw-bold" aria-hidden="true">Loại tài khoản</label>
            </div>
            <div class="col-4">
                <select class="form-select" name="role">
                    @foreach((array)\App\Helper\Common::getConfig('user_role_text') as $value => $roleName)
                    <option value="{{ $value }}" {{ $entity->role ==  $value ? 'selected="selected' : ''}}>{{ $roleName }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-2">
                <label for="twitch_chanel" class="form-label fw-bold" aria-hidden="true">Trạng thái</label>
            </div>
            <div class="col-4">
                <select class="form-select" name="status">
                    @foreach((array)\App\Helper\Common::getConfig('user_status_text') as $value => $statusName)
                    <option value="{{ $value }}" {{ $entity->status ==  $value ? 'selected="selected' : ''}}>{{ $statusName }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-2">&nbsp;</div>
            <div class="col-2">
                <a class="btn btn-outline-default-custom col-12" href="{{ route('user.index') }}"><span class="fas fa-undo" aria-hidden="true">&nbsp;</span>Quay lại</a>
            </div>
            <div class="col-2">
                <input type="hidden" name="id" value="{{ $entity->id }}">
                <button class="btn btn-outline-primary-custom col-12" type="submit"><span class="fas fa-save" aria-hidden="true">&nbsp;</span>Lưu</button>
            </div>
        </div>
    </form>
</div>
