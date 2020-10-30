<?php
/**
 * Entrada Active Record
 * @author  <your-name-here>
 */
class Entrada extends TRecord
{
    const TABLENAME = 'entrada';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $tipo_entrada_saida;
    private $produto;
    private $usuario;

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

    
    /**
     * Method set_tipo_entrada_saida
     * Sample of usage: $entrada->tipo_entrada_saida = $object;
     * @param $object Instance of TipoEntradaSaida
     */
    public function set_tipo_entrada_saida(TipoEntradaSaida $object)
    {
        $this->tipo_entrada_saida = $object;
        $this->tipo_entrada_saida_id = $object->id;
    }
    
    /**
     * Method get_tipo_entrada_saida
     * Sample of usage: $entrada->tipo_entrada_saida->attribute;
     * @returns TipoEntradaSaida instance
     */
    public function get_tipo_entrada_saida()
    {
        // loads the associated object
        if (empty($this->tipo_entrada_saida))
            $this->tipo_entrada_saida = new TipoEntradaSaida($this->tipo_entrada_saida_id);
    
        // returns the associated object
        return $this->tipo_entrada_saida;
    }
    
    
    /**
     * Method set_produto
     * Sample of usage: $entrada->produto = $object;
     * @param $object Instance of Produto
     */
    public function set_produto(Produto $object)
    {
        $this->produto = $object;
        $this->produto_id = $object->id;
    }
    
    /**
     * Method get_produto
     * Sample of usage: $entrada->produto->attribute;
     * @returns Produto instance
     */
    public function get_produto()
    {
        // loads the associated object
        if (empty($this->produto))
            $this->produto = new Produto($this->produto_id);
    
        // returns the associated object
        return $this->produto;
    }
    
    
    /**
     * Method set_usuario
     * Sample of usage: $entrada->usuario = $object;
     * @param $object Instance of SystemUser
     */
    public function set_usuario(SystemUser $object)
    {
        $this->usuario = $object;
        $this->usuario_id = $object->id;
    }
    
    /**
     * Method get_usuario
     * Sample of usage: $entrada->usuario->attribute;
     * @returns SystemUser instance
     */
    public function get_usuario()
    {
        // loads the associated object
        if (empty($this->usuario))
            $this->usuario = new SystemUser($this->usuario_id);
    
        // returns the associated object
        return $this->usuario;
    }
    


}
