<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ProductAlert\Block;

class Notification extends \Magento\Framework\View\Element\Template
{
    protected $_objectManager;
    protected $_jsonEncoder;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
        $this->_jsonEncoder = $jsonEncoder;
    }

    public function getJsonConfig()
    {
        return $this->_jsonEncoder->encode(['igor' => 'korol']);
    }
}
