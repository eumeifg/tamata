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
namespace Magedelight\Vendor\Model\Microsite;

class Copier
{

    /**
     * @var \Magedelight\Vendor\Model\MicrositeFactory
     */
    protected $micrositeFactory;

    /**
     * @param \Magedelight\Vendor\Model\MicrositeFactory $micrositeFactory
     */
    public function __construct(
        \Magedelight\Vendor\Model\MicrositeFactory $micrositeFactory
    ) {
        $this->micrositeFactory = $micrositeFactory;
    }
    
    /**
     * @param \Magedelight\Vendor\Model\Microsite $microsite
     * @param integer $storeId
     * @return integer
     */
    public function copy(\Magedelight\Vendor\Model\Microsite $microsite, $storeId = 0)
    {
        /** @var \Magedelight\Vendor\Model\Microsite $duplicate */
        $duplicate = $this->micrositeFactory->create();
        
        $duplicate->setData($microsite->getData());
       
        $duplicate->setId(null);
       
        $duplicate->setStoreId($storeId);
       
        $isDuplicateSaved = false;
        
        $duplicate->save();
        
        return $duplicate->getId();
    }
}
