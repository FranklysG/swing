<?php
/**
 * ViewRelatorioOcupacao Active Record
 * @author  <your-name-here>
 */
class ViewRelatorioOcupacao extends TRecord
{
    const TABLENAME = 'view_relatorio_ocupacao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('n_quarto');
        parent::addAttribute('valor_quarto');
        parent::addAttribute('valor_consumo');
        parent::addAttribute('status');
        parent::addAttribute('dtcadastro');
        parent::addAttribute('entrada_id');
        parent::addAttribute('valor_venda_uni');
        parent::addAttribute('produto_id');
        parent::addAttribute('produto_nome');
    }


}
