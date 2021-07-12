@extends('layouts.backend.auth')
@section('content')
    <div class="">
        <div class="wrapper">
            <form method="post" action="{{ route('backend.auth') }}" class="form-horizontal" enctype="multipart/form-data">
                @csrf
                <div class="mb-3 m-5">
                    <img class="rounded mx-auto d-block" src="{{ public_url('/images/backend/logo.jpg') }}" height="104" alt="Management">
                </div>

                @include('layouts.backend.elements.messages')

                <div class="container m-1">
                    <div class="row mx-auto col-6">
                        <div class="col-2">
                            <label for="email" class="form-label" aria-hidden="true">Email</label>
                        </div>
                        <div class="col-10">
                            <input type="email" name="email" class="form-control" id="email" value="{{ request()->get('email') }}">
                        </div>
                    </div>
                </div>

                <div class="container m-1">
                    <div class="row mx-auto col-6">
                        <div class="col-2">
                            <label for="password" class="form-label" aria-hidden="true">Password</label>
                        </div>
                        <div class="col-10">
                            <input type="password" name="password" class="form-control" id="password">
                        </div>
                    </div>
                </div>

                <div class="container m-1">
                    <div class="row mx-auto col-6">
                        <button class="btn btn-lg btn-success btn-block mx-auto col-4" name="submit" type="submit">
                            <span class="ls-icon ls-icon-login" aria-hidden="true">Login</span>
                        </button>
                        <input type="hidden" name="return_url" value="{{ request()->get('return_url') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
