<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   Ktpl_ProductLabel
  * @author    Kirti Nariya <kirti.nariya@krishtechnolabs.com>
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */

namespace Ktpl\ProductLabel\Ui\Component\ProductLabel\Form\Modifier;

class System implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    private $urlBuilder;

    public function __construct(\Magento\Backend\Model\UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    public function modifyData(array $data)
    {
        $reloadParameters = [
            'popup'         => 1,
            'componentJson' => 1,
        ];

        $data['config']['reloadUrl'] = $this->urlBuilder->getUrl('*/*/reload', $reloadParameters);

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
