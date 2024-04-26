<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Block\Adminhtml\ProductRequest\Grid\Renderer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

class ProductPrice extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var Json
     */
    protected $serializer;

    public function __construct(
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        Json $serializer = null
    ) {
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->priceHelper = $priceHelper;
    }

    public function _getValue(\Magento\Framework\DataObject $row)
    {
        $data = $row->getData('website_data');

        if (!empty($data) && $data != 'NULL') {
            $data = $this->serializer->unserialize($data);
            if (!empty($data['default']['price'])) {
                $pname =  ($data['default']['price'] != null &&
                    $data['default']['price'] != '') ? $data['default']['price'] : ' ';

                return $formattedPrice = $this->priceHelper->currency($pname, true, false);
            }
        }
        return '';
    }
}
