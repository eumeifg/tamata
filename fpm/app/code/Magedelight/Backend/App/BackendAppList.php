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
namespace Magedelight\Backend\App;

/**
 * List of Backend Applications to allow injection of them through the DI
 */
class BackendAppList
{
    /**
     * @var BackendApp[]
     */
    private $backendApps = [];

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $backendApps
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        array $backendApps = []
    ) {
        $this->backendApps = $backendApps;
        $this->request = $request;
    }

    /**
     * Get Backend app based on its name
     *
     * @return BackendApp|null
     */
    public function getCurrentApp()
    {
        $appName = $this->request->getQuery('app');
        if ($appName && isset($this->backendApps[$appName])) {
            return $this->backendApps[$appName];
        }
        return null;
    }

    /**
     * Retrieve backend application by name
     *
     * @param string $appName
     * @return BackendApp|null
     */
    public function getBackendApp($appName)
    {
        if (isset($this->backendApps[$appName])) {
            return $this->backendApps[$appName];
        }
        return null;
    }
}
