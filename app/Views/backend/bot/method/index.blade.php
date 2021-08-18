<div class="row mt-2">
    <div class="col-md-3 col-12">
        <a class="btn btn-info col-12" href="javascript:void(0)" onclick="BotController.toggleMethods()"><span class="fas fa-list-alt">&nbsp;</span>Phương pháp</a>
    </div>
    <div class="col-md-3 col-12 mt-sp-2 research-btn hide">
        <a class="btn btn-outline-danger-custom col-12" href="javascript:void(0)" onclick="BotController.research()"><span class="fas fa-chart-line">&nbsp;</span>Phân tích lệnh</a>
    </div>
    <div class="col-md-3 col-12 mt-sp-2 action-method-btn hide">
        <div class="row">
            <div class="col-6">
                <a class="btn btn-outline-success-custom col-12" href="javascript:void(0)" onclick="BotController.createMethod()"><span class="fas fa-plus">&nbsp;</span>Thêm</a>
            </div>
            <div class="col-6">
                <a class="btn btn-outline-primary-custom col-12 run-method disabled" href="javascript:void(0)" onclick="BotController.updateStatusMethod(true)"><span class="fas fa-play">&nbsp;</span>Chạy</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-12 mt-sp-2 action-method-btn hide">
        <div class="row">
            <div class="col-6">
                <a class="btn btn-outline-warning-custom col-12 stop-method disabled" href="javascript:void(0)" onclick="BotController.updateStatusMethod(false)"><span class="fas fa-stop">&nbsp;</span>Dừng</a>
            </div>
            <div class="col-6">
                <a class="btn btn-outline-danger-custom col-12 delete-method disabled" href="javascript:void(0)" onclick="BotController.deleteMethodConfirm()"><span class="fas fa-trash">&nbsp;</span>Xóa</a>
            </div>
        </div>
    </div>
</div>
<div class="row mt-2">
    <div class="list-method hide not-active">
        <div class="row mt-2 list-methods-scroll">
            <div class="col-12">
                <table class="table table-striped table-dark table-hover col-12">
                    <thead>
                    <th><input type="checkbox" id="select-all-methods" class="form-check-input" onclick="BotController.selectAllMethod()"></th>
                    <th>Tên PP</th>
                    <th class="pc">Loại PP</th>
                    <th class="text-start">Tín hiệu</th>
                    <th class="text-start">Lệnh</th>
                    <th>+/-</th>
                    <th class="pc">Cắt lỗ</th>
                    <th class="pc">Chốt lãi</th>
                    <th><span class="pc">Thao tác</span></th>
                    </thead>
                    <tbody class="method-item">
                    @if(blank($methods))
                        <tr class="empty"><td colspan="8">Chưa có phương pháp nào</td></tr>
                    @else
                        @foreach($methods as $method)
                            <tr id="method_{{ $method->id }}" class="{{ $method->status ? 'active' : 'stop' }}">
                                <td class="method-id"><input type="checkbox" class="form-check-input" onclick="BotController.selectMethod(this)" value="{{ $method->id }}"></td>
                                <td>{{ $method->getNameText() }}</td>
                                <td class="pc">{{ $method->getTypeText() }}</td>
                                <td class="method-signal">{!! $method->getSignalText() !!}</td>
                                <td class="method-pattern">{!! $method->getOrderPatternText() !!}</td>
                                <td class="method-profit">{!! $method->getProfitText() !!}</td>
                                <td class="pc method-stop-loss">{!! $method->getStopLossText() !!}</td>
                                <td class="pc method-take-profit">{!! $method->getTakeProfitText() !!}</td>
                                <td class="method-action">
                                    <a class="btn btn-info" onclick="BotController.editMethod(this)" data-href="{{ route('bot_method.edit', $method->id) }}" href="javascript:void(0)"><span class="fas fa-edit">&nbsp;</span>Sửa</a>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
