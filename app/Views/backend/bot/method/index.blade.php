<div class="row mt-2">
    <div class="col-md-3 col-12">
        <button class="btn btn-primary col-12" type="button" onclick="BotController.showHideMethod()"><span class="fas fa-list-alt">&nbsp;</span>Danh sách phương pháp</button>
    </div>
    <div class="list-method not-active" style="display: none">
        <div class="row mt-2">
            <div class="col-md-3 col-12">
                <a class="btn btn-danger col-12" href="javascript:void(0)" onclick="BotController.research()"><span class="fas fa-chart-line">&nbsp;</span>Phân tích thị trường</a>
            </div>
            <div class="col-md-3 col-12 mt-sp-2">
                <a class="btn btn-success col-12" href="javascript:void(0)" onclick="BotController.createMethod()"><span class="fas fa-plus">&nbsp;</span>Thêm mới phương pháp</a>
            </div>
        </div>
        <div class="row mt-2 list-methods-scroll">
            <div class="col-12">
                <table class="table table-striped table-dark table-hover text-center col-12">
                    <thead>
                    <th>{!! getSortLink('Tên PP', 'name') !!}</th>
                    <th class="pc">{!! getSortLink('Loại PP', 'type') !!}</th>
                    <th>{!! getSortLink('Tín hiệu', 'signal') !!}</th>
                    <th>{!! getSortLink('Lệnh', 'order_pattern') !!}</th>
                    <th class="pc">{!! getSortLink('Cắt lỗ', 'stop_loss') !!}</th>
                    <th class="pc">{!! getSortLink('Chốt lời', 'stop_win') !!}</th>
                    <th>{!! getSortLink('Trạng thái', 'status') !!}</th>
                    <th><span class="pc">Thao tác</span></th>
                    </thead>
                    <tbody class="method-item">
                    @if(blank($methods))
                        <tr><td colspan="8">Chưa có phương pháp nào</td></tr>
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
                                <div class="pc">
                                    <a class="btn btn-info" onclick="BotController.editMethod(this)" data-href="{{ route('bot_method.edit', $method->id) }}" href="javascript:void(0)"><span class="fas fa-edit">&nbsp;</span>Sửa</a>
                                    <a class="btn btn-danger" onclick="BotController.deleteMethodConfirm('{{ $method->getNameText() }}', '{{ $method->id }}')" href="javascript:void(0)"><span class="fas fa-trash">&nbsp;</span>Xóa</a>
                                </div>
                                <div class="sp" style="min-width: 70px;">
                                    <ul class="list-inline">
                                        <li class="list-inline-item"><a class="text-info" onclick="BotController.editMethod(this)" data-href="{{ route('bot_method.edit', $method->id) }}" href="javascript:void(0)"><span class="fas fa-edit"></span></a></li>
                                        <li class="list-inline-item"><a class="text-danger" onclick="BotController.deleteMethodConfirm('{{ $method->getNameText() }}', '{{ $method->id }}')" href="javascript:void(0)"><span class="fas fa-trash"></span></a></li>
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
