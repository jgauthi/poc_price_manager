<?php
use Jgauthi\Utils\Tarif\PriceManager;

// In this example, the vendor folder is located in root poc
require_once __DIR__.'/../vendor/autoload.php';

// Example of pricing class in your project
class Pricing
{
    public const TAX_RATE_DEFAULT = 20;

    public function __construct(private string $title, private float $amountTaxesIncluded, private float $taxRate = self::TAX_RATE_DEFAULT) { }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getAmountTaxesIncluded(): float
    {
        return $this->amountTaxesIncluded;
    }

    public function setAmountTaxesIncluded(float $amountTaxesIncluded): self
    {
        $this->amountTaxesIncluded = $amountTaxesIncluded;
        return $this;
    }

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    public function setTaxRate(float $taxRate): self
    {
        $this->taxRate = $taxRate;
        return $this;
    }
}

// Adapt Price Manager for your project and add features
class PriceManagerExtended extends PriceManager
{
    // Don't impact the price
    private array $giveBackChange = [];
    private bool $lockPriceEdition = false;

    // Private the __construct to use the method init instead
    private function __construct(
        protected float   $originalPrice,
        protected ?float  $taxRate = null,
        protected ?string $description = null,
        protected string  $priceModeByDefault = self::PRICE_WITH_TAXES,
    ) {
        parent::__construct($originalPrice, $taxRate, $description, $priceModeByDefault);
    }

    static public function init(Pricing $sp): self
    {
        if (!empty($sp->getTaxRate())) {
            return new self($sp->getAmountTaxesIncluded(), $sp->getTaxRate(), $sp->getTitle(), self::PRICE_WITH_TAXES);
        }

        return new self($sp->getAmountTaxesIncluded(), description: $sp->getTitle());
    }

    public function setLockPrice(): static
    {
        $this->lockPriceEdition = true;

        return $this;
    }

    public function setNewPrice(float $newPrice, ?string $comment = null): static
    {
        if ($this->lockPriceEdition) {
            return $this;
        }

        $this->description .= ', replaced price';
        if (!empty($comment)) {
            $this->description .= ': ' . $comment;
        }

        return parent::setNewPrice($newPrice);
    }

    public function addGiveBackChange(string $title, float $value): static
    {
        $this->giveBackChange[] = ['title' => $title, 'value' => abs($value)];

        return $this;
    }

    public function getGiveBackChange(): float
    {
        return array_sum(array_column($this->giveBackChange, 'value'));
    }

    public function addDiscountPourcent(float $pourcent): static
    {
        $this->addDiscount($pourcent.'%', $this->getPrice() * ($pourcent / 100));
        return $this;
    }

    // Add some information to description
    public function getDescription(): string
    {
        $description = (string)parent::getDescription();
        $completeDescription = [];
        if (!empty($this->discounts)) {
            $text = [];
            foreach ($this->discounts as ['title' => $title, 'value' => $value]) {
                $text[] = "{$title} (-{$value}€)";
            }
            $completeDescription[] = 'with discount: '.implode(', ', $text);
        }

        if (!empty($this->supplements)) {
            $text = [];
            foreach ($this->supplements as ['title' => $title, 'value' => $value]) {
                $text[] = "{$title} (+{$value}€)";
            }
            $completeDescription[] = 'with supplements: '.implode(', ', $text);
        }

        if (!empty($this->giveBackChange)) {
            $text = [];
            foreach ($this->giveBackChange as ['title' => $title, 'value' => $value]) {
                $text[] = "{$title} (+{$value}€)";
            }
            $completeDescription[] = 'with giveBackChange: '.implode(', ', $text);
        }

        return $description . ', ' . implode(', ', $completeDescription);
    }


    public function getPriceCalculated(bool $priceWithTaxes = true): float
    {
        $price = parent::getPriceCalculated($priceWithTaxes);
        if ($price < 0) {
            $this->addGiveBackChange('Negative price', $price);
            $price = 0;
        }

        return $price;
    }
}



?>
<h3>Example of specific feature</h3>
<p>Custom PriceManager for your project.</p>

<?php
$projectPrice = new Pricing('Standard', 15, Pricing::TAX_RATE_DEFAULT);
$price = PriceManagerExtended::init($projectPrice);

// Price locked, you can't change it directly
$price->setLockPrice();
$price->setNewPrice(12); // no work

// Discounts is OK
$price->addDiscountPourcent(10);
$price->addDiscountPourcent(15);

// Return back changes, don't change the price
$price->addGiveBackChange('returned money', 2);

?>
<p>
    Original price: <?=$price->getPrice()?> (taxRate: <?=$price->getTaxRate()?>),<br>
    Price Calculated: <?=$price->getPriceCalculated()?><br>
    <br>
    Price info: <?=nl2br($price->getDescription())?>
</p>

<p><a href="./index.php">Classic usage</a></p>
