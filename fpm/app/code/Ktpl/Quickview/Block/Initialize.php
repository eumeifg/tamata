<?php
namespace Ktpl\Quickview\Block;

/**
 * Quickview Initialize block
 */
class Initialize extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Ktpl\QuickView\Helper\Data
     */
    private $helper;

    /**
     * @param \Ktpl\Quickview\Helper\Data $helper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Ktpl\Quickview\Helper\Data $helper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Returns config
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'baseUrl' => $this->getBaseUrl(),
            'closeSeconds' => $this->helper->getCloseSeconds(),
            'showMiniCart' => $this->helper->getScrollAndOpenMiniCart(),
            'showShoppingCheckoutButtons' => $this->helper->getShoppingCheckoutButtons()
        ];
    }

    /**
     * Return base url.
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}
