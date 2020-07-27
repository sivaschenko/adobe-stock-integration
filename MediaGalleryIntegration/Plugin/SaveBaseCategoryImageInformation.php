<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryIntegration\Plugin;

use Magento\Catalog\Model\ImageUploader;
use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface;
use Magento\MediaGalleryApi\Api\GetAssetsByPathsInterface;
use Magento\MediaGalleryApi\Api\SaveAssetsInterface;
use Magento\MediaGallerySynchronizationApi\Api\SynchronizeFilesInterface;
use Magento\MediaGalleryUiApi\Api\ConfigInterface;

/**
 * Save base category image by SaveAssetsInterface.
 */
class SaveBaseCategoryImageInformation
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var DeleteAssetsByPathsInterface
     */
    private $deleteAssetsByPaths;

    /**
     * @var GetAssetsByPathsInterface
     */
    private $getAssetsByPaths;

    /**
     * @var Storage
     */
    private $storage;
    
    /**
     * @var SynchronizeFilesInterface
     */
    private $synchronizeFiles;

    /**
     * @param DeleteAssetsByPathsInterface $deleteAssetsByPath
     * @param GetAssetsByPathsInterface $getAssetsByPaths
     * @param Storage $storage
     * @param SynchronizeFilesInterface $synchronizeFiles
     * @param ConfigInterface $config
     */
    public function __construct(
        DeleteAssetsByPathsInterface $deleteAssetsByPath,
        GetAssetsByPathsInterface $getAssetsByPaths,
        Storage $storage,
        SynchronizeFilesInterface $synchronizeFiles,
        ConfigInterface $config
    ) {
        $this->deleteAssetsByPaths = $deleteAssetsByPath;
        $this->getAssetsByPaths = $getAssetsByPaths;
        $this->storage = $storage;
        $this->synchronizeFiles = $synchronizeFiles;
        $this->config = $config;
    }

    /**
     * Saves base category image information after moving from tmp folder.
     *
     * @param ImageUploader $subject
     * @param string $imagePath
     */
    public function afterMoveFileFromTmp(ImageUploader $subject, string $imagePath): string
    {
        if (!$this->config->isEnabled()) {
            return $result;
        }
        
        $absolutePath = $this->storage->getCmsWysiwygImages()->getStorageRoot() . $imagePath;
        $tmpPath = $subject->getBaseTmpPath() . '/' . substr(strrchr($imagePath, "/"), 1);
        $tmpAssets = $this->getAssetsByPaths->execute([$tmpPath]);

        if (!empty($tmpAssets)) {
            $this->deleteAssetsByPaths->execute([$tmpAssets[0]->getPath()]);
        }

        $this->synchronizeFiles->execute([$absolutePath]);

        return $imagePath;
    }
}
