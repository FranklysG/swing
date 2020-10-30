<?php
/**
 * entrada Active Record
 * @author  <your-name-here>
 */
class Entrada extends TRecord
{
    const TABLENAME = 'entrada';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $tipo_entrada;
    private $consumo;
    private $usuario;
    private $produto;
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('produto_id');
        parent::addAttribute('tipo_entrada_id');
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
     * Method set_tipo_entrada
     * Sample of usage: $entrada->tipo_entrada = $object;
     * @param $object Instance of Tipoentrada
     */
    public function set_tipo_entrada(TipoEntradaSaida $object)
    {
        $this->tipo_entrada = $object;
        $this->tipo_entrada_id = $object->id;
    }
    
    /**
     * Method get_tipo_entrada
     * Sample of usage: $entrada->tipo_entrada->attribute;
     * @returns Tipoentrada instance
     */
    public function get_tipo_entrada()
    {
        // loads the associated object
        if (empty($this->tipo_entrada))
            $this->tipo_entrada = new TipoEntradaSaida($this->tipo_entrada_id);
    
        // returns the associated object
        return $this->tipo_entrada;
    }
    
    
    /**
     * Method set_consumo
     * Sample of usage: $entrada->consumo = $object;
     * @param $object Instance of Consumo
     */
    public function set_consumo(Consumo $object)
    {
        $this->consumo = $object;
        $this->consumo_id = $object->id;
    }
    
    /**
     * Method get_consumo
     * Sample of usage: $entrada->consumo->attribute;
     * @returns Consumo instance
     */
    public function get_consumo()
    {
        // loads the associated object
        if (empty($this->consumo))
            $this->consumo = new Consumo($this->consumo_id);
    
        // returns the associated object
        return $this->consumo;
    }
    
    
    
    /**
     * Method set_produto
     * Sample of usage: $consumo->produto = $object;
     * @param $object Instance of Produto
     */
    public function set_produto(Produto $object)
    {
        $this->produto = $object;
        $this->produto_id = $object->id;
    }
    
    /**
     * Method get_produto
     * Sample of usage: $consumo->produto->attribute;
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
     * Sample of usage: $reserva->usuario = $object;
     * @param $object Instance of SystemUser
     */
    public function set_usuario(SystemUser $object)
    {
        $this->usuario = $object;
        $this->usuario_id = $object->id;
    }
    
    /**
     * Method get_usuario
     * Sample of usage: $reserva->usuario->attribute;
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
