<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Controller\Sellerhtml\Account;

use Magento\Framework\Controller\Result;
use Magento\Framework\Encryption\Helper\Security;

class Tunnel extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        Result\RawFactory $resultRawFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * Forward request for a graph image to the web-service
     *
     * This is done in order to include the image to a HTTPS-page regardless of web-service settings
     *
     * @return  \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $error = __('invalid request');
        $httpCode = 400;
        $gaData = $this->_request->getParam('ga');
        $gaHash = $this->_request->getParam('h');
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        if ($gaData && $gaHash) {
            /** @var $helper \Magedelight\Vendor\Helper\Dashboard\Data */
            $helper = $this->_objectManager->get(\Magedelight\Vendor\Helper\Dashboard\Data::class);
            $newHash = $helper->getChartDataHash($gaData);
            if (Security::compareStrings($newHash, $gaHash)) {
                $params = null;
                $paramsJson = base64_decode(urldecode($gaData));
                if ($paramsJson) {
                    $params = json_decode($paramsJson, true);
                }
                if ($params) {
                    try {
                        /** @var $httpClient \Magento\Framework\HTTP\ZendClient */
                        $httpClient = $this->_objectManager->create(\Magento\Framework\HTTP\ZendClient::class);
                        $response = $httpClient->setUri(
                            \Magedelight\Vendor\Block\Sellerhtml\Dashboard\Graph::API_URL
                        )->setParameterGet(
                            $params
                        )->setConfig(
                            ['timeout' => 5]
                        )->request(
                            'GET'
                        );

                        $headers = $response->getHeaders();

                        $resultRaw->setHeader('Content-type', $headers['Content-type'])
                            ->setContents($response->getBody());
                        return $resultRaw;
                    } catch (\Exception $e) {
                        $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                        $error = __('see error log for details');
                        $httpCode = 503;
                    }
                }
            }
        }
        $resultRaw->setHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->setHttpResponseCode($httpCode)
            ->setContents(__('Service unavailable: %1', $error));
        return $resultRaw;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::account');
    }
}
