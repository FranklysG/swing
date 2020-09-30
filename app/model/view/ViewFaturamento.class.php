<?php
/**
 * ViewFaturamento Active Record
 * @author  <your-name-here>
 */
class ViewFaturamento extends TRecord
{
    const TABLENAME = 'view_faturamento';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('ocupados_hoje');
        parent::addAttribute('est_entrada_semanal_quarto');
        parent::addAttribute('est_entrada_mensal_quarto');
        parent::addAttribute('est_entrada_anual_quarto');
        parent::addAttribute('est_saida_semanal');
        parent::addAttribute('est_saida_mensal');
        parent::addAttribute('est_saida_anual');
        parent::addAttribute('est_entrada_mensal');
        parent::addAttribute('est_entrada_anual');
    }


}
