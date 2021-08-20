@extends('layouts.backend.default')
@section('content')
    <h3>Quản lý người dùng</h3>
    <div class="row mt-2">
        <form action="{{ route('user.index') }}" method="GET" class="form-horizontal" enctype="multipart/form-data" show-loading="1">
            @csrf
            <div class="row mt-2">
                <div class="col-md-2 col-12">
                    <label for="email" class="form-label fw-bold" aria-hidden="true">Email</label>
                </div>
                <div class="col-md-4 col-12">
                    <input type="text" name="email_cons" class="form-control" id="email" value="{{ request()->get('email_cons') }}">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-2 col-12">
                    <label for="name" class="form-label fw-bold" aria-hidden="true">Tên người dùng</label>
                </div>
                <div class="col-md-4 col-12">
                    <input type="text" name="name_cons" class="form-control" id="name" value="{{ request()->get('name_cons') }}">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-2 col-0">&nbsp;</div>
                <div class="col-md-2 col-12">
                    <button class="btn btn-outline-default-custom reset col-12" type="button"><span class="fas fa-trash" aria-hidden="true">&nbsp;</span>Xóa tìm kiếm</button>
                </div>
                <div class="col-md-2 col-12">
                    <button class="btn btn-outline-primary-custom col-12" type="submit"><span class="fas fa-search" aria-hidden="true">&nbsp;</span>Tìm kiếm</button>
                </div>
            </div>
        </form>
    </div>
    <div class="row mt-2">
        <div class="col-md-10 col-0">&nbsp;</div>
        <div class="col-md-2 col-12 text-end">
            <a class="btn btn-outline-success-custom" href="{{ route('user.create') }}"><span class="fas fa-plus" aria-hidden="true">&nbsp;</span>Thêm mới</a>
        </div>
    </div>
    <div class="row mt-2">
        <table class="table table-bordered table-striped table-dark table-hover list-user col-12">
            <thead>
            <th>Email</th>
            <th>Tên người dùng</th>
            <th>Loại tài khoản</th>
            <th>Trạng thái</th>
            <th></th>
            </thead>
            <tbody>
            @if(!blank($entities))
                @foreach($entities as $entity)
                    <tr>
                        <td>{{ $entity->email }}</td>
                        <td>{!! $entity->getName() !!}</td>
                        <td>{!! $entity->getRole() !!}</td>
                        <td>{!! $entity->getStatus() !!}</td>
                        <td class="text-center">
                            <a href="{{ route('user.edit', $entity->id) }}" class="btn btn-outline-primary-custom"><span class="fas fa-edit">&nbsp;</span>Sửa</a>
                            @php $disabled = $entity->id == backendGuard()->user()->id || $entity->role == \App\Helper\Common::getConfig('user_role.admin') @endphp
                            <a href="javascript:void(0)" @if(!$disabled)onclick="UserController.deleteConfirm('{{ route('user.delete', $entity->id) }}', '{{ $entity->email }}')"@endif class="btn btn-outline-danger-custom {{ $disabled ? 'disabled' : '' }}"><span class="fas fa-trash">&nbsp;</span>Xóa</a>
                        </td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>
    <div class="modal delete-confirm">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header">
                    <h4 class="modal-title">Bạn có chắc chắn muốn xóa <span class="username text-info"></span>?</h4>
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
