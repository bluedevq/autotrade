<form action="{{ route('method-trade.valid') }}" method="POST" class="form-horizontal" enctype="multipart/form-data">
    @csrf
    <div class="row mt-5">
        <div class="col-md-2">
            <label for="name" class="form-label" aria-hidden="true">Tên phương pháp</label>
        </div>
        <div class="col-md-4">
            <input type="text" name="name" class="form-control" id="name" value="{{ $entity->name }}">
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-2">
            <label for="type" class="form-label" aria-hidden="true">Loại phương pháp</label>
        </div>
        <div class="col-md-4">
            <select name="type" class="form-select form-select-lg">
                @php $methodType = \App\Helper\Common::getConfig('aresbo.method_type.text') @endphp
                @foreach($methodType as $methodValue => $methodName)
                    <option value="{{ $methodValue }}" {{ $methodValue == $entity->type ? 'selected="selected' : '' }}>{{ $methodName }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-2">
            <label for="signal" class="form-label" aria-hidden="true">Tín hiệu</label>
        </div>
        <div class="col-md-4">
            <input type="text" name="signal" class="form-control" id="signal" value="{{ $entity->signal }}">
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-2">
            <label for="order_pattern" class="form-label" aria-hidden="true">Lệnh đặt</label>
        </div>
        <div class="col-md-4">
            <input type="text" name="order_pattern" class="form-control" id="order_pattern" value="{{ $entity->order_pattern }}">
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-2">
            <label for="stop_loss" class="form-label" aria-hidden="true">Cắt lỗ</label>
        </div>
        <div class="col-md-4">
            <input type="text" name="stop_loss" class="form-control" id="stop_loss" value="{{ $entity->stop_loss }}">
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-2">
            <label for="stop_win" class="form-label" aria-hidden="true">Chốt lời</label>
        </div>
        <div class="col-md-4">
            <input type="text" name="stop_win" class="form-control" id="stop_win" value="{{ $entity->stop_win }}">
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-2">
            <label for="type" class="form-label" aria-hidden="true">Trạng thái</label>
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select form-select-lg">
                @php $methodStatus = \App\Helper\Common::getConfig('aresbo.method.text') @endphp
                @foreach($methodStatus as $statusValue => $statusName)
                    <option value="{{ $statusValue }}" {{ $statusValue == $entity->status ? 'selected="selected' : '' }}>{{ $statusName }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-2">&nbsp;</div>
        <div class="col-md-4">
            <input type="hidden" name="id" value="{{ $entity->id }}">
            <button class="btn btn-lg btn-primary btn-block col-12" name="submit" type="submit">
                <span class="fas fa-save" aria-hidden="true">&nbsp;</span>Lưu lại
            </button>
        </div>
    </div>
</form>
