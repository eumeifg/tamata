<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   Ktpl_ProductLabel
  * @author    Kirti Nariya <kirti.nariya@krishtechnolabs.com>
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */

namespace Ktpl\ProductLabel\Ui\Component\Source\Location;

use Ktpl\ProductLabel\Api\Data\ProductLabelInterface;

class View implements \Magento\Framework\Data\OptionSourceInterface
{
    private $viewsList;

    public function toOptionArray()
    {
        return $this->getViewsList();
    }

    private function getViewsList()
    {
        $this->viewsList[0] = [
            'value'     => 0,
            'view_id'   => '',
            'label'     => '',
        ];

        $this->viewsList[1] = [
            'value'     => ProductLabelInterface::PRODUCTLABEL_DISPLAY_PRODUCT,
            'view_id'   => 'product_view',
            'label'     => 'Product View',
        ];

        $this->viewsList[2] = [
            'value'     => ProductLabelInterface::PRODUCTLABEL_DISPLAY_LISTING,
            'view_id'   => 'product_listing',
            'label'     => 'Product Listing',
        ];

        return $this->viewsList;
    }
}
