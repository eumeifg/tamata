<?php
namespace Ktpl\Quickview\Plugin;

class PageConfigStructure
{

    /**
     * @var \Ktpl\Quickview\Helper\Data
     */
    private $helper;

    /**
     * PageConfigStructure constructor.
     * @param \Ktpl\Quickview\Helper\Data $helper
     */
    public function __construct(
        \Ktpl\Quickview\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Modify the hardcoded breakpoint for styles-menu.css
     * @param \Magento\Framework\View\Page\Config\Structure $subject
     * @param string $name
     * @param array $attributes
     * @return $this
     */
    public function beforeAddAssets(
        \Magento\Framework\View\Page\Config\Structure $subject,
        $name,
        $attributes
    ) {
        $isQuickviewEnabled = $this->helper->isEnabled();

        if (!$isQuickviewEnabled) {
            switch ($name) {
                case 'Ktpl_Quickview::css/magnific-popup.css':
                    $name = '';
                    break;
            }
        }

        return [$name, $attributes];
    }
}
