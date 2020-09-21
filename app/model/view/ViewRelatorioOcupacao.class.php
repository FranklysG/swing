<?php
/**
 * ViewRelatorioOcupacao Active Record
 * @author  <your-name-here>
 */
class ViewRelatorioOcupacao extends TRecord
{
    const TABLENAME = 'view_relatorio_ocupacao';
    const PRIMARYKEY= 'res_id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('res_status');
        parent::addAttribute('res_dtcadastro');
        parent::addAttribute('m_res_id');
        parent::addAttribute('m_res_n_quarto');
        parent::addAttribute('m_res_valor');
        parent::addAttribute('cos_id');
        parent::addAttribute('prod_id');
        parent::addAttribute('prod_nome');
        parent::addAttribute('prod_valor');
        parent::addAttribute('prod_qtd');
    }


}
