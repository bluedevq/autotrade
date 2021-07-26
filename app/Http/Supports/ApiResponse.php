<?php

namespace App\Http\Supports;

use GuzzleHttp\Client;
use Illuminate\Support\Str;

/**
 * Trait ApiResponse
 * @package App\Http\Supports
 */
trait ApiResponse
{
    /**
     * @var int
     */
    protected $_code = 200;

    /**
     * @var string
     */
    protected $_message = array();

    /**
     * @var array
     */
    protected $_data = array();

    /**
     * @var array
     */
    protected $_metaData = [];

    /**
     * @var bool
     */
    protected $_ok = true;

    /**
     * @param int $code
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function renderErrorJson($code = 200, $data = [])
    {
        $this->setOk(false);
        return $this->renderJson($data, $code);
    }

    /**
     * @param int $code
     * @param array $data
     * @return mixed
     */
    public function renderErrorXml($code = 200, $data = [])
    {
        $this->setOk(false);
        return $this->renderXml($data, $code);
    }

    /**
     * @param array $data
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function renderJson($data = [], $code = 200)
    {
        $data = $this->_buildResponse($data);
        $result = \Illuminate\Support\Facades\Response::json($data, $code);
        return $result;
    }

    /**
     * @param array $data
     * @param int $code
     * @return mixed
     */
    public function renderXml($data = [], $code = 200)
    {
        $data = $this->_buildResponse($data);
        $result = \Illuminate\Support\Facades\Response::xml($data, $code);
        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function _buildResponse($data = [])
    {
        return array_merge(array(
            'status' => $this->isOk(),
            'message' => (array)$this->getMessage(),
            'data' => $this->getData(),
            'meta' => $this->getMetaData()
        ), $data);
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->_code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->_message = (array)$message;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param array $data
     **@return $this
     */
    public function setData($data)
    {
        $this->_data = array_merge($this->_data, $data);
        return $this;
    }

    /**
     * @return array
     */
    public function getMetaData()
    {
        return $this->_metaData;
    }

    /**
     * @param array $metaData
     */
    public function setMetaData($metaData)
    {
        $this->_metaData = $metaData;
    }

    /**
     * @return bool
     */
    public function isOk()
    {
        return $this->_ok;
    }

    /**
     * @param bool $ok
     * @return $this
     */
    public function setOk($ok)
    {
        $this->_ok = $ok;
        return $this;
    }

    /**
     * @param $url
     * @param array $params
     * @param string $method
     * @param array $headers
     * @param bool $forceHeader
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestApi($url, $params = [], $method = 'POST', $headers = [], $forceHeader = false)
    {
        if (!$forceHeader) {
            $headers['Content-Type'] = 'application/json';
        }
        if (Str::lower($method) == 'get') {
            $url = $url . '?' . http_build_query($params);
        }

        $client = new Client();
        $response = $client->request($method, $url, [
            'body' => json_encode($params),
            'headers' => $headers
        ]);

        return json_decode($response->getBody(), true);
    }
}
