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
declare(strict_types=1);

namespace Magedelight\Backend\Plugin\ViewModel\Template\Preview;

use Magento\Framework\App\RequestInterface;

/**
 * Class Form
 */
class AddPreviewTemplateCode
{

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Gets the fields to be included in the email preview form.
     *
     * @return array
     * @throws LocalizedException
     */
    public function afterGetFormFields(\Magento\Email\ViewModel\Template\Preview\Form $subject, $result)
    {
        $result['preview_template_code'] = $this->request->getParam('preview_template_code');
        return $result;
    }
}
