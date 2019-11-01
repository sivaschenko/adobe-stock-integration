<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeMediaGallery\Plugin\Product\Gallery;

use Magento\AdobeMediaGalleryApi\Model\Asset\Command\DeleteByPathInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\Processor as ProcessorSubject;
use Psr\Log\LoggerInterface;

/**
 * Ensures that metadata is removed from the database when a product image has been deleted.
 */
class Processor
{
    /**
     * @var DeleteByPathInterface
     */
    private $deleteMediaAssetByPath;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Processor constructor.
     *
     * @param DeleteByPathInterface $deleteMediaAssetByPath
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeleteByPathInterface $deleteMediaAssetByPath,
        LoggerInterface $logger
    ) {
        $this->deleteMediaAssetByPath = $deleteMediaAssetByPath;
        $this->logger = $logger;
    }

    /**
     * Remove media asset image after the product gallery image remove
     *
     * @param ProcessorSubject $subject
     * @param $result
     * @param Product $product
     * @param $file
     *
     * @return mixed
     */
    public function afterRemoveImage(ProcessorSubject $subject, $result, Product $product, $file)
    {
        if (!is_string($file)) {
            return $result;
        }

        try {
            $this->deleteMediaAssetByPath->execute($file);
        } catch (\Exception $exception) {
            $message = __('An error occurred during media asset delete at media processor: %1', $exception->getMessage());
            $this->logger->critical($message->render());
        }

        return $result;
    }
}