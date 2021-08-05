<?php

namespace Salecto\MediaStorageSync\Plugin;

use Magento\MediaStorage\Model\File\Storage\Database as StorageDatabase;
use Salecto\MediaStorageSync\Model\Config;
use Salecto\MediaStorageSync\Model\Sync;
use Salecto\MediaStorageSync\Helper\Data as Helper;


class Synchronization
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Sync
     */
    protected $sync;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Synchronization constructor.
     * @param Config $config
     * @param Sync $sync
     * @param Helper $helper
     */
    public function __construct(
        Config $config,
        Sync $sync,
        Helper $helper
    ) {
        $this->config = $config;
        $this->sync = $sync;
        $this->helper = $helper;
    }

    /**
     * @param StorageDatabase $subject
     * @param callable $proceed
     * @param string $filePath
     * @return StorageDatabase
     */
    public function aroundLoadByFilename(StorageDatabase $subject, callable $proceed, $filePath)
    {
        if ($this->config->isEnabled()) {
            try {
                if ($this->sync->sync($filePath, $this->helper->getMediaBaseDir())) {
                    $subject->setId(1);
                    $subject->setContent($this->sync->getHttpClient()->getBody());
                }
                return $subject;
            } catch (\Exception $e) {
                // do nothing
            }
        }
        return $proceed($filePath);
    }
}