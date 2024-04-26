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
namespace Magedelight\Vendor\Block\Adminhtml\Microsite\Request\Edit;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;

/**
 * Description of Form
 *
 * @author Rocket Bazaar Core Team
 */
class Form extends GenericForm
{
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
       
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                    'data' => [
                        'id' => 'edit_form',
                        'action' => $this->getUrl('*/*/save'),
                        'method' => 'post',
                        'enctype' => 'multipart/form-data'
                    ]
                ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}