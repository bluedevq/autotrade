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
                <div class="col-md-2 col-12">
                    <label for="role" class="form-label fw-bold" aria-hidden="true">Loại tài khoản</label>
                </div>
                <div class="col-md-2 col-12">
                    <select class="form-select" id="role" name="role_eq">
                        <option value="">----</option>
                        @foreach((array)\App\Helper\Common::getConfig('user_role_text') as $value => $roleName)
                        <option value="{{ $value }}" {{ (string)request()->get('role_eq') ==  (string)$value ? 'selected="selected"' : ''}}>{{ $roleName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-2 col-12">
                    <label for="status" class="form-label fw-bold" aria-hidden="true">Trạng thái</label>
                </div>
                <div class="col-md-2 col-12">
                    <select class="form-select" id="status" name="status_eq">
                        <option value="">----</option>
                        @foreach((array)\App\Helper\Common::getConfig('user_status_text') as $value => $statusName)
                        <option value="{{ $value }}" {{ (string)request()->get('status_eq') ===  (string)$value ? 'selected="selected"' : ''}}>{{ $statusName }}</option>
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
            <a class="btn btn-outline-success-custom" href="{{ route('user.create') }}"><span class="fas fa-plus" aria-hidden="true">&nbsp;</span>Thêm mới</a>
        </div>
    </div>
    <div class="row mt-2">
        <table class="table table-bordered table-striped table-dark table-hover list-user col-12" style="word-break: break-all">
            <thead>
            <th>Email</th>
            <th>Tên người dùng</th>
            <th>Ngày hết hạn</th>
            <th>Tài khoản AresBO</th>
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
                        <td>{!! $entity->getExpiredDate() !!}</td>
                        <td>{!! $entity->getBotUsers() !!}</td>
                        <td>{!! $entity->getRoleText() !!}</td>
                        <td>{!! $entity->getStatus() !!}</td>
                        <td class="text-center">
                        @if (backendGuard()->user()->role == \App\Helper\Common::getConfig('user_role.supper_admin') || $entity->role == \App\Helper\Common::getConfig('user_role.normal') || $entity->id == backendGuard()->user()->id)
                            <a href="{{ route('user.edit', $entity->id) }}" class="btn btn-outline-primary-custom"><span class="fas fa-edit">&nbsp;</span>Sửa</a>
                            @if($entity->id != backendGuard()->user()->id)<a href="javascript:void(0)" onclick="UserController.deleteConfirm('{{ route('user.delete', $entity->id) }}', '{{ $entity->email }}')" class="btn btn-outline-danger-custom"><span class="fas fa-trash">&nbsp;</span>Xóa</a>@endif
                        @endif
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
