@extends('layouts.backend.register')
@section('content')
    @include('layouts.backend.elements.logo')
    @if(isset($verify))
        @include('backend.password.verify.' . $verify)
    @endif
@stop
