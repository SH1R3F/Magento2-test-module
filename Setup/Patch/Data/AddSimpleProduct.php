<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\CatalogSampleData\Setup\Patch\Data\InstallCatalogSampleData;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddSimpleProduct implements DataPatchInterface
{
    public function apply(): self
    {
        return $this;
    }
    public static function getDependencies(): array
    {
        return [InstallCatalogSampleData::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
