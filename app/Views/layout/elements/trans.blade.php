@if(getSystemConfig('trans_with_editor'))
<div class="hide" id="trans_editor_container">
    <div style="padding: 20px" class="trans_editor_container">
        <div id="error_msg">
        </div>
        <br/>
        <textarea name="trans_editor" style="width: 500px" id="trans_editor"></textarea>
        <button class="btn btn-danger tippy" onclick="saveTrans()" style="cursor: pointer; height: 40px;width: 70px;margin: 0 auto; display: block" data-arrow="true">Save</button>
    </div>
</div>
{!! MyForm::open() !!}
{!! MyForm::close() !!}
<script>
    function saveTrans() {
        var val = $('.tippy-tooltip-content #trans_editor').val();
        var source = $('.trans-with-editor.active').data('source');
        sendRequest({
            url: 'update-lang',
            type: 'POST',
            data: {
                source: source,
                val: val
            },
        }, function (response) {
            if (!response.ok) {
                return showErrorFlash(response.message, '.trans_editor_container', false);
            }
            $('.trans-with-editor.active').html(val);
            $('.tippy-popper.html-template').remove();
        });
    }

    // HTML & nested tooltip example
    tippy('.trans-with-editor', {
        html: '#trans_editor_container',
        interactive: true,
        interactiveBorder: 50,
        distance: 15,
        theme: "light",
        animation: "scale",
        arrowsize: "big",
        animatefill: "false",
        arrow: "true",
        onShown: function (e) {
            var val = $('.trans-with-editor.active').html();
            $('.tippy-tooltip-content #trans_editor').val(val);
        }
    });
</script>
@endif
