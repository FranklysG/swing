<?php
/**
 * CadastroForm Form
 * @author  <your name here>
 */
class CadastroForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Cadastro');
        $this->form->setFormTitle('Formulario de Cadastro');
        $this->form->setFieldSizes('100%');
        

        // create the form fields
        $id = new THidden('id');
        $usuario_id = new THidden('usuario_id');
        $nome = new TEntry('nome');
        $nome->forceUpperCase();
        $contato = new TEntry('contato');
        $contato->setMask('(99) 99999 - 9999');
        $sexo = new TRadioGroup('sexo');
        $sexo->addItems(['M' => 'Masculino', 'F' => 'Feminino']);
        $sexo->setLayout('horizontal');
        $sexo->setUseButton();
        $dtnascimento = new TDate('dtnascimento');
        $dtnascimento->setMask('dd/mm/yyyy');
        $dtnascimento->setDatabaseMask('yyyy-mm-dd');
        $cpf_cnpj = new TEntry('cpf_cnpj');
        $cpf_cnpj->setMask('999.999.999-99');
        $rg_ie = new TEntry('rg_ie');
        $ctps = new THidden('ctps');
        $endereco = new TEntry('endereco');
        $endereco->forceUpperCase();
        $bairro = new TEntry('bairro');
        $bairro->forceUpperCase();
        $cidade = new TEntry('cidade');
        $cidade->forceUpperCase();
        $uf = new TCombo('uf');
        $uf->AddItems(AppUtil::rUf('s'));
        $cep = new TEntry('cep');
        $referencia = new THidden('referencia');
        $email = new TEntry('email');
        $dtcadastro = new THidden('dtcadastro');

        $row = $this->form->addFields( [$id, $usuario_id]);

        // add the fields
        $row = $this->form->addFields([ new TLabel('SEU NOME'),$nome ]
                                    ,[ new TLabel('CONTATO'),$contato ]
                                    ,[ new TLabel('SEXO'),$sexo ]
                                    ,[ new TLabel('DATA NASCIMENTO'),$dtnascimento ]);
        $row->layout = ['col-sm-4','col-sm-3','col-sm-2','col-sm-3'];
        $row = $this->form->addFields([ new TLabel('CPF'),$cpf_cnpj ]
                                    ,[ new TLabel('ENDERECO'),$endereco ]
                                    ,[ new TLabel('BAIRRO'),$bairro ]);
        $row->layout = ['col-sm-3','col-sm-5','col-sm-4'];
        $row = $this->form->addFields([ new TLabel('CIDADE'),$cidade ]
                                    ,[ new TLabel('UF'),$uf ]
                                    ,[ new TLabel('CEP'),$cep ]
                                    ,[ new TLabel('EMAIL'),$email ]);
        $row->layout = ['col-sm-3','col-sm-1','col-sm-2','col-sm-6'];

        // set sizes
        $id->setSize('100%');
        $usuario_id->setSize('100%');
        $nome->setSize('100%');
        $contato->setSize('100%');
        $sexo->setSize('100%');
        $dtnascimento->setSize('100%');
        $cpf_cnpj->setSize('100%');
        $rg_ie->setSize('100%');
        $ctps->setSize('100%');
        $endereco->setSize('100%');
        $bairro->setSize('100%');
        $cidade->setSize('100%');
        $uf->setSize('100%');
        $cep->setSize('100%');
        $referencia->setSize('100%');
        $email->setSize('100%');
        $dtcadastro->setSize('100%');



        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('app'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new Cadastro;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->usuario_id = 3;
            $object->store(); // save the object

            $cad_tipo = new CadastroTipo;
            $cad_tipo->cadastro_id = $object->id;
            $cad_tipo->tipo_id = 1; // tipo 1 - cliente
            $cad_tipo->store();
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('app'); // open a transaction
                $object = new Cadastro($key); // instantiates the Active Record

                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
}
