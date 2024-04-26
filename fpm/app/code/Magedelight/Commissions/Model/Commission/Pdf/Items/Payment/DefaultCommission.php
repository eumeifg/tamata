<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Model\Commission\Pdf\Items\Payment;

use Magento\Framework\Serialize\Serializer\Json;

/**
 * Description of DefaultCommission
 *
 * @author Rocket Bazaar Core Team
 */
class DefaultCommission extends \Magedelight\Commissions\Model\Commission\Pdf\Items\AbstractItems
{
    /**
     * Core string
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magedelight\Catalog\Helper\Pricing\Data $priceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magedelight\Catalog\Helper\Pricing\Data $priceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        Json $serializer = null,
        \Magedelight\Commissions\Model\Source\CalculationType $calculationType,
        \Magedelight\Commissions\Model\Source\CommissionType $commissionType,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->string = $string;
        parent::__construct(
            $context,
            $registry,
            $filesystem,
            $filterManager,
            $priceHelper,
            $resource,
            $resourceCollection,
            $serializer,
            $calculationType,
            $commissionType,
            $storeManager,
            $data
        );
    }

    /**
     * Draw item line
     *
     * @return void
     */
    public function draw()
    {
        $item = $this->getItem(); /*Magedelight\Commissions\Model\Commission\Payment\Interceptor*/
        $order = $this->getOrder();

        $pdf = $this->getPdf();
        $page = $this->getPage();
        
        $lines = [];
        
        // draw sr.No
        $lines[0][] = ['text' => $order['id'], 'feed' => 35];

        // draw Description
        $lines[0][] = ['text' => $this->string->split($order['text'], 60, true, true), 'feed' => 100];

        // draw Calculation break flow
        $lines[0][] = [
            'text' => $this->getAppliedFeesDetails($this->string->split($order['code'], 60, true, true), $item),
            'feed' => 510,
            'align' => 'right',
        ];
       
        // draw amount
        $lines[0][] = [
            'text' => $this->getAmount($item, $order),
            'feed' => 565,
            'align' => 'right',
        ];
       
        $lineBlock = ['lines' => $lines, 'height' => 20];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->setPage($page);
    }
}
