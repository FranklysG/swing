<?php
/**
 * ViewCartProduto Active Record
 * @author  <your-name-here>
 */
class ViewCartProduto extends TRecord
{
    const TABLENAME = 'view_cart_produto';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('tipo_entrada_saida_id');
        parent::addAttribute('produto_id');
        parent::addAttribute('usuario_id');
        parent::addAttribute('descricao');
        parent::addAttribute('qtd_nota');
        parent::addAttribute('qtd_estoque');
        parent::addAttribute('valor_uni');
        parent::addAttribute('valor_venda_uni');
        parent::addAttribute('status');
        parent::addAttribute('dtcadastro');
    }


}
