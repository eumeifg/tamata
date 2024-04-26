<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_FacebookPixel
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\FacebookPixel\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerLogin implements ObserverInterface
{

    /**
     * @var \Bss\FacebookPixel\Model\SessionFactory
     */
    protected $fbPixelSession;

    /**
     * @var \Bss\FacebookPixel\Helper\Data
     */
    protected $fbPixelHelper;

    /**
     * Register constructor.
     * @param \Bss\FacebookPixel\Model\SessionFactory $fbPixelSession
     * @param \Bss\FacebookPixel\Helper\Data $helper
     */
    public function __construct(
        \Bss\FacebookPixel\Model\SessionFactory $fbPixelSession,
        \Bss\FacebookPixel\Helper\Data $helper
    ) {
        $this->fbPixelSession = $fbPixelSession;
        $this->fbPixelHelper  = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomerdata();
        if (!$this->fbPixelHelper->isSocialLogin()
            || !$customer
        ) {
            return true;
        }
        if($customer['social_type'] == "facebook")
        {
            $data = [
                'social_type' => $customer['social_type'],
                'email' => $customer['email'],
                'fn' => $customer['firstname'],
                'ln' => $customer['lastname']
            ];    
            $this->fbPixelSession->create()->setFacebook($data);
        }
        if($customer['social_type'] == "google")
        {
            $data = [
                'social_type' => $customer['social_type'],
                'email' => $customer['email'],
                'fn' => $customer['firstname'],
                'ln' => $customer['lastname']
            ];    
            $this->fbPixelSession->create()->setGoogle($data);
        }


        return true;
    }
}
