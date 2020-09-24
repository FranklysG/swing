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
    
    
    private $system_user;
    private $tipo_saida;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('usuario_id');
        parent::addAttribute('tipo_saida_id');
        parent::addAttribute('descricao');
        parent::addAttribute('valor_saida');
        parent::addAttribute('status');
        parent::addAttribute('dtcadastro');
    }
    
    
    /**
     * Method set_system_user
     * Sample of usage: $saida->system_user = $object;
     * @param $object Instance of SystemUser
     */
    public function set_system_user(SystemUser $object)
    {
        $this->system_user = $object;
        $this->system_user_id = $object->id;
    }
    
    /**
     * Method get_system_user
     * Sample of usage: $saida->system_user->attribute;
     * @returns SystemUser instance
     */
    public function get_system_user()
    {
        // loads the associated object
        if (empty($this->system_user))
            $this->system_user = new SystemUser($this->system_user_id);
    
        // returns the associated object
        return $this->system_user;
    }
    
    
    /**
     * Method set_tipo_saida
     * Sample of usage: $saida->tipo_saida = $object;
     * @param $object Instance of TipoSaida
     */
    public function set_tipo_saida(TipoEntradaSaida $object)
    {
        $this->tipo_saida = $object;
        $this->tipo_saida_id = $object->id;
    }
    
    /**
     * Method get_tipo_saida
     * Sample of usage: $saida->tipo_saida->attribute;
     * @returns TipoSaida instance
     */
    public function get_tipo_saida()
    {
        // loads the associated object
        if (empty($this->tipo_saida))
            $this->tipo_saida = new TipoEntradaSaida($this->tipo_saida_id);
    
        // returns the associated object
        return $this->tipo_saida;
    }
    


}
