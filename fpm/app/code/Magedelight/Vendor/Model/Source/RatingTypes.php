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
namespace Magedelight\Vendor\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Magedelight\Vendor\Model\ResourceModel\RatingTypes\CollectionFactory as RatingTypeCollectionFactory;

class RatingTypes implements ArrayInterface
{

    /**
     * @var RatingTypeCollectionFactory
     */
    protected $ratingTypeCollectionFactory;

    /**
     * @param RatingTypeCollectionFactory $ratingTypeCollectionFactory
     */
    public function __construct(
        RatingTypeCollectionFactory $ratingTypeCollectionFactory
    ) {
        $this->ratingTypeCollectionFactory = $ratingTypeCollectionFactory;
    }

    /**
     * get options
     *
     * @return array
     */
    public function toOptionArray()
    {

        $ratingTypeCollection = $this->ratingTypeCollectionFactory->create();

        $_options [] = [ 'value' => '', 'label' => __('--Please Select Type--')];

        foreach ($ratingTypeCollection as $type) {
            $_options [] = [ 'value' => $type->getEntityId(), 'label' => __($type->getEntityCode())];
        }

        return $_options;
    }
}
