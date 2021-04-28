<?php
/**
 * TipoEstoque Active Record
 * @author  <your-name-here>
 */
class TipoEntradaSaida extends TRecord
{
    const TABLENAME = 'tipo_entrada_saida';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('dtcadastro');
    }


}
