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
namespace Magedelight\ProductAlert\Plugins;

class Unsubscribe
{
    protected $_request;

    protected $_urlHash;

    protected $_alertProviderCollection;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magedelight\ProductAlert\Model\UrlHash $urlHash,
        \Magedelight\ProductAlert\Model\ResourceModel\Unsubscribe\AlertProvider $alertProvider
    ) {
        $this->_request = $request;
        $this->_urlHash = $urlHash;
        $this->_alertProviderCollection = $alertProvider;
    }


    public function afterDispatch($subject, $result)
    {
        //remove alerts for guests
        if (!$this->_urlHash->check($this->_request)) {
            return $result;
        }

        $productId = $this->_request->getParam('product_id');
        $email = $this->_request->getParam('email');
        $type = $this->_request->getParam('type');

        $collection = $this->_alertProviderCollection->getAlertModel($type, $productId, $email);

        foreach ($collection as $alert) {
            $alert->delete();
        }
    }
}
