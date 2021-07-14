<?php
$jsFiles = [
    'lib/jquery.min',
    'lib/bootstrap.min',
    'lib/bootstrap.bundle.min',
//    'lib/bootstrap-datepicker.min',
//    'lib/bootstrap-datepicker.ja.min',
//    'lib/bootstrap-timepicker.min',
    'lib/loadingoverlay.min',
    'lib/fontawesome.min',
    'common/xhr',
//    'common/system',
    'common/common',
    'backend/custom',
];
?>
{!! loadFiles($jsFiles, '', 'js') !!}
@include('layouts.backend.structures.footer_autoload')
<script type="application/javascript">
    showTime();
</script>
