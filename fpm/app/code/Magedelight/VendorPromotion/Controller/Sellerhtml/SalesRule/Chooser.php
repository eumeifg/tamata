<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\VendorPromotion\Controller\Sellerhtml\SalesRule;

class Chooser extends \Magedelight\Backend\App\Action
{
    /**
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magedelight\Vendor\Model\Design $design
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Vendor\Model\Design $design,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->design = $design;
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context);
    }

    /**
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return type
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->_auth->isLoggedIn() || !$request->getParam('key')) {
            $data = '<script>window.location.href="'. $this->_urlInterface->getUrl('rbvendor/index/index').'"</script>';
        }
        return parent::dispatch($request);
    }

    public function execute()
    {
        $request = $this->getRequest();
        /**
         * $this->design->setDesignStore(0, 'adminhtml');
         */

        switch ($request->getParam('attribute')) {
            case 'sku':
                $block = $this->_view->getLayout()->createBlock(
                    '\Magedelight\VendorPromotion\Block\Sellerhtml\PromoWidgetChooserSku',
                    'promo_widget_chooser_sku',
                    ['data' => ['js_form_object' => 'actions_filter_fieldset'],
                    ]
                );
                break;

            case 'category_ids':
                $ids = $request->getParam('selected', []);
                if (is_array($ids)) {
                    foreach ($ids as $key => &$id) {
                        $id = (int) $id;
                        if ($id <= 0) {
                            unset($ids[$key]);
                        }
                    }

                    $ids = array_unique($ids);
                } else {
                    $ids = [];
                }


                $block = $this->_view->getLayout()->createBlock(
                    '\Magedelight\VendorPromotion\Block\Sellerhtml\CategoryCheckboxesTree',
                    'promo_widget_chooser_category_ids',
                    ['data' => ['js_form_object' => $request->getParam('form')]]
                )
                    ->setCategoryIds($ids);
                break;

            default:
                $block = false;
                break;
        }

        if ($block) {
            return $this->resultRawFactory->create()->setContents($block->toHtml());
        }
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::promotion');
    }
}
