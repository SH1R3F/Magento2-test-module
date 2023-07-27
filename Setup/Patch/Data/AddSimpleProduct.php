<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogSampleData\Setup\Patch\Data\InstallCatalogSampleData;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\State;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddSimpleProduct implements DataPatchInterface
{
    /**
     * @var State
     */
    protected State $appState;

    /**
     * @var ProductInterfaceFactory
     */
    protected ProductInterfaceFactory $productFactory;

    /**
     * @var EavSetup
     */
    protected EavSetup $eavSetup;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $categoryCollectionFactory;

    /**
     * @var CategoryLinkManagementInterface
     */
    protected CategoryLinkManagementInterface $categoryLink;

    public function __construct(
        State $appState,
        ProductInterfaceFactory $productFactory,
        EavSetup $eavSetup,
        ProductRepositoryInterface $productRepository,
        CollectionFactory $categoryCollectionFactory,
        CategoryLinkManagementInterface $categoryLink
    ) {
        $this->appState = $appState;
        $this->productFactory = $productFactory;
        $this->eavSetup = $eavSetup;
        $this->productRepository = $productRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryLink = $categoryLink;
    }

    public function apply(): self
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
        return $this;
    }

    public function execute(): void
    {
        /** @var Product $product */
        $product = $this->productFactory->create();

        if ($product->getIdBySku('resistance-band')) {
            return;
        }

        $product = $this->createProduct($product);

        $this->assignCategories($product);
    }

    public static function getDependencies(): array
    {
        return [InstallCatalogSampleData::class];
    }

    public function getAliases(): array
    {
        return [];
    }

    private function createProduct(Product $product): \Magento\Catalog\Api\Data\ProductInterface
    {
        $defaultAttributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');

        $product
            ->setTypeId(Product\Type::TYPE_SIMPLE)
            ->setAttributeSetId($defaultAttributeSetId)
            ->setName('Resistance band')
            ->setSku('resistance-band')
            ->setUrlKey('resistanceband')
            ->setPrice(5.99)
            ->setVisibility(Product\Visibility::VISIBILITY_BOTH)
            ->setStatus(Product\Attribute\Source\Status::STATUS_ENABLED);

        // Save product
        return $this->productRepository->save($product);
    }

    public function assignCategories(\Magento\Catalog\Api\Data\ProductInterface $product): void
    {
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryIds = $categoryCollection->addAttributeToFilter('name', ['in' => ['Men', 'Women']])->getAllIds();

        $this->categoryLink->assignProductToCategories($product->getSku(), $categoryIds);
    }
}
