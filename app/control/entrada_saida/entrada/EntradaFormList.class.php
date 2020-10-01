<?php
/**
 * EntradaFormList Form List
 * @author  <your name here>
 */

// use NFePHP\NFe;
use NFePHP\NFe\Tools;

class EntradaFormList extends TPage
{
    protected $form; // form
    protected $datagrid; // datagrid
    protected $pageNavigation;
    protected $loaded;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
            
        $this->form = new BootstrapFormBuilder('form_Entrada');
        $this->form->setFormTitle('Entradas');
        $this->form->setFieldSizes('100%');
        

        // create the form fields
        $id = new THidden('id');
        $tipo_entrada_id = new TDBUniqueSearch('tipo_entrada_id', 'app', 'TipoEntradaSaida', 'id', 'nome');
        $tipo_entrada_id->setMinLength(0);
        $produto_id = new TDBUniqueSearch('produto_id', 'app', 'Produto', 'id', 'nome');
        $produto_id->setMinLength(0);
        $usuario_id = new TEntry('usuario_id');
        $descricao = new TEntry('descricao');
        $descricao->forceUpperCase();
        $qtd_nota = new TEntry('qtd_nota');
        $valor_uni = new TEntry('valor_uni');
        $valor_uni->setNumericMask(2, ',', '.', true);
        $valor_venda_uni = new TEntry('valor_venda_uni');
        $valor_venda_uni->setNumericMask(2, ',', '.', true);
        $status = new TEntry('status');
        $dtcadastro = new TDate('dtcadastro');
        $dtcadastro->setMask('dd/mm/yyyy');
        $dtcadastro->setDatabaseMask('yyyy-mm-dd');

        $this->form->addFields( [$id ]);
        // add the fields
        $row = $this->form->addFields(
                                [ new TLabel('Tipo Entrada'), $tipo_entrada_id ],
                                [ new TLabel('Produto'), $produto_id ] ,
                                [ new TLabel('Quantidade'), $qtd_nota ],
                                [ new TLabel('Valor Uni'), $valor_uni ] );

        $row->layout = ['col-sm-3','col-sm-3','col-sm-3','col-sm-3'];
        $row = $this->form->addFields(
                                [ new TLabel('Valor Venda Uni'), $valor_venda_uni],
                                [ new TLabel('Data'), $dtcadastro ]);

        $row->layout = ['col-sm-3','col-sm-3'];
        // set sizes
     
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
        // $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        // $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left');
        $column_tipo_entrada_id = new TDataGridColumn('tipo_entrada->nome', 'TIPO DA ENTRADA', 'left');
        $column_produto_id = new TDataGridColumn('produto->nome', 'PRODUTO', 'left');
        $column_usuario_id = new TDataGridColumn('usuario_id', 'Usuario Id', 'left');
        $column_nome = new TDataGridColumn('descricao', 'NOME', 'left');
        $column_qtd_nota = new TDataGridColumn('qtd_nota', 'QUANT', 'center');
        $column_valor_uni = new TDataGridColumn('valor_uni', 'VALOR UNI', 'right');
        $column_valor_venda_uni = new TDataGridColumn('valor_venda_uni', 'VALOR VEN', 'right');
        $column_valor_lucro_ext = new TDataGridColumn('=({qtd_nota}*{valor_uni})-({qtd_nota}*{valor_venda_uni})', 'LUCRO EXT.', 'right');
        $column_valor_total = new TDataGridColumn('={qtd_nota}*{valor_uni}', 'VALOR NOTA TOTAL', 'right');
        $column_status = new TDataGridColumn('status', 'STATUS', 'left');
        $column_dtcadastro = new TDataGridColumn('dtcadastro', 'DATA', 'right');

        $column_valor_uni->setTransformer(function ($value) {
            return Convert::toMonetario($value);
        });

        $column_valor_venda_uni->setTransformer(function ($value) {
            return Convert::toMonetario($value);
        });

        $column_valor_lucro_ext->setTransformer(function ($value) {
            ($value > 0)?: $value = $value*(-1);
            return Convert::toMonetario($value);
        });
        

