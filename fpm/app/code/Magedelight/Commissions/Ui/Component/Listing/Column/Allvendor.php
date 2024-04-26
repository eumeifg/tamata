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
namespace Magedelight\Commissions\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magedelight\Commissions\Model\Vendorcommission;

class Allvendor extends \Magento\Ui\Component\Listing\Columns\Column
{
    const COLUMN_NAME = 'vendor_id';
    const INDEX_FIELD = 'vendor_commission_id';

    protected $vendorcommission;
    
    protected $vendorFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Vendorcommission $vendorcommission,
        \Magedelight\Vendor\Model\VendorFactory  $vendorFactory,
        array $components = [],
        array $data = []
    ) {
        $this->vendorcommission = $vendorcommission;
        $this->vendorFactory =  $vendorFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $vendorcommissionId = $item[self::INDEX_FIELD];
                $this->vendorcommission->load($vendorcommissionId);
                $vendorId = $this->vendorcommission->getData(self::COLUMN_NAME);
                $item[self::COLUMN_NAME] = $this->_getVendorName($vendorId);
            }
        }
        return $dataSource;
    }
    private function _getVendorName($vendorId)
    {
        $vendorModel = $this->vendorFactory->create();
        $item = $vendorModel->load($vendorId);
        return $item->getBusinessName();
    }
}
