<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Log;

class Logger implements LoggerInterface
{
    private $logger;

    public function __construct()
    {
        $this->_construct();
    }
    
    /**
     * Internal constructor, that is called from real constructor
     *
     * Please override this one instead of overriding real __construct constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/marketplace_logs.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
    }

    /**
     * Will print logs in a custom file for marketplace.
     *
     * @param string $label
     * @param mixed $param
     */
    public function debug($label = '', $param = '')
    {
        $this->logger->debug($label.' : ', [$param]);
    }
    
    /**
     * Will print logs in a custom file for marketplace.
     *
     * @param string $label
     * @param mixed $param
     */
    public function info($label = '', $param = '')
    {
        $this->logger->info($label.' : ', [$param]);
    }
}