        $column_valor_total->setTransformer(function ($value) {
            return Convert::toMonetario($value);
        });

        $column_status->setTransformer(function ($value) {
           
            if ($value == 0) {
                $class = 'success';
                $label = 'LIMPO';
            } 
            else if ($value == 1) {
                $class = 'danger';
                $label = 'OCUPADO';
            }

            $div = new TElement('span');
            $div->class = "btn btn-{$class}";
            $div->style = "text-shadow:none; font-size:12px; font-weight:lighter;width:100px;";
            $div->add($label);
            return $div;
        });

        $column_dtcadastro->setTransformer(function ($value) {
                return Convert::toDateBR($value);
        });

        // add the columns to the DataGrid
        // $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_tipo_entrada_id);
        // $this->datagrid->addColumn($column_usuario_id);
        $this->datagrid->addColumn($column_produto_id);
        $this->datagrid->addColumn($column_qtd_nota);
        $this->datagrid->addColumn($column_valor_uni);
        $this->datagrid->addColumn($column_valor_venda_uni);
        $this->datagrid->addColumn($column_valor_total);
        $this->datagrid->addColumn($column_valor_lucro_ext);
        // $this->datagrid->addColumn($column_status);
        $this->datagrid->addColumn($column_dtcadastro);

        
        // creates two datagrid actions
        $action1 = new TDataGridAction([$this, 'onEdit']);
        //$action1->setUseButton(TRUE);
        //$action1->setButtonClass('btn btn-default');
        $action1->setLabel(_t('Edit'));
        $action1->setImage('far:edit blue');
        $action1->setField('id');
        $action1->setDisplayCondition( array($this, 'displayColumnToday') );
        
        $action2 = new TDataGridAction([$this, 'onDelete']);
        //$action2->setUseButton(TRUE);
        //$action2->setButtonClass('btn btn-default');
        $action2->setLabel(_t('Delete'));
        $action2->setImage('far:trash-alt red');
        $action2->setField('id');
        $action2->setDisplayCondition( array($this, 'displayColumnToday') );
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1);
        $this->datagrid->addAction($action2);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
    }
    
    // dessativa os botão quando a data não for a de hoje
    public function displayColumnToday( $object )
    {
        if(isset($object->dtcadastro)){
            if (Convert::toDateBR($object->dtcadastro) == date('d/m/Y')){
                return TRUE;
            }else{
                return FALSE;
            }
        }
        
    }

    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            
            // open a transaction with database 'app'
            TTransaction::open('app');
            // nfce compra mateus 21200803995515002615653110000268171001618230
            $chave_nfe = '53190222769530000131556110000002001616311935';
            
            // $nfe = new Tools($configJson, $certificate);
            // $nfe->csefazConsultaChave($chave_nfe);
            // var_dump($nfe);
            // var_dump($this->nfe->sefazConsultaChave($chave_nfe));
            // creates a repository for Entrada
            $repository = new TRepository('Entrada');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * Ask before deletion
     */
    public static function onDelete($param)
    {
        // define the delete action
        $action = new TAction([__CLASS__, 'Delete']);
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }
    
    /**
     * Delete a record
     */
    public static function Delete($param)
    {
        try
        {
            $key = $param['key']; // get the parameter $key
            TTransaction::open('app'); // open a transaction with database
            $object = new Entrada($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            
            $pos_action = new TAction([__CLASS__, 'onReload']);
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'), $pos_action); // success message
        }
        catch (Exception $e) // in case of exception
        {
            // new TMessage('error', $e->getMessage()); // shows the exception error message
            new TMessage('error','Existem quartos que consumiram esse produto'); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
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
            
            $object = new Entrada;  // create an empty object
            $object->qtd_estoque = $data->qtd_nota;
            $object->usuario_id = TSession::getValue('userid');
            $object->status = 1;
            
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), new TAction([$this, 'onEdit'])); // success message
            $this->onReload(); // reload the listing
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
                $object = new Entrada($key); // instantiates the Active Record
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
    
    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') )
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
}
