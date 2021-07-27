<div class="modal" id="research-method">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-danger">
            <div class="modal-header">
                <h4 class="modal-title">Phân tích 50 lệnh gần nhất</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <canvas id="chart-method" width="400" height="400"></canvas>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<div class="modal" id="form-method">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-danger">
            <div class="modal-header">
                <h4 class="modal-title">Thêm phương pháp</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="BotController.resetForm()"></button>
            </div>
            <div class="modal-body">
                <form data-action="{{ route('bot_method.valid') }}" method="POST" class="form-horizontal" enctype="multipart/form-data">
                    @csrf
                    <div class="mt-2">
                        <ul class="list-group validate-method"></ul>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-2 col-0 pc">&nbsp;</div>
                        <div class="col-md-3">
                            <label for="name" class="form-label fw-bold" aria-hidden="true">Tên phương pháp</label>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="name" class="form-control" id="name" value="">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-2 col-0 pc">&nbsp;</div>
                        <div class="col-md-3">
                            <label for="type" class="form-label fw-bold" aria-hidden="true">Loại phương pháp</label>
                        </div>
                        <div class="col-md-4">
                            <select name="type" class="form-select form-select-lg" id="type">
                                @php $methodType = \App\Helper\Common::getConfig('aresbo.method_type.text') @endphp
                                @foreach($methodType as $methodValue => $methodName)
                                    <option value="{{ $methodValue }}">{{ $methodName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-2 col-0 pc">&nbsp;</div>
                        <div class="col-md-3">
                            <label for="signal" class="form-label fw-bold" aria-hidden="true">Tín hiệu</label>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="signal" class="form-control" id="signal" value="">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-2 col-0 pc">&nbsp;</div>
                        <div class="col-md-3">
                            <label for="order_pattern" class="form-label fw-bold" aria-hidden="true">Lệnh đặt</label>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="order_pattern" class="form-control" id="order_pattern" value="">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-2 col-0 pc">&nbsp;</div>
                        <div class="col-md-3">
                            <label for="stop_loss" class="form-label fw-bold" aria-hidden="true">Cắt lỗ</label>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="stop_loss" class="form-control" id="stop_loss" value="">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-2 col-0 pc">&nbsp;</div>
                        <div class="col-md-3">
                            <label for="stop_win" class="form-label fw-bold" aria-hidden="true">Chốt lời</label>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="stop_win" class="form-control" id="stop_win" value="">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-2 col-0 pc">&nbsp;</div>
                        <div class="col-md-3">
                            <label for="type" class="form-label fw-bold" aria-hidden="true">Trạng thái</label>
                        </div>
                        <div class="col-md-4">
                            <select name="status" class="form-select form-select-lg" id="status">
                                @php $methodStatus = \App\Helper\Common::getConfig('aresbo.method.text') @endphp
                                @foreach($methodStatus as $statusValue => $statusName)
                                    <option value="{{ $statusValue }}">{{ $statusName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-5">&nbsp;</div>
                        <div class="col-md-4">
                            <input type="hidden" name="id" id="id" value="">
                            <button class="btn btn-lg btn-primary btn-block col-12" type="button" onclick="BotController.validateMethod()">
                                <span class="fas fa-save" aria-hidden="true">&nbsp;</span>Lưu lại
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<div class="modal" id="delete-method">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-danger">
            <div class="modal-header">
                <h4 class="modal-title">Xóa phương pháp <span class="method-title fw-bold"></span> ?</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form data-action="{{ route('bot_method.delete') }}" method="POST" class="form-horizontal" enctype="multipart/form-data">
                    @csrf
                    <div class="row mt-2">
                        <div class="col-12 text-center">
                            <input type="hidden" name="id" id="delete_method_id" value="">
                            <button class="btn btn-lg btn-danger btn-block col-md-2 col-12" type="button" onclick="BotController.deleteMethod()">
                                <span class="fas fa-trash" aria-hidden="true">&nbsp;</span>Xóa
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
