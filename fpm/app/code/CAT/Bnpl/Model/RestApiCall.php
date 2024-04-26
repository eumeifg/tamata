<?php

namespace CAT\Bnpl\Model;

use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class RestApiCall
{
    const ENVIRONMENT = 'payment/bnpl/bnpl_api/environment';
    /* staging data */
    const API_STAGING_END_POINT = 'payment/bnpl/bnpl_api/staging_end_point';
    const API_STAGING_TOKEN = 'payment/bnpl/bnpl_api/staging_token';
    /* production data */
    const API_PROD_END_POINT = 'payment/bnpl/bnpl_api/production_end_point';
    const API_PROD_TOKEN = 'payment/bnpl/bnpl_api/production_token';

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param CurlFactory $curlFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        CurlFactory $curlFactory,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    ) {
        $this->curlFactory = $curlFactory;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * @param $url
     * @param $method
     * @param $params
     * @param $header
     * @return array
     */
    public function execute($url, $method, $params=null, $header=[]) {
        $cUrl = $this->getCurlUrl($url);
        $headerData = $this->getHeader($header);

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $cUrl,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => $method,
          CURLOPT_POSTFIELDS =>$params,
          CURLOPT_HTTPHEADER => $headerData,
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response,1);
    }

    /**
     * @param $url
     * @return string
     */
    public function getCurlUrl($url)
    {
        $apiUrl = $this->scopeConfig->getValue(self::API_PROD_END_POINT);
        if ($this->getEnvironment() == 'staging') {
            $apiUrl = $this->scopeConfig->getValue(self::API_STAGING_END_POINT);
        }
        return $apiUrl.$url;
    }

    /**
     * @param $header
     * @return array
     */
    public function getHeader($header){
        $token = $this->scopeConfig->getValue(self::API_PROD_TOKEN);
        if ($this->getEnvironment() == 'staging') {
            $token = $this->scopeConfig->getValue(self::API_STAGING_TOKEN);
        }
        $header=[];
        $header['token'] = $token;
        $header['content-type'] = 'application/json';
        $heads = [];
        foreach ($header as $k => $v) {
            $heads[] = $k . ': ' . $v;
        }
        return $heads;
    }

    /**
     * @return mixed
     */
    public function getEnvironment() {
        return $this->scopeConfig->getValue(self::ENVIRONMENT);
    }
}
