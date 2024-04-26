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
namespace Magedelight\Vendor\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Description of Register
 *
 * @author Rocket Bazaar Core Team
 */
class AdminVendorPrepareMassDelete implements ObserverInterface
{

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    private $_vendorHelper;

    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/vendor_delete/template';

    /**
     *
     * @param \Magedelight\Vendor\Model\Vendor $vendor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magedelight\Vendor\Helper\Data $_vendorHelper
     */
    public function __construct(
        \Magedelight\Vendor\Model\Vendor $vendor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\Vendor\Helper\Data $_vendorHelper
    ) {
        $this->request = $request;
        $this->vendor = $vendor;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_vendorHelper = $_vendorHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $stLabel = 'Delete';
        $id = $observer->getEvent()->getVendorIds();
        $collection = $this->vendor->getCollection()->addFieldToFilter('vendor_id', ['in' => $id]);
        foreach ($collection as $data) {
            $displayname = $this->_vendorHelper->getVendorNameById($data->getVendorId());
            $email = $data->getEmail();
            $this->_sendNotification($email, $stLabel, $displayname);
        }
    }

    /**
     * @param $email
     * @param $stLabel
     * @param $displayname
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function _sendNotification($email, $stLabel, $displayname)
    {
        $templateVars = [
            'status' => $stLabel,
            'disply_name'  => $displayname
        ];
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setFromByScope('general')
            ->setTemplateVars($templateVars)
            ->addTo($email)
            ->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_messageManager->addException($e, __('Email could not be sent. Please try again or contact us.'));
        }
    }
}
