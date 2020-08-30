<?php
/*******************************************************************************
 * @name: Calcul Tarif
 * @note: Small Framework to manage price calculations, with promotion and discount
 * @author: Jgauthi <github.com/jgauthi>, crée le [30avril2015]
 * @version: 2.2
 *******************************************************************************/

namespace Jgauthi\Utils\Tarif;

use InvalidArgumentException;

class CalculTarif
{
    public float $tarif;
    public int $qte = 1;
    protected int $tax;
    protected array $promotion;
    protected int $discount;
    protected array $format;

    public function __construct(int $tarif_unitaire, int $qte = 1)
    {
        $this->tarif = ((is_numeric($tarif_unitaire)) ? $tarif_unitaire : 0);
        $this->setFormat();

        if ($qte > 1) {
            $this->qte = $qte;
        }
    }

    public function setFormat(string $sep = ',', string $mille = ' ', ?string $start = null, ?string $end = null): self
    {
        $this->format = [
            'sep'   => $sep,
            'mille' => $mille,
            'start' => $start,
            'end'   => $end,
        ];

        return $this;
    }

    // % tax (tva)
    public function setTax(?int $pourcent = null): self
    {
        if (null === $pourcent) {
            return $this;
        } elseif (!is_numeric($pourcent) || $pourcent >= 100 || $pourcent < 0) {
            throw new InvalidArgumentException("$pourcent is not a correct integer%");
        }

        $this->tax = $pourcent;

        return $this;
    }

    public function format(int $int): string
    {
        return $this->format['start'].
            number_format($int, 2, $this->format['sep'], $this->format['mille']).
            $this->format['end'];
    }

    //-- Reduction management ----------------------------------------------------

    public function addPromotion(int $pourcent, int $promoStart = 1): self
    {
        if ($pourcent < 0 || $pourcent > 100) {
            throw new InvalidArgumentException("Invalid pourcent args: {$pourcent}");
        } elseif ($promoStart > $this->qte) {
            throw new InvalidArgumentException("Invalid debut_promo args: {$promoStart}");
        }

        $this->promotion[] = ['pourcent' => $pourcent, 'debut' => $promoStart];

        return $this;
    }

    public function setDiscount(int $discount): self
    {
        if (null === $discount) {
            return $this;
        } elseif ($discount < 0 || $discount >= $this->tarif) {
            throw new InvalidArgumentException("Invalid remise args: {$discount}");
        }

        $this->discount = $discount;

        return $this;
    }

    //-- Fonctions de calcul ---------------------------------------------------

    public function total(bool $reduction = true): float
    {
        $total = 0;

        if (!empty($this->promotion) && $reduction) {
            for ($i = 1; $i <= $this->qte; ++$i) {
                // Calcul d'un coefficient multiplicateur
                //	http://www.capte-les-maths.com/pourcentage/les_pourcentages_p11_exos.php?exo=4
                $coef_multi = 1;
                foreach ($this->promotion as $pr) {
                    if ($i >= $pr['debut']) {
                        $coef_multi *= 1 - ($pr['pourcent'] / 100);
                    }
                }

                // Appliquer la promotion
                $total += $this->tarif * $coef_multi;
            }
        } else {
            $total = $this->tarif * $this->qte;
        }

        if (!empty($this->discount) && $reduction) {
            $total -= $this->discount;
        }

        // Tax (tva)
        if (!empty($this->tax)) {
            $total *= (1 + ($this->tax / 100));
        }

        return $total;
    }

    /**
     * Calculer la réduction appliquer à un montant.
     */
    public function percentageApplied(int $total): ?float
    {
        // Calcul du MHR (Montant Hors Réduction)
        $montant_hr = $this->tarif * $this->qte;

        // Retirer la TVA du montant
        if (!empty($this->tax)) {
            $total *= 1 - ($this->tax / 100);
        }

        // Calculer le % de réduction, formule: ( (MHR - Montant) * 100) / MHR)
        if ($montant_hr !== $total) {
            return (($montant_hr - $total) * 100) / $montant_hr;
        }

        return null;
    }
}