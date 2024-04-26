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
namespace Magedelight\Backend\Model\View\Result;

use Magedelight\Backend\App\AbstractAction;
use Magedelight\Vendor\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;

class Forward extends \Magento\Framework\Controller\Result\Forward
{
    /**
     * @var \Magedelight\Vendor\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $actionFlag;

    /**
     * @param RequestInterface $request
     * @param Session $session
     * @param ActionFlag $actionFlag
     */
    public function __construct(RequestInterface $request, Session $session, ActionFlag $actionFlag)
    {
        $this->session = $session;
        $this->actionFlag = $actionFlag;
        parent::__construct($request);
    }

    /**
     * @param string $action
     * @return $this
     */
    public function forward($action)
    {
        $this->session->setIsUrlNotice($this->actionFlag->get('', AbstractAction::FLAG_IS_URLS_CHECKED));
        return parent::forward($action);
    }
}
