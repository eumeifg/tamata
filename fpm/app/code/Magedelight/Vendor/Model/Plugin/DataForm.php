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
namespace Magedelight\Vendor\Model\Plugin;

use Magento\Backend\Block\Widget\Form;

class DataForm
{
    public function aroundSetForm(Form $subject, \Closure $proceed, $form)
    {
        $proceed($form);
        $data = $subject->getData();
        if (isset($data['module_name']) && isset($data['dest_element_id']) && $data['module_name'] == 'Magento_User') {
            $subject->getForm()->addCustomAttribute('enctype', 'multipart/form-data');
        }
        return $subject;
    }
}
