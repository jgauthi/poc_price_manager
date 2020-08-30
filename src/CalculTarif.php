<?php
/*******************************************************************************
 * @name: Calcul Tarif
 * @note: Mini Framework pour gérer les calculs de prix, avec promo et remise
 * @author: Jgauthi <github.com/jgauthi>, crée le [30avril2015]
 * @version: 1.2.3
 *******************************************************************************/

namespace Jgauthi\Utils\Tarif;

class CalculTarif
{
    public $tarif;
    public $qte;
    protected $tva;
    protected $promo;
    protected $remise;
    protected $format = array('sep' => ',', 'mille' => ' ', 'start' => null, 'end' => null);

    public function __construct($tarif_unitaire, $qte = 1)
    {
        $this->tarif = ((is_numeric($tarif_unitaire)) ? $tarif_unitaire : 0);

        if(is_numeric($qte) && $qte > 1)
                $this->qte = $qte;
        else	$this->qte = 1;
    }

    public function tva($pourcent = null)
    {
        if(is_null($pourcent))
            return $this->tva;

        elseif(!is_numeric($pourcent) || $pourcent >= 100 || $pourcent < 0)
            return false;

        $this->tva = $pourcent;
    }

    public function format($int)
    {
        return $this->format['start'].
            number_format
            (
                $int,
                2,
                $this->format['sep'],
                $this->format['mille']
            ).
            $this->format['end'];
    }

    public function set_format($sep = -1, $mille = -1, $start = -1, $end = -1)
    {
        if($sep != -1)		$this->format['sep'] = $sep;
        if($mille != -1)	$this->format['mille'] = $mille;
        if($start != -1)	$this->format['start'] = $start;
        if($end != -1)		$this->format['end'] = $end;
    }


    //-- Gestion des réductions ------------------------------------------------
    public function add_promo($pourcent, $debut_promo = 1)
    {
        if(!is_numeric($pourcent) || $pourcent < 0 || $pourcent > 100 || $debut_promo > $this->qte)
            return false;

        $this->promo[] = array('pourcent' => $pourcent, 'debut' => $debut_promo);
    }

    public function remise($remise = null)
    {
        if(is_null($remise))
            return $this->remise;

        elseif(!is_numeric($remise) || $remise < 0 || $remise >= $this->tarif)
            return false;

        $this->remise = $remise;
    }


    //-- Fonctions de calcul ---------------------------------------------------
    public function total($reduction = true)
    {
        $total = 0;

        if(!empty($this->promo) && $reduction) for($i = 1; $i <= $this->qte; $i++)
        {
            // Calcul d'un coefficient multiplicateur
            //	http://www.capte-les-maths.com/pourcentage/les_pourcentages_p11_exos.php?exo=4
            $coef_multi = 1;
            foreach($this->promo as $pr)
                if($i >= $pr['debut'])
                    $coef_multi *= 1 - ($pr['pourcent'] / 100);

            // Appliquer la promotion
            $total += $this->tarif * $coef_multi;
        }
        else $total = $this->tarif * $this->qte;

        if(!empty($this->remise) && $reduction)
            $total -= $this->remise;

        // Tax (tva)
        if(!empty($this->tva))
            $total *= (1 + ($this->tva / 100));

        return $total;
    }

    // Calculer la réduction appliquer à un montant
    public function pourcent_applique($total)
    {
        // Calcul du MHR (Montant Hors Réduction)
        $montant_hr = $this->tarif * $this->qte;

        // Retirer la TVA du montant
        if(!empty($this->tva))
            $total *= 1 - ($this->tva / 100);

        // Calculer le % de réduction, formule: ( (MHR - Montant) * 100) / MHR)
        if($montant_hr != $total)
            return (($montant_hr - $total) * 100) / $montant_hr;
        else return null;
    }
}
