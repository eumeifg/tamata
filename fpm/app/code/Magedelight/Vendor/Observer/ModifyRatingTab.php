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
namespace Magedelight\Vendor\Observer;

use Magento\Framework\Event\ObserverInterface;

class ModifyRatingTab implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magedelight\Vendor\Model\Source\RatingTypes
     */
    protected $ratingTypes;

    const RATING_EDIT_FORM = \Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form\Interceptor::class;

    /**
     * @param \Magedelight\Vendor\Model\Source\RatingTypes $ratingTypes
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magedelight\Vendor\Model\Source\RatingTypes $ratingTypes,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->ratingTypes = $ratingTypes;
        $this->coreRegistry = $coreRegistry;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        if (self::RATING_EDIT_FORM == get_class($observer->getData('block'))) {
            /** @var \Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form $block */
            $block = $observer->getData('block');
            /** @var \Magento\Framework\Data\Form $form */
            $form = $block->getForm();
            /** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
            $fieldset = $form->getElement('rating_form');

            $entityId = '';
            if ($this->coreRegistry->registry('rating_data')) {
                $entityId = $this->coreRegistry->registry('rating_data')->getEntityId();
                ;
            }
            $fieldset->addField(
                'entity_id',
                'select',
                [
                    'name' => 'entity_id',
                    'label' => __('Rating Type'),
                    'title' => __('Rating Type'),
                    'values' => $this->ratingTypes->toOptionArray(),
                    'value' => $entityId,
                    'required' => true,
                ]
            );
        }
    }
}
