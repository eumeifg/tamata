<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Controller\Adminhtml\Validator;

use Ktpl\ElasticSearch\Controller\Adminhtml\Validator;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class ValidateAutocompleteSpeed
 *
 * @package Ktpl\ElasticSearch\Controller\Adminhtml\Validator
 */
class ValidateAutocompleteSpeed extends Validator
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * ValidateAutocompleteSpeed constructor.
     *
     * @param JsonFactory $resultJsonFactory
     * @param Context $context
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        Context $context
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = $this->resultJsonFactory->create();
        if ($this->getRequest()->getParam('q') && !empty($this->getRequest()->getParam('q'))) {
            $query = $this->getRequest()->getParam('q');
        } else {
            $status = self::STATUS_ERROR;
            $result = '<p>Please specify search term</p>';
            return $response->setData(['result' => $result,'status' => $status]);
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeManager->getStore()->getBaseUrl();
        $url = $storeManager->getStore()->getBaseUrl().'/searchautocomplete/ajax/suggest/?q='.$query ;

        $start = microtime(true);
        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($request);
        curl_close($request);

        $result = round(microtime(true) - $start, 4);

        return $response->setData(['result' => $result . ' sec']);
    }
}
