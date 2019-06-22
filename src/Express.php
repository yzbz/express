<?php

namespace Zhangpeng\Express;

class Express
{
    protected $type;
    protected $app_id; //商户id
    protected $app_key;
    protected $req_url;

    /**
     * Express constructor.
     *
     * @param        $app_id
     * @param        $app_key
     * @param string $req_url
     */
    public function __construct($app_id, $app_key, $req_url = 'http://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx')
    {
        $this->app_id = $app_id;
        $this->app_key = $app_key;
        $this->req_url = $req_url;
    }


    public function getOrderInfoByJson($code)
    {
        $requestData = "{'LogisticCode':'" . $code . "'}";
        $datas = array(
            'EBusinessID' => $this->app_id,
            'RequestType' => '2002',
            'RequestData' => urlencode($requestData),
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $this->app_key);
        $result = $this->sendPost($this->req_url, $datas);
        if ($result['Success'] == true) {
            return $this->getOrderTracesByJson($result);
        } else {
            return $result['Reason'];
        }
    }

    private function getOrderTracesByJson($data)
    {
        $requestData = "{'LogisticCode':'" . $data['LogisticCode'] . "','ShipperCode':'" . $data['Shippers'][0]['ShipperCode'] . "'}";
        $datas = array(
            'EBusinessID' => $this->app_id,
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData),
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $this->app_key);
        $result = $this->sendPost($this->req_url, $datas);
        if ($result['Success'] == true) {
            return ['trace' => $result['Traces'], 'state' => $result['State'], 'express_name' => $data['Shippers'][0]['ShipperName']];
        }
        return $result['Reason'];
    }

    private function sendPost($url, $datas)
    {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if (empty($url_info['port'])) {
            $url_info['port'] = 80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader .= "Host:" . $url_info['host'] . "\r\n";
        $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader .= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader .= "Connection:close\r\n\r\n";
        $httpheader .= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets .= fread($fd, 128);
        }
        fclose($fd);
        return json_decode($gets, true);
    }

    private function encrypt($data, $appkey)
    {
        return urlencode(base64_encode(md5($data . $appkey)));
    }

}
