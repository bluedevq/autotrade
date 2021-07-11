<?php
$jsFiles = [
    'lib/bootstrap.min',
    'lib/bootstrap-datepicker.min',
    'lib/bootstrap-datepicker.ja.min',
    'lib/bootstrap-timepicker.min',
    'common/xhr',
    'common/system',
    'common/common',
];
?>
{!! loadFiles($jsFiles, $area, 'js') !!}
@include('layout.backend.structures.footer_autoload')
<script>
    $('#sandbox-container .input-daterange').datepicker({
        language: "ja",
        todayHighlight: true
    });
</script>
