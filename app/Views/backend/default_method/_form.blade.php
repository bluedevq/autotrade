<div class="row mt-2">
    <form action="{{ route('default.method.valid') }}" method="POST" class="form-horizontal" enctype="multipart/form-data" show-loading="1">
        @csrf
        <div class="row mt-2">
            <div class="col-md-2 col-12">
                <label for="name" class="form-label fw-bold" aria-hidden="true">Tên phương pháp</label>
            </div>
            <div class="col-md-4 col-12">
                <input type="text" name="name" class="form-control" id="name" value="{{ $entity->name }}">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2 col-12">
                <label for="signal" class="form-label fw-bold" aria-hidden="true">Tín hiệu</label>
            </div>
            <div class="col-md-4 col-12">
                <input type="text" name="signal" class="form-control" id="signal" value="{{ $entity->signal }}">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2 col-12">
                <label for="order_pattern" class="form-label fw-bold" aria-hidden="true">Lệnh đặt</label>
            </div>
            <div class="col-md-4 col-12">
                <input type="text" name="order_pattern" class="form-control" id="order_pattern" value="{{ $entity->order_pattern }}">
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2 col-12">
                <label for="type" class="form-label fw-bold" aria-hidden="true">Loại phương pháp</label>
            </div>
            <div class="col-md-4 col-12">
                <select class="form-select" name="type">
                    @foreach((array)\App\Helper\Common::getConfig('aresbo.method_type.text') as $value => $typeName)
                        <option value="{{ $value }}" {{ $entity->type ==  $value ? 'selected="selected"' : ''}}>{{ $typeName }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2 col-12">
                <label for="twitch_chanel" class="form-label fw-bold" aria-hidden="true">Trạng thái</label>
            </div>
            <div class="col-md-4 col-12">
                <select class="form-select" name="status">
                    @foreach((array)\App\Helper\Common::getConfig('aresbo.method.text') as $value => $statusName)
                    <option value="{{ $value }}" {{ $entity->status ==  $value ? 'selected="selected"' : ''}}>{{ $statusName }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2 col-12">&nbsp;</div>
            <div class="col-md-2 col-12">
                <a class="btn btn-outline-default-custom col-12" href="{{ route('default.method.index') }}"><span class="fas fa-undo" aria-hidden="true">&nbsp;</span>Quay lại</a>
            </div>
            <div class="col-md-2 col-12 mt-sp-2">
                <input type="hidden" name="id" value="{{ $entity->id }}">
                <button class="btn btn-outline-primary-custom col-12" type="submit"><span class="fas fa-save" aria-hidden="true">&nbsp;</span>Lưu</button>
            </div>
        </div>
    </form>
</div>
