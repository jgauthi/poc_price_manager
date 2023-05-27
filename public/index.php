<?php
use Jgauthi\Utils\Tarif\PriceManager;

// In this example, the vendor folder is located in root poc
require_once __DIR__.'/../vendor/autoload.php';

$price = 10;
$listPrices = [
    ['description' => 'Standard price', 'taxRate' => 20],
    ['description' => 'New Price', 'taxRate' => 20, 'newPrice' => 8],
    ['description' => 'Price TTC with discount', 'taxRate' => 20, 'priceTtc' => 14.50, 'discounts' => [['title' => 'Réduction', 'value' => 2]]],
    ['description' => 'Price calculated with no taxes with supplements', 'taxRate' => null, 'supplements' => [['title' => 'Port', 'value' => 6], ['title' => 'Papier cadeau', 'value' => 1.2]]],
    ['description' => 'Price with discount+supplement', 'taxRate' => 12, 'discounts' => [['title' => 'Réduction', 'value' => 2]], 'supplements' => [['title' => 'Port', 'value' => 6]]],
    ['description' => 'Price, discount OFF', 'taxRate' => 15, 'discounts' => [['title' => 'Réduction', 'value' => 4]], 'applyDiscount' => false],
    ['description' => 'Price, supplements OFF', 'taxRate' => 15, 'supplements' => [['title' => 'Port', 'value' => 6]], 'applySupplement' => false],
];

$calculListPrices = [];
foreach ($listPrices as $info) {
    $comment = [];
    if (!empty($info['priceTtc'])) {
        $comment[] = 'Init class on price with taxes';
        $priceManager = new PriceManager(
            $info['priceTtc'],
            $info['taxRate'],
            $info['description'],
            PriceManager::PRICE_WITH_TAXES,
        );
    } else {
        $priceManager = new PriceManager($price, $info['taxRate'], $info['description']);
    }

    if ($priceManager->getTaxRate() === null) {
        $comment[] = 'No Taxes for this price';
    }

    if (!empty($info['newPrice'])) {
        $priceManager->setNewPrice($info['newPrice']);
        $comment[] = 'New Price: '.$info['newPrice'];
    }

    if (!empty($info['discounts'])) {
        if (isset($info['applyDiscount'])) {
            $priceManager->setApplyDiscount($info['applyDiscount']);
        }

        if ($priceManager->isApplyDiscount()) {
            foreach ($info['discounts'] as ['title' => $title, 'value' => $value]) {
                $priceManager->addDiscount($title, $value);
                $comment[] = sprintf('Add discount: %.2f (%s)', $value, $title);
            }
        } else {
            $comment[] = "Can't apply discount, disabled";
        }
    }

    if (!empty($info['supplements'])) {
        if (isset($info['applySupplement'])) {
            $priceManager->setApplySupplement($info['applySupplement']);
        }

        if ($priceManager->isApplySupplement()) {
            foreach ($info['supplements'] as ['title' => $title, 'value' => $value]) {
                $priceManager->addSupplement($title, $value);
                $comment[] = sprintf('Add supplement: %.2f (%s)', $value, $title);
            }
        } else {
            $comment[] = "Can't apply supplements, disabled";
        }
    }

    $calculListPrices[$priceManager->getDescription()] = [
        'Original Price' => !empty($info['priceTtc']) ? $info['priceTtc'] : $price,
        'Tax Rate' => ($priceManager->getTaxRate() !== null) ? $priceManager->getTaxRate() : 'N/A',
        'PriceNT Calculated' => $priceManager->getPriceCalculated(priceWithTaxes: false),
        'PriceWT Calculated' => $priceManager->getPriceCalculated(),
        'Comment' => implode(PHP_EOL, $comment),
    ];
}

?>
<h3>Class to manage prices</h3>
<table class="table table-striped table-hover table-bordered" border="1">
    <thead class="thead-dark">
    <tr>
        <th scope="row">Colonnes</th>
        <th scope="col">Original Price</th>
        <th scope="col">Tax Rate</th>
        <th scope="col">PriceNT Calculated</th>
        <th scope="col">PriceWT Calculated</th>
        <th scope="col">Comment</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($calculListPrices as $title => $info): ?>
    <tr>
        <th align="left" scope="row"><?=$title?></th>
        <td><?=$info['Original Price']?></td>
        <td><?=$info['Tax Rate']?></td>
        <td><?=$info['PriceNT Calculated']?></td>
        <td><?=$info['PriceWT Calculated']?></td>
        <td><?=$info['Comment']?></td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>

<dl class="row">
    <dt class="col-sm-3">NT</dt><dd class="col-sm-9">No taxes (hors taxes)</dd>
    <dt class="col-sm-3">WT</dt><dd class="col-sm-9">With taxes (TTC)</dd>
</dl>

<p><a href="./specific_feature.php">Example of specific feature</a>: custom PriceManager for your project</p>
