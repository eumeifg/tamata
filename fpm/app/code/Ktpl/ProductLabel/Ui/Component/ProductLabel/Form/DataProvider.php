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

namespace Ktpl\ProductLabel\Ui\Component\ProductLabel\Form;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Ktpl\ProductLabel\Model\ResourceModel\ProductLabel\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    protected $fileInfo;

    private $modifierPool;

    private $dataPersistor;

    protected $request;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\App\RequestInterface $request,
        \Ktpl\ProductLabel\Model\ImageLabel\FileInfo $fileInfo,
        \Magento\Ui\DataProvider\Modifier\PoolInterface $modifierPool,
        array $meta = [],
        array $data = []
    ) {
        $this->collection    = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->modifierPool  = $modifierPool;
        $this->fileInfo      = $fileInfo;
        $this->request       = $request;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        $requestId = $this->request->getParam($this->requestFieldName);
        $productLabel = $this->collection->addFieldToFilter($this->requestFieldName, $requestId)->getFirstItem();

        if ($productLabel->getId()) {
            $data                               = $this->convertValues($productLabel, $productLabel->getData());
            $this->data[$productLabel->getId()] = $data;
        }

        foreach ($this->modifierPool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        }

        return $this->data;
    }

    public function getMeta()
    {
        $this->meta = parent::getMeta();
        foreach ($this->modifierPool->getModifiersInstances() as $modifier) {
            $this->meta = $modifier->modifyMeta($this->meta);
        }

        return $this->meta;
    }

    private function convertValues($productLabel, $data)
    {
        $fileName = $productLabel->getData('image');

        if ($fileName && $this->fileInfo->isExist($fileName)) {
            $stat = $this->fileInfo->getStat($fileName);
            $mime = $this->fileInfo->getMimeType($fileName);

            unset($data['image']);
            $data['image'][0]['name'] = basename($fileName);

            $data['image'][0]['url'] = $productLabel->getImageUrl();
            if ($this->fileInfo->isBeginsWithMediaDirectoryPath($fileName)) {
                $data['image'][0]['url'] = $fileName;
            }

            $data['image'][0]['size'] = isset($stat) ? $stat['size'] : 0;
            $data['image'][0]['type'] = $mime;
        }

        return $data;
    }
}
