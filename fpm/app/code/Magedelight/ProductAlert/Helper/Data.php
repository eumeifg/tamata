<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ProductAlert\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_registry;
    protected $_resultPageFactory;
    protected $_objectManager;
    protected $_messageManager;
    protected $_scopeConfig;
    protected $schedule;
    /**
     * @var \Magento\ProductAlert\Helper\Data
     */
    protected $_helper;


    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\ProductAlert\Helper\Data $helper,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Cron\Model\Schedule $schedule
    ) {
        parent::__construct($context);
        $this->_registry = $registry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_objectManager = $objectManager;
        $this->_messageManager = $messageManager;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_customerSession = $customerSession;
        $this->_helper = $helper;
        $this->schedule = $schedule;
    }

    public function getStockAlert($product)
    {
        if (!$product->getId() || !$this->_registry->registry('current_product')) { // this is the extension's setting.
            return '';
        }
        $html = '';
        $tempCurrentProduct = $this->_registry->registry('current_product');

        /*check if it is child product for replace product registed to child product.*/
        $isChildProduct = ($tempCurrentProduct->getId() != $product->getId());
        if ($isChildProduct) {
            $this->_registry->unregister('par_product_id');
            $this->_registry->unregister('product');

            $this->_registry->register(
                'par_product_id',
                $this->_registry->registry('main_product')
                    ? $this->_registry->registry('main_product')->getId()
                    : $tempCurrentProduct->getId()
            );
            $this->_registry->register('product', $product);
        }

        $pageResult = $this->_resultPageFactory->create();
        $alertBlock = $pageResult->getLayout()->createBlock(
            'Magento\ProductAlert\Block\Product\View',
            'productalert.stock.' . $product->getId()
        );
        if ($alertBlock && !$product->getData('rbnotification_hide_alert')) {
            if (!$this->_customerSession->isLoggedIn()) {
                if ($isChildProduct) {
                    $alertBlock->setTemplate('Magedelight_ProductAlert::product/view_email_simple.phtml');
                } else {
                    $alertBlock->setTemplate('Magedelight_ProductAlert::product/view_email.phtml');
                }
            } else {
                $alertBlock->setTemplate('Magento_ProductAlert::product/view.phtml');
                $alertBlock->setSignupUrl($this->_helper->getSaveUrl('stock'));
                $alertBlock->setHtmlClass('alert stock link-stock-alert');
                $alertBlock->setSignupLabel(__('Sign up to get notified when this configuration is back in stock'));
            }

            $html = $alertBlock->toHtml();
        }
        if ($isChildProduct) {
            $this->_registry->unregister('product');
            $this->_registry->register('product', $tempCurrentProduct);
        }

        return $html;
    }
    public function getPriceAlert($product, $isLogged)
    {
        if (!$product->getId() || !$this->_registry->registry('current_product')) { // this is the extension's setting.
            return '';
        }
        $tempCurrentProduct = $this->_registry->registry('current_product');

        $this->_registry->unregister('par_product_id');
        $this->_registry->unregister('product');
        $this->_registry->unregister('current_product');

        $this->_registry->register('par_product_id', $tempCurrentProduct->getId());
        $this->_registry->register('current_product', $product);
        $this->_registry->register('product', $product);
        $pageResult = $this->_resultPageFactory->create();
        $alertBlock = $pageResult->getLayout()->createBlock('Magento\ProductAlert\Block\Product\View', 'productalert.price' . $product->getId());
        $html = '';

        if ($alertBlock && !$isLogged && !$this->_scopeConfig->getValue('rbnotification/price/disable_guest')) {
            $alertBlock->setTemplate('Magedelight_ProductAlert::product/price/view_email_simple.phtml');
            $html = $alertBlock->toHtml();
        }

        $this->_registry->unregister('product');
        $this->_registry->unregister('current_product');
        $this->_registry->register('current_product', $tempCurrentProduct);
        $this->_registry->register('product', $tempCurrentProduct);

        return $html;
    }

    public function getProduct()
    {
        return $this->_registry->registry('product');
    }

    public function getSignupUrl($type)
    {

        return $this->_getUrl('productalert/email/' . $type, [
            'product_id' => $this->getProduct()->getId(),
            'parent_id' => $this->_registry->registry('par_product_id'),
            \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
        ]);
    }

    public function getEmailUrl($type)
    {
        return $this->_getUrl('productalert/email/' . $type);
    }

    public function addMessage()
    {
        $scheduleCollection = $this->schedule->getCollection()
            ->addFieldToFilter('job_code', ['eq' => 'Magedelight_Catalog_product_alert']);

        $scheduleCollection->getSelect()->order("schedule_id desc");
        $scheduleCollection->getSelect()->limit(1);
        if ($scheduleCollection->getSize() == 0) {
            $message = '';
//            $message = '<div style="font-size: 13px;">'
//                . __('No cron job "Magedelight_Catalog_product_alert" found. Please check your cron configuration: ')
//                . '<a href="https://support.rb.com/index.php?/Knowledgebase/Article/View/79/25/i-cant-send-notifications">'
//                . __('Read more') . '</a>'
//                . '</div>';
            $this->_messageManager->addNotice($message);
        }
    }

    public function getUrl($route, $params = [])
    {
        return parent::_getUrl($route, $params);
    }
}
