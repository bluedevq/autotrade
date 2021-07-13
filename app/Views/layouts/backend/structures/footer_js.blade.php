<?php
$jsFiles = [
    'lib/jquery.min',
    'lib/bootstrap.min',
    'lib/bootstrap-datepicker.min',
    'lib/bootstrap-datepicker.ja.min',
    'lib/bootstrap-timepicker.min',
    'lib/loadingoverlay.min',
    'lib/fontawesome.min',
    'common/xhr',
//    'common/system',
    'common/common',
];
?>
{!! loadFiles($jsFiles, '', 'js') !!}
@include('layouts.backend.structures.footer_autoload')
<script>
    $('#sandbox-container .input-daterange').datepicker({
        language: "ja",
        todayHighlight: true
    });
</script>
