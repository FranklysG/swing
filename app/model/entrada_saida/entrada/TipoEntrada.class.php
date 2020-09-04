<?php
/**
 * TipoEstoque Active Record
 * @author  <your-name-here>
 */
class TipoEntrada extends TRecord
{
    const TABLENAME = 'tipo_entrada';
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
