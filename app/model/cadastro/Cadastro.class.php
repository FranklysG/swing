<?php
/**
 * Cadastro Active Record
 * @author  <your-name-here>
 */
class Cadastro extends TRecord
{
    const TABLENAME = 'cadastro';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $system_user;
    private $cadastro_tipos;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('usuario_id');
        parent::addAttribute('nome');
        parent::addAttribute('contato');
        parent::addAttribute('sexo');
        parent::addAttribute('dtnascimento');
        parent::addAttribute('cpf_cnpj');
        parent::addAttribute('rg_ie');
        parent::addAttribute('ctps');
        parent::addAttribute('endereco');
        parent::addAttribute('bairro');
        parent::addAttribute('cidade');
        parent::addAttribute('uf');
        parent::addAttribute('cep');
        parent::addAttribute('referencia');
        parent::addAttribute('email');
        parent::addAttribute('dtcadastro');
    }

    
    /**
     * Method set_system_user
     * Sample of usage: $cadastro->system_user = $object;
     * @param $object Instance of SystemUser
     */
    public function set_system_user(SystemUser $object)
    {
        $this->system_user = $object;
        $this->system_user_id = $object->id;
    }
    
    /**
     * Method get_system_user
     * Sample of usage: $cadastro->system_user->attribute;
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
     * Method addCadastroTipo
     * Add a CadastroTipo to the Cadastro
     * @param $object Instance of CadastroTipo
     */
    public function addCadastroTipo(CadastroTipo $object)
    {
        $this->cadastro_tipos[] = $object;
    }
    
    /**
     * Method getCadastroTipos
     * Return the Cadastro' CadastroTipo's
     * @return Collection of CadastroTipo
     */
    public function getCadastroTipos()
    {
        return $this->cadastro_tipos;
    }

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        $this->cadastro_tipos = array();
    }

    /**
     * Load the object and its aggregates
     * @param $id object ID
     */
    public function load($id)
    {
    
        // load the related CadastroTipo objects
        $repository = new TRepository('CadastroTipo');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('cadastro_id', '=', $id));
        $this->cadastro_tipos = $repository->load($criteria);
    
        // load the object itself
        return parent::load($id);
    }

    /**
     * Store the object and its aggregates
     */
    public function store()
    {
        // store the object itself
        parent::store();
    
        // delete the related CadastroTipo objects
        $criteria = new TCriteria;
        $criteria->add(new TFilter('cadastro_id', '=', $this->id));
        $repository = new TRepository('CadastroTipo');
        $repository->delete($criteria);
        // store the related CadastroTipo objects
        if ($this->cadastro_tipos)
        {
            foreach ($this->cadastro_tipos as $cadastro_tipo)
            {
                unset($cadastro_tipo->id);
                $cadastro_tipo->cadastro_id = $this->id;
                $cadastro_tipo->store();
            }
        }
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        $id = isset($id) ? $id : $this->id;
        // delete the related CadastroTipo objects
        $repository = new TRepository('CadastroTipo');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('cadastro_id', '=', $id));
        $repository->delete($criteria);
        
    
        // delete the object itself
        parent::delete($id);
    }


}
