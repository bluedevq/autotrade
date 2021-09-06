@extends('layouts.backend.default')
@section('content')
    <div class="row mt-2 mx-auto col-md-8">
        <h3 class="text-center">Điều khoản sử dụng và miễn trừ trách nhiệm</h3>
        <br>
        <p class="mt-5">
            1. Quý khách thừa nhận rằng <span class="fw-bold text-info">{{ env('APP_NAME') }}</span> là bên thứ 3 độc lập và được sử dụng như một phần mềm nội bộ, <span class="fw-bold text-info">{{ env('APP_NAME') }}</span> chỉ cung cấp công cụ phân tích, thống kê và các giải pháp tự động để thực hiện các giao dịch trên các sàn giao dịch quyền chọn.
            <br><br>
            2. Quý khách thừa nhận rằng các hoạt động giao dịch của mình có thể gặp rủi ro liên quan đến mạng, bao gồm cả lỗi phần cứng, phần mềm, máy chủ, đường truyền thông tin và internet. Bất kỳ lỗi nào như vậy đều có thể dẫn đến việc không thực hiện giao dịch của khách hàng theo lệnh đặt của họ. <span class="fw-bold text-info">{{ env('APP_NAME') }}</span> sẽ không chịu trách nhiệm đối với bất kỳ thiệt hại hoặc mất mát nào do các sự kiện nằm ngoài tầm kiểm soát của mình do sự chậm trễ khách quan từ hỏng hóc phần mềm và phần cứng, ngắt kết nối internet, lỗi thiết bị và chất lượng kém của dịch vụ viễn thông từ phía khách hàng, thiết bị của khách hàng bị trục trặc, mất điện.
            <br><br>
            3. Quý khách thừa nhận rằng việc đóng cửa sổ <span class="fw-bold text-info">{{ env('APP_NAME') }}</span>, dừng chạy auto sẽ không hủy đơn đặt hàng hoặc yêu cầu đã được sàn giao dịch nhận để xử lý.
            <br><br>
            4. Quý khách thừa nhận rằng nguồn thông tin duy nhất về giá tài sản là máy chủ của các sàn giao dịch. Tất cả các thông tin từ <span class="fw-bold text-info">{{ env('APP_NAME') }}</span> đều được truy xuất một chiều. Giá tài sản trên thiết bị đầu cuối giao dịch của khách hàng có thể thay đổi nếu kết nối giữa thiết bị đầu cuối giao dịch của khách hàng và Máy chủ của sàn giao dịch trở nên không ổn định, do các nguyên nhân khách quan từ đường truyền của quý khách, một số báo giá tài sản có thể không đến được thiết bị đầu cuối giao dịch của khách hàng.
            <br><br>
            5. Quý khách chịu rủi ro tài chính và các rủi ro khác trong trường hợp các giao dịch (và các hành động liên quan) trên thị trường tài chính bị cấm hoặc hạn chế bởi luật pháp của quốc gia nơi khách hàng thường trú.
            <br><br>
            6. Nếu quý khách đồng ý với tất cả điều khoản sử dụng trên, vui lòng bấm "Tôi đồng ý" để tiếp tục sử dụng dịch vụ <span class="fw-bold text-info">{{ env('APP_NAME') }}</span> của chúng tôi.
        </p>
        <div class="mx-auto text-center"><a class="btn btn-danger" href="{{ route('bot.index') }}">Tôi đồng ý</a></div>
    </div>
@stop
