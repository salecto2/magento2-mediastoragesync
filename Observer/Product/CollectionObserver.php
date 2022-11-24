<?php

namespace Salecto\MediaStorageSync\Observer\Product;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Salecto\MediaStorageSync\Model\Sync;
use Salecto\MediaStorageSync\Model\Config;
use Salecto\MediaStorageSync\Helper\Data as Helper;

class CollectionObserver implements ObserverInterface
{
    protected $galleryReadHandler;
    protected $sync;
    protected $configModel;
    protected $helper;

    /**
     * ProductObserver constructor.
     * @param GalleryReadHandler $galleryReadHandler
     * @param Sync $syncModel
     * @param Config $configModel
     * @param Helper $helper
     */
    public function __construct(
        GalleryReadHandler $galleryReadHandler,
        Sync $syncModel,
        Config $configModel,
        Helper $helper
    )
    {
        $this->galleryReadHandler = $galleryReadHandler;
        $this->sync = $syncModel;
        $this->configModel = $configModel;
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->configModel->isEnabled()) {
            $collection = $observer->getCollection();
            foreach ($collection as $product) {
                $this->galleryReadHandler->execute($product);
                if ($product->getTypeId() == 'configurable') {
                    $mediaGallery = $product->getMediaGallery();
                    $mediaGalleryImages = $mediaGallery['images'] ?? [];
                    foreach ($mediaGalleryImages as $mediaGalleryImage) {
                        $file = $mediaGalleryImage['file'];
                        $fileIsNotAvailable = $this->helper->fileIsNotAvailable($file);

                        if ($fileIsNotAvailable) {
                            $this->sync->sync(
                                $this->helper->getCatalogMediaConfigPath() . $file,
                                $this->helper->getMediaBaseDir()
                            );
                        }
                    }
                } else {
                    $mediaGalleryImages = $product->getMediaGalleryImages();

                    foreach ($mediaGalleryImages as $mediaGalleryImage) {
                        $file = $mediaGalleryImage->getData('path');
                        $fileIsNotAvailable = $this->helper->fileIsNotAvailable($file);

                        if ($fileIsNotAvailable) {
                            $this->sync->sync(
                                $this->helper->getCatalogMediaConfigPath() . "/" . $mediaGalleryImage->getData('file'),
                                $this->helper->getMediaBaseDir()
                            );
                        }
                    }
                }
            }
        }
    }
}
