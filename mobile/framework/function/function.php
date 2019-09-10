<?php
/**
 * mobile公共方法
 *
 * 公共方法
 *
 * @好商城 (c) 2015-2018 33HAO Inc. (http://www.33hao.com)
 * @license    http://www.33 hao.c om
 * @link       交流群号：138182377
 * @since      好商城提供技术支持 授权请购买shopnc授权
 */
defined('In33hao') or exit('Access Invalid!');

function output_data($datas, $extend_data = array(), $error = false)
{
    $data = array();
    $data['code'] = 200;
    if ($error) {
        $data['code'] = 400;
    }

    if (!empty($extend_data)) {
        $data = array_merge($data, $extend_data);
    }

    $data['datas'] = $datas;

    $jsonFlag = 0 && C('debug') && version_compare(PHP_VERSION, '5.4.0') >= 0
        ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        : 0;

    if ($jsonFlag) {
        header('Content-type: text/plain; charset=utf-8');
    }

    if (!empty($_GET['callback'])) {
        echo $_GET['callback'] . '(' . json_encode($data, $jsonFlag) . ')';
        die;
    } else {
        header("Access-Control-Allow-Origin:*");
        echo json_encode($data, $jsonFlag);
        die;
    }
}

function output_error($message, $extend_data = array())
{
    $datas = array('error' => $message);
    output_data($datas, $extend_data, true);
}

function mobile_page($page_count)
{
    //输出是否有下一页
    $extend_data = array();
    $current_page = intval($_GET['curpage']);
    if ($current_page <= 0) {
        $current_page = 1;
    }
    if ($current_page >= $page_count) {
        $extend_data['hasmore'] = false;
    } else {
        $extend_data['hasmore'] = true;
    }
    $extend_data['page_total'] = $page_count;
    return $extend_data;
}

function get_server_ip()
{
    if (isset($_SERVER)) {
        if ($_SERVER['SERVER_ADDR']) {
            $server_ip = $_SERVER['SERVER_ADDR'];
        } else {
            $server_ip = $_SERVER['LOCAL_ADDR'];
        }
    } else {
        $server_ip = getenv('SERVER_ADDR');
    }
    return $server_ip;
}

function http_get($url)
{
    return file_get_contents($url);
}

function http_post($url, $param)
{
    $postdata = http_build_query($param);

    $opts = array('http' =>
        array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );

    $context = stream_context_create($opts);

    return @file_get_contents($url, false, $context);
}

function http_postdata($url, $postdata)
{
    $opts = array('http' =>
        array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );

    $context = stream_context_create($opts);

    return @file_get_contents($url, false, $context);
}

/**
 * 发送短信验证码
 * @param $phone
 * @param $randCode
 * @return mixed
 */
function sendMobileCode($phone, $randCode)
{
    $apikey = '51836dc35ccb5f034784bc2e2dbe5694';
    $clnt = Yunpian\Sdk\YunpianClient::create($apikey);
    $param = [Yunpian\Sdk\YunpianClient::MOBILE => "$phone", Yunpian\Sdk\YunpianClient::TEXT => '【云片网】您的验证码是' . $randCode];
    $r = $clnt->sms()->single_send($param);
    return $r;
}

/**
 * 生成二维码
 * @param string $data
 * @param string $prefix
 * @param string $suffix
 * @param string $text
 * @return bool|string 二维码路径
 */
function makeQrCode($data = '', $prefix = 'qrcode', $suffix = 'png', $text = '注册二维码')
{
    if ($data == '') return false;
//    $font = BASE_VENDOR_PATH . '/endroid/qr-code/assets/noto_sans.otf';
    $qrCode = new Endroid\QrCode\QrCode($data);
    $qrCode->setSize(300);
    $qrCode->setWriterByName($suffix);
    $qrCode->setMargin(10);
    $qrCode->setEncoding('UTF-8');
//        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
    $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
    $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
//        $qrCode->setLabel('Scan the code', 16, $this->font);
//    $qrCode->setLabel($text, 16, $font);
//        $qrCode->setLogoPath($this->logoPath);
    $qrCode->setLogoWidth(150);
//        $qrCode->setRoundBlockSize(true);
//        $qrCode->setValidateResult(false);
    // Directly output the QR code
    header('Content-Type: ' . $qrCode->getContentType());
    //保存图片
    $fileName = $prefix . time() . '_' . rand(10, 9999) . '.' . $suffix;
    $saveQrcodeFile = BASE_QRCODE_PATH . '/' . $fileName;
//        echo $qrCode->writeString();
    // Save it to a file
    $qrCode->writeFile($saveQrcodeFile);
    // Create a response object
//        $response = new QrCodeResponse($qrCode);
    return '/data/upload/qrcode/' . $fileName;
}

