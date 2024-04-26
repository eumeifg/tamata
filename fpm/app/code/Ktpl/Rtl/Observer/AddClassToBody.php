<?php
namespace Ktpl\Rtl\Observer;

use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\Event\ObserverInterface;

class AddClassToBody implements ObserverInterface
{
    const ELEMENT_TYPE_HTML         = 'html';
    const HTML_ATTRIBUTE_CLASS      = 'dir';
    const HTML_ATTRIBUTE_CLASS_LANG = 'lang';

    private $pageConfig;

    private $storeManager;

    public function __construct(
        PageConfig $pageConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->pageConfig    = $pageConfig;
        $this->storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $store_code = $this->storeManager->getStore()->getCode();
        $bodyClass  = '';

        if ($store_code == 'ar') {
            $this->pageConfig->setElementAttribute(
                self::ELEMENT_TYPE_HTML,
                self::HTML_ATTRIBUTE_CLASS,
                'rtl'
            );
            $this->pageConfig->setElementAttribute(
                self::ELEMENT_TYPE_HTML,
                self::HTML_ATTRIBUTE_CLASS_LANG,
                'ar'
            );
            $bodyClass = 'rtl_layout';
        }

        $this->pageConfig->addBodyClass($bodyClass);
    }
}
