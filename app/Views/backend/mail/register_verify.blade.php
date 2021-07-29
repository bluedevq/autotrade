<div style="overflow: hidden;">
    <div style="margin: 0;padding: 0;background-color: #080d1f;font-family: 'Open Sans',sans-serif;font-size: 14px;color: #ffffff;font-weight: 400;">
        <div style="padding-bottom: 20px;">
            <div style="text-align: center">
                <img style="height: 170px;object-fit: cover;" src="http://xoano.net/public/images/backend/logo.png" alt="">
            </div>
            <div style="color: #ffffff;width: 600px;margin: 0 auto;border: 1px solid #171b2c">
                <div style="padding: 25px 25px 25px 15px;background-color: #171b2c;">
                    <div style="padding-bottom:15px;font-size:20px;font-weight:bold;text-align: center">
                        <div>Xác nhận tài khoản</div>
                        <div style="display: block;overflow: hidden;width: 114px;height: 3px;border-radius: 5px;background-color: #fa2843;text-align: center;margin: 0 auto;margin-top: 15px"></div>
                    </div>
                </div>
                <div style="padding: 20px 30px">
                    <div style="padding: 5px 0 15px 0">
                        Kính gửi <span style="font-size: 17px;font-weight: bold">{{ $data['name'] }},</span>
                    </div>
                    <div style="padding: 5px 0">
                        Cảm ơn bạn đã đăng ký tài khoản.
                    </div>
                    <div style="padding: 5px 0">
                        Vui lòng xác nhận email của bạn bằng cách bấm vào nút bên dưới
                    </div>
                    <div style="padding: 10px 0;text-align:center">
                        <a href="{{ $data['confirm_url'] }}"
                           style="text-align: center;padding: 0 20px;line-height: 50px;overflow: hidden;border-radius: 5px;text-decoration: none;background-color: #fa2843;color: #fff;font-size: 16px;margin-bottom: 10px;display: inline-block;font-weight: bold"
                           target="_blank">Xác nhận</a>
                    </div>
                    <div style="padding: 5px 0">
                        Nếu không phải là bạn, vui lòng liên hệ ngay với chúng tôi qua email:<br>
                        <a href="mailto:bluedevq@gmail.com" style="text-decoration: none;color: #fa2843" target="_blank">bluedevq@gmail.com</a>
                    </div>
                    <div style="padding: 25px 0 5px 0">
                        Trân trọng,
                    </div>
                    <div style="padding: 5px 0">
                        Bluedevq
                    </div>
                </div>
                <div style="padding-top: 25px;padding-bottom: 5px;font-weight: normal;color: #4c616c;text-align: center">
                    <span>© {{ date('Y') }} Bluedevq All rights reserved.</span>
                </div>
            </div>
        </div>
    </div>
</div>
