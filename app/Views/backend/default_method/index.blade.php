@extends('layouts.backend.default')
@section('content')
    <h3>Quản lý phương pháp mặc định</h3>
    <div class="row mt-2">
        <form action="{{ route('default.method.index') }}" method="GET" class="form-horizontal" enctype="multipart/form-data" show-loading="1">
            @csrf
            <div class="row mt-2">
                <div class="col-md-2 col-12">
                    <label for="name" class="form-label fw-bold" aria-hidden="true">Tên phương pháp</label>
                </div>
                <div class="col-md-4 col-12">
                    <input type="text" name="name_cons" class="form-control" id="name" value="{{ request()->get('name_cons') }}">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-2 col-12">
                    <label for="type" class="form-label fw-bold" aria-hidden="true">Loại phương pháp</label>
                </div>
                <div class="col-md-2 col-12">
                    <select class="form-select" id="status" name="type_eq">
                        <option value="">----</option>
                        @foreach((array)\App\Helper\Common::getConfig('aresbo.method_type.text') as $value => $typeName)
                        <option value="{{ $value }}" {{ (string)request()->get('type_eq') ===  (string)$value ? 'selected="selected"' : ''}}>{{ $typeName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-2 col-0">&nbsp;</div>
                <div class="col-md-2 col-12">
                    <button class="btn btn-outline-default-custom reset col-12" type="button"><span class="fas fa-trash" aria-hidden="true">&nbsp;</span>Xóa tìm kiếm</button>
                </div>
                <div class="col-md-2 col-12 mt-sp-2">
                    <button class="btn btn-outline-primary-custom col-12" type="submit"><span class="fas fa-search" aria-hidden="true">&nbsp;</span>Tìm kiếm</button>
                </div>
            </div>
        </form>
    </div>
    <div class="row mt-2">
        <div class="col-md-10 col-0">&nbsp;</div>
        <div class="col-md-2 col-12 text-end">
            <a class="btn btn-outline-success-custom" href="{{ route('default.method.create') }}"><span class="fas fa-plus" aria-hidden="true">&nbsp;</span>Thêm mới</a>
        </div>
    </div>
    <div class="row mt-2">
        <table class="table table-bordered table-striped table-dark table-hover list-user col-12" style="word-break: break-all">
            <thead>
            <th>Tên phương pháp</th>
            <th>Loại phương pháp</th>
            <th>Tín hiệu</th>
            <th>Lệnh</th>
            <th>Trạng thái</th>
            <th></th>
            </thead>
            <tbody>
            @if(!blank($entities))
                @foreach($entities as $entity)
                    <tr>
                        <td>{!! $entity->name !!}</td>
                        <td>{!! $entity->getDefaultMethodType() !!}</td>
                        <td>{!! $entity->getDefaultMethodSignal() !!}</td>
                        <td>{!! $entity->getDefaultMethodOrderPattern() !!}</td>
                        <td>{!! $entity->getDefaultMethodStatus() !!}</td>
                        <td class="text-center">
                            <a href="{{ route('default.method.edit', $entity->id) }}" class="btn btn-outline-primary-custom"><span class="fas fa-edit">&nbsp;</span>Sửa</a>
                            <a href="javascript:void(0)" onclick="DefaultMethodController.deleteConfirm('{{ route('default.method.delete', $entity->id) }}', '{{ $entity->name }}')" class="btn btn-outline-danger-custom"><span class="fas fa-trash">&nbsp;</span>Xóa</a>
                        </td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>
    <div class="row mt-2 mx-auto">
        {{ $paginator->appends(request()->all())->links('layouts.backend.elements._paging') }}
    </div>
    <div class="modal delete-confirm">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header">
                    <h4 class="modal-title">Bạn có chắc chắn muốn xóa <span class="default-method-name text-info"></span>?</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST" class="form-horizontal" enctype="multipart/form-data" show-loading="1">
                        @csrf
                        <div class="row mt-2">
                            <div class="col-12 text-center">
                                <input type="hidden" name="id" id="delete_method_id" value="">
                                <button class="btn btn-outline-danger-custom" type="submit"><span class="fas fa-trash" aria-hidden="true">&nbsp;</span>Xóa</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
