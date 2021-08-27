@if($paginator->total())
    @php
        $totalRecord  = $paginator->total();
        $currentPage = $paginator->currentPage();
        $perPage = $paginator->perPage();
        // paging info variables
        $fromRecord = (int)($currentPage - 1) * $perPage + 1;
        $toRecord = (($currentPage * $perPage) - $totalRecord) > 0 ? $totalRecord : ($currentPage * $perPage);
    @endphp
    <span>Hiển thị từ {{$fromRecord}} đến {{$toRecord}} trong tổng số {{$paginator->total()}}</span>
@endif
