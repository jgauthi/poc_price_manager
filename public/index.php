<?php
use Jgauthi\Utils\Tarif\CalculTarif;

// In this example, the vendor folder is located in root poc
require_once __DIR__.'/../vendor/autoload.php';

// Different calculations
$expression = [
    ['tarif' => 25.4, 	'qte' => 1, 'promo' => null],
    ['tarif' => 50.21,	'qte' => 4, 'promo' => 10],
    ['tarif' => 28.69,	'qte' => 3, 'promo' => null, 'remise' => 15.52],
    ['tarif' => 60, 	'qte' => 8, 'promo' => 15,	'promo_debut' => 4],
    ['tarif' => 200,	'qte' => 6, 'remise' => 60, 'promo' => [
        ['%' => 10],
        ['%' => 15, 'debut' => 3],
    ]],
    ['tarif' => 142, 'qte' => 18, 'remise' => 123, 'promo' => [
        ['%' => 10],
        ['%' => 15,	'debut' => 3],
        ['%' => 10,	'debut' => 6],
        ['%' => 8, 	'debut' => 9],
        ['%' => 9, 	'debut' => 12],
        ['%' => 13,	'debut' => 15],
        ['%' => 35,	'debut' => 18],
    ]],
    ['tarif' => 49.99, 'qte' => 4, 'promo' => 11, 'tva' => 19.6],
];

$result = [];
foreach ($expression as $exp) {
    $affichage = ['promo' => 'N/A', 'debut' => 'N/A'];

    // Setting up rules for the calculation
    $calc = new CalculTarif($exp['tarif'], $exp['qte']);
    $calc->setFormat(',', ' ', null, null);

    if (!empty($exp['promo'])) {
        // Several Promotions
        if (is_array($exp['promo'])) {
            $affichage = ['promo' => [], 'debut' => []];

            foreach ($exp['promo'] as $promo) {
                if (!isset($promo['%'])) {
                    continue;
                }
                if (!isset($promo['debut'])) {
                    $promo['debut'] = 1;
                }

                $affichage['promo'][] = $promo['%'];
                $affichage['debut'][] = $promo['debut'];

                $calc->addPromotion($promo['%'], $promo['debut']);
            }

            $affichage = [
                'promo' => implode('+', $affichage['promo']),
                'debut' => implode('+', $affichage['debut']),
            ];

        // One only promo
        } elseif (!empty($exp['promo'])) {
            $affichage['promo'] = $exp['promo'];
            if (!empty($exp['promo_debut'])) {
                $affichage['debut'] = $exp['promo_debut'];
                $calc->addPromotion($exp['promo'], $exp['promo_debut']);
            } else {
                $calc->addPromotion($exp['promo']);
            }
        }
    }

    if (!empty($exp['remise'])) {
        $calc->setDiscount($exp['remise']);
    }

    // Tax management
    if (!empty($exp['tva'])) {
        $calc->setTax($exp['tva']);
        $affichage['tva'] = $calc->format($exp['tva']);

        $exp['tarif_ttc'] = $exp['tarif'] * (1 + ($exp['tva'] / 100));
    } else {
        $exp['tarif_ttc'] = $exp['tarif']; // Pas de taxe
        $affichage['tva'] = 'N/A';
    }

    // Amount calculation with / without reduction
    $exp['total'] = $calc->total();
    $exp['total_ht'] = $calc->total(false);

    $result[] = [
        'Tarif HT' => $calc->format($exp['tarif']),
        'Quantité' => $exp['qte'],
        'Promotion (%)' => $affichage['promo'],
        'Promotion début' => $affichage['debut'],
        'Remise' => ((!empty($exp['remise'])) ? $exp['remise'] : 'N/A'),
        'TVA' => $affichage['tva'],
        'Résultat' => $calc->format($exp['total']),
        'Au lieu de' => $calc->format($exp['total_ht']),
        'Différence' => $calc->format(($exp['tarif_ttc'] * $exp['qte']) - $exp['total']),
        '% applique' => $calc->format($calc->percentageApplied($exp['total'])).'%',
    ];
}

?>
<h1>Calcul tarif</h1>
<table class="table table-striped table-hover table-bordered" border="1">
    <thead class="thead-dark">
    <tr>
        <th scope="col">Tarif HT</th>
        <th scope="col">Quantité</th>
        <th scope="col">Promotion (%)</th>
        <th scope="col">Promotion début</th>
        <th scope="col">Remise</th>
        <th scope="col">TVA</th>
        <th scope="col">Résultat</th>
        <th scope="col">Au lieu de</th>
        <th scope="col">Différence</th>
        <th scope="col">% applique</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($result as $data): ?>
    <tr class="tr_0">
        <td class="td_Tarif HT"><?=$data['Tarif HT']?></td>
        <td class="td_Quantité"><?=$data['Quantité']?></td>
        <td class="td_Promotion (%)"><?=$data['Promotion (%)']?></td>
        <td class="td_Promotion début"><?=$data['Promotion début']?></td>
        <td class="td_Remise"><?=$data['Remise']?></td>
        <td class="td_TVA"><?=$data['TVA']?></td>
        <td class="td_Résultat"><?=$data['Résultat']?></td>
        <td class="td_Au lieu de"><?=$data['Au lieu de']?></td>
        <td class="td_Différence"><?=$data['Différence']?></td>
        <td class="td_% applique"><?=$data['% applique']?></td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>

<br><br>
<?php var_dump($result); ?>


<style>
    th, td                              { padding: 5px; }
    th:nth-child(7), td:nth-child(7) 	{ background-color: #F25769; color: white; }
    th:nth-child(8), td:nth-child(8) 	{ background-color: #669999; color: white; }
    th:nth-child(9), td:nth-child(9) 	{ background-color: #000099; color: white; }
    th:nth-child(10), td:nth-child(10) 	{ background-color: #663333; color: white; }
</style>