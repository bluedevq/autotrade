@extends('layouts.backend.default')
@section('content')
    <div class="mt-2">
        <h3>Danh sách các phương pháp</h3>
        <div class="row">
            <div class="col-8">&nbsp;</div>
            <div class="col-4 text-end">
                <a class="btn btn-success" href="{{ route('method-trade.create') }}"><span class="fas fa-plus">&nbsp;</span>Thêm mới</a>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <table class="table-bordered text-center col-12">
                    <thead>
                        <th>Tên PP</th>
                        <th>Loại PP</th>
                        <th>Tín hiệu</th>
                        <th>Lệnh</th>
                        <th>Cắt lỗ</th>
                        <th>Chốt lời</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </thead>
                    <tbody>
                    @if($entities)
                        @foreach($entities as $entity)
                            <tr>
                                <td>{{ $entity->getNameText() }}</td>
                                <td>{{ $entity->getTypeText() }}</td>
                                <td>{{ $entity->getSignalText() }}</td>
                                <td>{{ $entity->getOrderPatternText() }}</td>
                                <td>{{ $entity->getStopLossText() }}</td>
                                <td>{{ $entity->getStopWinText() }}</td>
                                <td>{{ $entity->getMethodText() }}</td>
                                <td>
                                    <a class="btn btn-info" href="{{ route('method-trade.edit', $entity->id) }}"><span class="fas fa-edit">&nbsp;</span>Sửa</a>
                                    <a class="btn btn-danger" href="javascript:void(0)"><span class="fas fa-trash">&nbsp;</span>Xóa</a>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
