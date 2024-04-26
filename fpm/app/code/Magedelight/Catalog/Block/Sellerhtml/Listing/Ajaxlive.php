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
namespace Magedelight\Catalog\Block\Sellerhtml\Listing;

class Ajaxlive extends AbstractAjaxProduct
{
    public function getCollection()
    {
        $id = $this->getRequest()->getParam('id');
        $collection = $this->vendorProductFactory->create()->load($id);
        return $collection;
    }

    /**
     *
     * @return string
     */
    public function getSubmitUrlLive()
    {
        $queryParams = [
            'p' => $this->getRequest()->getParam('p', 1),
            'sfrm' => $this->getRequest()->getParam('sfrm', 'l'),
            'limit' => $this->getRequest()->getParam('limit', 10),
        ];
        return $this->getUrl('rbcatalog/listing/livesubmit/', ['_query' => $queryParams]);
    }
}
