<div class="row mt-2">
    <div class="col-md-4 col-lg-3 col-12">
        <button class="btn btn-primary col-12" type="button" onclick="BotController.showHideMethod()"><span class="fas fa-list-alt">&nbsp;</span>Phương pháp</button>
    </div>
    <div class="col-md-4 col-lg-3 col-6 mt-sp-2 research-btn hide">
        <a class="btn btn-danger col-12" href="javascript:void(0)" onclick="BotController.research()"><span class="fas fa-chart-line">&nbsp;</span>Phân tích lệnh</a>
    </div>
    <div class="col-md-4 col-lg-3 col-6 mt-sp-2 add-method-btn hide">
        <a class="btn btn-success col-12" href="javascript:void(0)" onclick="BotController.createMethod()"><span class="fas fa-plus">&nbsp;</span>Thêm</a>
    </div>
    <div class="list-method hide not-active">
        <div class="row mt-2 list-methods-scroll">
            <div class="col-12">
                <table class="table table-striped table-dark table-hover text-center col-12">
                    <thead>
                    <th>Tên PP</th>
                    <th class="pc">Loại PP</th>
                    <th>Tín hiệu</th>
                    <th>Lệnh</th>
                    <th class="pc">Cắt lỗ</th>
                    <th class="pc">Chốt lời</th>
                    <th>Trạng thái</th>
                    <th><span class="pc">Thao tác</span></th>
                    </thead>
                    <tbody class="method-item">
                    @if(blank($methods))
                        <tr class="empty"><td colspan="8">Chưa có phương pháp nào</td></tr>
                    @else
                        @foreach($methods as $method)
                        <tr id="method_{{ $method->id }}">
                            <td>{{ $method->getNameText() }}</td>
                            <td class="pc">{{ $method->getTypeText() }}</td>
                            <td>{!! $method->getSignalText() !!}</td>
                            <td>{!! $method->getOrderPatternText() !!}</td>
                            <td class="pc">{{ $method->getStopLossText() }}</td>
                            <td class="pc">{{ $method->getStopWinText() }}</td>
                            <td>{{ $method->getMethodStatusText() }}</td>
                            <td>
                                <div class="row">
                                    <ul class="list-inline method-action">
                                        <li class="list-inline-item"><a class="btn btn-info" onclick="BotController.editMethod(this)" data-href="{{ route('bot_method.edit', $method->id) }}" href="javascript:void(0)"><span class="fas fa-edit">&nbsp;</span>Sửa</a></li>
                                        <li class="list-inline-item"><a class="btn btn-danger" onclick="BotController.deleteMethodConfirm('{{ $method->getNameText() }}', '{{ $method->id }}')" href="javascript:void(0)"><span class="fas fa-trash">&nbsp;</span>Xóa</a></li>
                                    </ul>
                                </div>
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
