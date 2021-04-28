<?php
/**
 * Saida Active Record
 * @author  <your-name-here>
 */
class Saida extends TRecord
{
    const TABLENAME = 'saida';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $tipo_entrada_saida;
    private $usuario;
    private $entrada;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('usuario_id');
        parent::addAttribute('entrada_id');
        parent::addAttribute('tipo_entrada_saida_id');
        parent::addAttribute('descricao');
        parent::addAttribute('valor_saida');
        parent::addAttribute('status');
        parent::addAttribute('dtcadastro');
    }

    
    /**
     * Method set_tipo_entrada_saida
     * Sample of usage: $saida->tipo_entrada_saida = $object;
     * @param $object Instance of TipoEntradaSaida
     */
    public function set_tipo_entrada_saida(TipoEntradaSaida $object)
    {
        $this->tipo_entrada_saida = $object;
        $this->tipo_entrada_saida_id = $object->id;
    }
    
    /**
     * Method get_tipo_entrada_saida
     * Sample of usage: $saida->tipo_entrada_saida->attribute;
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
     * Method set_usuario
     * Sample of usage: $saida->usuario = $object;
     * @param $object Instance of SystemUser
     */
    public function set_usuario(SystemUser $object)
    {
        $this->usuario = $object;
        $this->usuario_id = $object->id;
    }
    
    /**
     * Method get_usuario
     * Sample of usage: $saida->usuario->attribute;
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
    /**
     * Method set_entrada
     * Sample of usage: $saida->entrada = $object;
     * @param $object Instance of SystemUser
     */
    public function set_entrada(SystemUser $object)
    {
        $this->entrada = $object;
        $this->entrada_id = $object->id;
    }
    
    /**
     * Method get_entrada
     * Sample of usage: $saida->entrada->attribute;
     * @returns SystemUser instance
     */
    public function get_entrada()
    {
        // loads the associated object
        if (empty($this->entrada))
            $this->entrada = new SystemUser($this->entrada_id);
    
        // returns the associated object
        return $this->entrada;
    }
    


}
