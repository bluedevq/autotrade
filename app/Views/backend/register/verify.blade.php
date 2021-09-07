@extends('layouts.backend.register')
@section('content')
    @include('layouts.backend.elements.logo')
    @if(isset($verify))
        @include('backend.register.verify.' . $verify)
    @endif
@stop
@push('scripts')
    <script type="application/javascript">
        AdminController.verifyCount(5, '{{ route('backend.login') }}');
    </script>
@endpush
