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
class VendorRegisterFirstStep implements ObserverInterface
{

    const XML_PATH_EMAIL_TEMPLATE = 'emailconfiguration/vendor_register_first_step/template';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->scopeConfig->getValue('emailconfiguration/vendor_register_first_step/enabled')) {
            return;
        }
        $postData = $this->request->getPost();
        $adminemail = $this->scopeConfig->getValue('contact/email/recipient_email');
        $email = $postData['email'];
        $this->_sendNotificationAdminEmail($adminemail, $email);
    }

    protected function _sendNotificationAdminEmail($adminemail, $email)
    {
        $templateVars = [
            'email' => $email
        ];
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $transport = $this->_transportBuilder
            ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
            ->setTemplateOptions(
                [
                    'area' => \Magedelight\Backend\App\Area\FrontNameResolver::AREA_CODE,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setFromByScope('general')
            ->setTemplateVars($templateVars)
            ->addTo($adminemail)
            ->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_messageManager->addException($e, __('Email could not be sent. Please try again or contact us.'));
        }
    }
}
