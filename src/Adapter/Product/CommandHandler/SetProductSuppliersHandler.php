<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\Product\CommandHandler;

use PrestaShop\PrestaShop\Adapter\Product\Update\ProductSupplierUpdater;
use PrestaShop\PrestaShop\Core\Domain\Product\Combination\ValueObject\CombinationId;
use PrestaShop\PrestaShop\Core\Domain\Product\Supplier\Command\SetProductSuppliersCommand;
use PrestaShop\PrestaShop\Core\Domain\Product\Supplier\CommandHandler\SetProductSuppliersHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\Product\Supplier\ProductSupplier as ProductSupplierDTO;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use ProductSupplier;

/**
 * Handles @see SetProductSuppliersCommand using legacy object model
 */
final class SetProductSuppliersHandler implements SetProductSuppliersHandlerInterface
{
    /**
     * @var ProductSupplierUpdater
     */
    private $productSupplierUpdater;

    /**
     * @param ProductSupplierUpdater $productSupplierUpdater
     */
    public function __construct(
        ProductSupplierUpdater $productSupplierUpdater
    ) {
        $this->productSupplierUpdater = $productSupplierUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(SetProductSuppliersCommand $command): array
    {
        $productId = $command->getProductId();
        $productSuppliers = [];

        foreach ($command->getProductSuppliers() as $productSupplierDTO) {
            $productSuppliers[] = $this->buildEntityFromDTO($productId, $productSupplierDTO);
        }

        return $this->productSupplierUpdater->setProductSuppliers(
            $productId,
            $command->getDefaultSupplierId(),
            $productSuppliers
        );
    }

    /**
     * @param ProductId $productId
     * @param ProductSupplierDTO $productSupplierDTO
     *
     * @return ProductSupplier
     */
    private function buildEntityFromDTO(ProductId $productId, ProductSupplierDTO $productSupplierDTO): ProductSupplier
    {
        $id = $productSupplierDTO->getProductSupplierId() ? $productSupplierDTO->getProductSupplierId()->getValue() : null;

        $productSupplier = new ProductSupplier();
        $productSupplier->id = $id;
        $productSupplier->id_product = $productId->getValue();
        $productSupplier->id_product_attribute = CombinationId::NO_COMBINATION;
        $productSupplier->id_supplier = $productSupplierDTO->getSupplierId()->getValue();
        $productSupplier->id_currency = $productSupplierDTO->getCurrencyId()->getValue();
        $productSupplier->product_supplier_reference = $productSupplierDTO->getReference();
        $productSupplier->product_supplier_price_te = $productSupplierDTO->getPriceTaxExcluded();

        return $productSupplier;
    }
}
