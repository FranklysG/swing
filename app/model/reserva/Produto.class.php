<?php
/**
 * Produto Active Record
 * @author  <your-name-here>
 */
class Produto extends TRecord
{
    const TABLENAME = 'produto';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('valor');
        parent::addAttribute('dtcadastro');
    }

}