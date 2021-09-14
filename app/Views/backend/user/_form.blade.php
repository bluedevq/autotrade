<div class="row mt-2">
    <form action="{{ route('user.valid') }}" method="POST" class="form-horizontal" enctype="multipart/form-data" show-loading="1">
        @csrf
        <div class="row mt-2">
            <div class="col-md-2 col-12">
                <label for="email" class="form-label fw-bold" aria-hidden="true">Email</label>
            </div>
            <div class="col-md-4 col-12">
                <input type="text" name="email" class="form-control" id="email" value="{{ $entity->email }}">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2 col-12">
                <label for="password" class="form-label fw-bold" aria-hidden="true">Mật khẩu</label>
            </div>
            <div class="col-md-4 col-12">
                <input type="password" name="password" class="form-control" id="password" value="">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2 col-12">
                <label for="name" class="form-label fw-bold" aria-hidden="true">Tên người dùng</label>
            </div>
            <div class="col-md-4 col-12">
                <input type="text" name="name" class="form-control" id="name" value="{{ $entity->name }}">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2 col-12">
                <label for="phone" class="form-label fw-bold" aria-hidden="true">Số điện thoại</label>
            </div>
            <div class="col-md-4 col-12">
                <input type="text" name="phone" class="form-control" id="phone" value="{{ $entity->phone }}">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2 col-12">
                <label for="address" class="form-label fw-bold" aria-hidden="true">Địa chỉ</label>
            </div>
            <div class="col-md-4 col-12">
                <textarea class="form-control" name="address" id="address" rows="5">{!! $entity->address !!}</textarea>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2 col-12">
                <label for="expired_date" class="form-label fw-bold" aria-hidden="true">Ngày hết hạn</label>
            </div>
            <div class="col-md-4 col-12">
                <input type="text" name="expired_date" class="form-control datepicker" id="expired_date" value="{{ $entity->expired_date }}">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2 col-12">
                <label for="role" class="form-label fw-bold" aria-hidden="true">Loại tài khoản</label>
            </div>
            <div class="col-md-4 col-12">
                <select class="form-select" name="role">
                    @foreach($roles as $value => $roleName)
                    <option value="{{ $value }}" {{ $entity->role ==  $value ? 'selected="selected"' : ''}}>{{ $roleName }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2 col-12">
                <label for="status" class="form-label fw-bold" aria-hidden="true">Trạng thái</label>
            </div>
            <div class="col-md-4 col-12">
                <select class="form-select" name="status">
                    @foreach($status as $value => $statusName)
                    <option value="{{ $value }}" {{ $entity->status ==  $value ? 'selected="selected"' : ''}}>{{ $statusName }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2 col-12">&nbsp;</div>
            <div class="col-md-2 col-12">
                <a class="btn btn-outline-default-custom col-12" href="{{ route('user.index') }}"><span class="fas fa-undo" aria-hidden="true">&nbsp;</span>Quay lại</a>
            </div>
            <div class="col-md-2 col-12 mt-sp-2">
                <input type="hidden" name="id" value="{{ $entity->id }}">
                <button class="btn btn-outline-primary-custom col-12" type="submit"><span class="fas fa-save" aria-hidden="true">&nbsp;</span>Lưu</button>
            </div>
        </div>
    </form>
</div>
@if(!blank($entity->botUserQueues))
<hr>
<div class="row mt-2">
    <h3 class="p-0">Các tài khoản AresBO</h3>
    <table class="table table-bordered table-striped table-dark table-hover col-12 mt-2" style="word-break: break-all">
        <thead>
        <th>Email</th>
        <th>Tên biệt danh</th>
        <th>Người giới thiệu</th>
        <th>Cấp bậc</th>
        <th>Đăng nhập lần cuối</th>
        </thead>
        <tbody>
        @foreach($entity->botUserQueues as $botQueue)
            @php $botUser = $botQueue->botUser; @endphp
            @if(!blank($botUser))
            <tr>
                <td>{{ $botUser->email }}</td>
                <td>{{ $botUser->nick_name }}</td>
                <td>{{ $botUser->reference_name }}</td>
                <td>{{ $botUser->rank }}</td>
                <td>{{ $botUser->updated_at }}</td>
            </tr>
            @endif
        @endforeach
        </tbody>
    </table>
</div>
@endif
@push('scripts')
    <script type="application/javascript">
        $(document).ready(function () {
            $('.datepicker').datetimepicker({
                sideBySide: true,
                keepOpen: true,
                format: 'YYYY-MM-DD HH:mm:ss',
                icons: {
                    time: 'fas fa-clock',
                    date: 'fas fa-calendar-alt',
                    up: 'fas fa-chevron-up',
                    down: 'fas fa-chevron-down',
                    previous: 'fas fa-chevron-left',
                    next: 'fas fa-chevron-right',
                    today: 'fas fa-screenshot',
                    clear: 'fas fa-trash',
                    close: 'fas fa-window-close'
                },
                locale: 'vi',
            });
        });
    </script>
@endpush
