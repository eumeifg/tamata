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
namespace Magedelight\ProductAlert\Model;

use Magedelight\ProductAlert\Model\Unsubscribe;

class UrlHash
{
    const SOLD = 'qprugn1234njd';

    protected $_alertProvider;

    public function __construct(
        ResourceModel\Unsubscribe\AlertProvider $alertProvider
    ) {
        $this->alertProvider = $alertProvider;
    }

    public function getHash($productId, $email)
    {
        return md5($productId . $email . self::SOLD);
    }

    public function check(\Magento\Framework\App\Request\Http $request)
    {

        $hash = $request->getParam('hash');
        $productId = $request->getParam('product_id');
        $email = $request->getParam('email');

        if (empty($hash) || empty($productId) || empty($email)) {
            return false;
        }

        $real = $this->getHash($productId, $email);
        return $hash == $real;
    }
}
