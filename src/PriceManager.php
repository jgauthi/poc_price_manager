<?php
/******************************************************************************************
 * @name: Price Manager
 * @note: Framework to manage the calculation of a price according to different parameters
 * @author: Jgauthi, created at [28oct2022], url: <github.com/jgauthi/poc_price_manager>
 * @version: 3.0
 * @Requirements:
    - PHP version >= 8.1 (http://php.net)

 ******************************************************************************************/
namespace Jgauthi\Utils\Tarif;

class PriceManager
{
    public const PRICE_NO_TAXES = 'no taxes';
    public const PRICE_WITH_TAXES = 'with taxes';

    protected ?float $newPrice = null;
    protected ?float $priceCalculated = null;
    protected array $discounts = [];
    protected array $supplements = [];
    protected bool $replacePrice = true;
    protected bool $applyDiscount = true;
    protected bool $applySupplement = true;
    protected bool $allowFreePrice = true;

    public function __construct(
        protected float   $originalPrice,
        protected ?float  $taxRate = null,
        protected ?string $description = null,
        protected string  $priceModeByDefault = self::PRICE_NO_TAXES,
    ) { }

    public function getOriginalPrice(): float
    {
        return $this->originalPrice;
    }

    public function setNewPrice(float $newPrice): static
    {
        $this->priceCalculated = null;
        $this->newPrice = $newPrice;

        return $this;
    }

    public function getDiscounts(): array
    {
        return $this->discounts;
    }

    public function addDiscount(string $title, float $value): static
    {
        $this->discounts[] = ['title' => $title, 'value' => $value];
        $this->priceCalculated = null;

        return $this;
    }

    public function getSupplements(): array
    {
        return $this->supplements;
    }

    public function addSupplement(string $title, float $value): static
    {
        $this->supplements[] = ['title' => $title, 'value' => $value];
        $this->priceCalculated = null;

        return $this;
    }

    public function getTaxRate(): ?float
    {
        return $this->taxRate;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPriceModeByDefault(): string
    {
        return $this->priceModeByDefault;
    }

    public function isReplacePrice(): bool
    {
        return $this->replacePrice;
    }

    public function setReplacePrice(bool $replacePrice): static
    {
        $this->replacePrice = $replacePrice;
        $this->priceCalculated = null;

        return $this;
    }

    public function isApplyDiscount(): bool
    {
        return $this->applyDiscount;
    }

    public function setApplyDiscount(bool $applyDiscount): static
    {
        $this->applyDiscount = $applyDiscount;
        $this->priceCalculated = null;

        return $this;
    }

    public function isApplySupplement(): bool
    {
        return $this->applySupplement;
    }

    public function setApplySupplement(bool $applySupplement): static
    {
        $this->applySupplement = $applySupplement;
        $this->priceCalculated = null;

        return $this;
    }

    public function isAllowFreePrice(): bool
    {
        return $this->allowFreePrice;
    }

    public function setAllowFreePrice(bool $allowFreePrice): static
    {
        $this->allowFreePrice = $allowFreePrice;

        return $this;
    }

    private function convertPriceWithTaxOrNot(float $price, bool $priceWithTaxes = true): float
    {
        if ($this->taxRate === null) {
            return $price;
        } elseif ($priceWithTaxes && $this->priceModeByDefault === self::PRICE_NO_TAXES) { // Convert WithTaxes => NoTaxes
            $price *= (1 + ($this->taxRate / 100));
        } elseif (!$priceWithTaxes && $this->priceModeByDefault === self::PRICE_WITH_TAXES) { // Convert NoTaxes => WithTaxes
            $price /= (1 + ($this->taxRate / 100));
        }

        return $price;
    }

    /**
     * Get price (original or new) without Supplement/Discount
     */
    public function getPrice(?bool $priceWithTaxes = null): float
    {
        $price = ($this->isReplacePrice() && $this->newPrice !== null)
            ? $this->newPrice
            : $this->originalPrice
        ;

        if (is_null($priceWithTaxes)) { // Default mode
            return $price;
        }

        return $this->convertPriceWithTaxOrNot($price, $priceWithTaxes);
    }

    /**
     * @throws \Exception
     */
    public function getPriceCalculated(bool $priceWithTaxes = true): float
    {
        if ($this->priceCalculated !== null) {
            return round($this->convertPriceWithTaxOrNot($this->priceCalculated, $priceWithTaxes), 2);
        }

        $price = $this->getPrice();
        if ($this->isApplySupplement() && !empty($this->supplements)) {
            foreach ($this->supplements as ['value' => $value]) {
                $price += $value;
            }
        }

        if ($this->isApplyDiscount() && !empty($this->discounts)) {
            foreach ($this->discounts as ['value' => $value]) {
                $price -= $value;
            }
        }

        if (!$this->isAllowFreePrice() && empty($price)) {
            throw new \Exception('Free price is not allowed.');
        }

        $this->priceCalculated = $price;
        return round($this->convertPriceWithTaxOrNot($this->priceCalculated, $priceWithTaxes), 2);
    }
}