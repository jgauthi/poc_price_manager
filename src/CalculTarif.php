<?php
/*******************************************************************************
 * @name: Calcul Tarif
 * @note: Mini Framework pour gérer les calculs de prix, avec promo et remise
 * @author: Jgauthi <github.com/jgauthi>, crée le [30avril2015]
 * @version: 2.0
 *******************************************************************************/

namespace Jgauthi\Utils\Tarif;

use InvalidArgumentException;

class CalculTarif
{
    public $tarif;
    public $qte;
    protected $tva;
    protected $promo;
    protected $remise;
    protected $format = ['sep' => ',', 'mille' => ' ', 'start' => null, 'end' => null];

    /**
     * calcul_tarif constructor.
     *
     * @param int $tarif_unitaire
     * @param int $qte
     */
    public function __construct(int $tarif_unitaire, int $qte = 1)
    {
        $this->tarif = ((is_numeric($tarif_unitaire)) ? $tarif_unitaire : 0);

        if (is_numeric($qte) && $qte > 1) {
            $this->qte = $qte;
        } else {
            $this->qte = 1;
        }
    }

    /**
     * @param int|null $pourcent
     * @return self
     */
    public function tva(?int $pourcent = null): self
    {
        if (null === $pourcent) {
            return $this->tva;
        } elseif (!is_numeric($pourcent) || $pourcent >= 100 || $pourcent < 0) {
            throw new InvalidArgumentException("$pourcent is not a correct integer%");
        }

        $this->tva = $pourcent;

        return $this;
    }

    /**
     * @param int $int
     *
     * @return string
     */
    public function format(int $int): string
    {
        return $this->format['start'].
            number_format(
                $int,
                2,
                $this->format['sep'],
                $this->format['mille']
            ).
            $this->format['end'];
    }

    /**
     * @param int $sep
     * @param int $mille
     * @param int $start
     * @param int $end
     * @return self
     */
    public function set_format(int $sep = -1, int $mille = -1, int $start = -1, int $end = -1): self
    {
        if (-1 !== $sep) {
            $this->format['sep'] = $sep;
        }
        if (-1 !== $mille) {
            $this->format['mille'] = $mille;
        }
        if (-1 !== $start) {
            $this->format['start'] = $start;
        }
        if (-1 !== $end) {
            $this->format['end'] = $end;
        }

        return $this;
    }

    //-- Gestion des réductions ------------------------------------------------

    /**
     * @param int $pourcent
     * @param int $debut_promo
     *
     * @return self
     */
    public function add_promo(int $pourcent, int $debut_promo = 1): self
    {
        if (!is_numeric($pourcent) || $pourcent < 0 || $pourcent > 100) {
            throw new InvalidArgumentException("Invalid pourcent args: {$pourcent}");
        } elseif ($debut_promo > $this->qte) {
            throw new InvalidArgumentException("Invalid debut_promo args: {$debut_promo}");
        }

        $this->promo[] = ['pourcent' => $pourcent, 'debut' => $debut_promo];

        return $this;
    }

    /**
     * @param int $remise
     *
     * @return self
     */
    public function remise(int $remise)
    {
        if (null === $remise) {
            return $this->remise;
        } elseif (!is_numeric($remise) || $remise < 0 || $remise >= $this->tarif) {
            throw new InvalidArgumentException("Invalid remise args: {$remise}");
        }

        $this->remise = $remise;

        return $this;
    }

    //-- Fonctions de calcul ---------------------------------------------------

    /**
     * @param bool $reduction
     *
     * @return float
     */
    public function total(bool $reduction = true): float
    {
        $total = 0;

        if (!empty($this->promo) && $reduction) {
            for ($i = 1; $i <= $this->qte; ++$i) {
                // Calcul d'un coefficient multiplicateur
                //	http://www.capte-les-maths.com/pourcentage/les_pourcentages_p11_exos.php?exo=4
                $coef_multi = 1;
                foreach ($this->promo as $pr) {
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

        if (!empty($this->remise) && $reduction) {
            $total -= $this->remise;
        }

        // Tax (tva)
        if (!empty($this->tva)) {
            $total *= (1 + ($this->tva / 100));
        }

        return $total;
    }

    /**
     * Calculer la réduction appliquer à un montant.
     *
     * @param $total
     *
     * @return float|null
     */
    public function pourcent_applique(int $total): ?float
    {
        // Calcul du MHR (Montant Hors Réduction)
        $montant_hr = $this->tarif * $this->qte;

        // Retirer la TVA du montant
        if (!empty($this->tva)) {
            $total *= 1 - ($this->tva / 100);
        }

        // Calculer le % de réduction, formule: ( (MHR - Montant) * 100) / MHR)
        if ($montant_hr !== $total) {
            return (($montant_hr - $total) * 100) / $montant_hr;
        }

        return null;
    }
}
