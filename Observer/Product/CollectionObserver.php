<?php

namespace Salecto\MediaStorageSync\Observer\Product;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Salecto\MediaStorageSync\Model\Sync;
use Salecto\MediaStorageSync\Model\Config;
use Salecto\MediaStorageSync\Helper\Data as Helper;
use Magento\Catalog\Api\ProductRepositoryInterface;

class CollectionObserver implements ObserverInterface
{
    protected $galleryReadHandler;
    protected $sync;
    protected $configModel;
    protected $helper;
    protected $productRepository;

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
        Helper $helper,
        ProductRepositoryInterface $productRepository
    ) {
        $this->galleryReadHandler = $galleryReadHandler;
        $this->sync = $syncModel;
        $this->configModel = $configModel;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->configModel->isEnabled()) {
            $collection = $observer->getCollection();
            foreach ($collection as $product) {
                $product = $this->productRepository->getById($product->getId());

                $this->galleryReadHandler->execute($product);

                $mediaGalleryImages = $product->getMediaGalleryImages();

                foreach ($mediaGalleryImages as $mediaGalleryImage) {
                    $file = $mediaGalleryImage->getData("path");
                    $fileIsNotAvailable = $this->helper->fileIsNotAvailable(
                        $file
                    );

                    if ($fileIsNotAvailable) {
                        $this->sync->sync(
                            $this->helper->getCatalogMediaConfigPath() .
                                "/" .
                                $mediaGalleryImage->getData("file"),
                            $this->helper->getMediaBaseDir()
                        );
                    }
                }
            }
        }
    }
}
