<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Block\Adminhtml\RatingType\Grid\Renderer;

class Type extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{

    protected $ratingTypeCollectionFactory;

    /**
     * @param \Magedelight\Vendor\Model\ResourceModel\RatingTypes\CollectionFactory $ratingTypeCollectionFactory
     */
    public function __construct(
        \Magedelight\Vendor\Model\ResourceModel\RatingTypes\CollectionFactory $ratingTypeCollectionFactory
    ) {
        $this->ratingTypeCollectionFactory = $ratingTypeCollectionFactory;
    }

    /**
     * Render review type
     *
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getEntityId()) {
            return __($this->getEntityCodeById($row->getEntityId()));
        } else {
            return __('N/A');
        }
    }

    protected function getEntityCodeById($entityId)
    {
        return $this->ratingTypeCollectionFactory->create()->getEntityCodeById($entityId);
    }
}
