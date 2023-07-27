<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogSampleData\Setup\Patch\Data\InstallCatalogSampleData;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
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

    /**
     * @return void
     * @throws \Exception
     */
    public function apply(): void
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    /**
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws StateException
     */
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

    /**
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [InstallCatalogSampleData::class];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @param Product $product
     * @return ProductInterface
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws StateException
     */
    protected function createProduct(Product $product): ProductInterface
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

        // TODO: Set Product Quantity

        // Save product
        return $this->productRepository->save($product);
    }

    /**
     * @param ProductInterface $product
     * @return void
     * @throws LocalizedException
     */
    public function assignCategories(ProductInterface $product): void
    {
        $categoryTitles = ['Men', 'Women'];
        $categoryIds = $this->categoryCollectionFactory->create()
            ->addAttributeToFilter('name', ['in' => $categoryTitles])
            ->getAllIds();

        $this->categoryLink->assignProductToCategories($product->getSku(), $categoryIds);
    }
}
