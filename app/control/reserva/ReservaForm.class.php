<?php
/**
 * ReservaForm Master/Detail
 * @author  <your name here>
 */
class ReservaForm extends TPage
{
    protected $form; // form
    protected $detail_list;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_reserva_form');
        $this->form->setFormTitle('QUARTOS OCUPADOS HOJE - '.date('d/m/Y'));
        $this->form->setFieldSizes('100%');
        
        // master fields
        $id = new THidden('id');

        // detail fields
        $detail_uniqid = new THidden('detail_uniqid');
        $detail_id = new THidden('detail_id');
        $detail_n_quarto = new TEntry('detail_n_quarto');
        $detail_n_quarto->addValidation('numero do quarto', new TRequiredValidator);
        $detail_valor_quarto = new TEntry('detail_valor_quarto');
        $detail_valor_quarto->setMask('9!');
        $detail_valor_consumo = new THidden('detail_valor_consumo');
        $detail_status = new THidden('detail_status');
        $detail_dtcadastro = new THidden('detail_dtcadastro');
        
        $detail_produto_id   = new TDBCheckGroup('detail_produto_id', 'app', 'Produto', 'id', 'nome');
    
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        // // detail fields
        $this->form->addFields([$id,$detail_uniqid,$detail_id,$detail_valor_consumo]);
        
        $add = TButton::create('add', [$this, 'onDetailAdd'], 'Register', 'fa:plus-circle green');
        $add->getAction()->setParameter('static','1');

        $row = $this->form->addFields( [new TLabel('NUMERO DO QUARTO'), $detail_n_quarto],
                                        [new TLabel('VALOR'), $detail_valor_quarto],
                                        [new TLabel(''), $add],
                                        [new TLabel(''), $detail_status],
                                        [new TLabel(''), $detail_dtcadastro] );
        
        $row->layout = ['col-sm-3','col-sm-3','col-sm-3','col-sm-2','col-sm-2'];
       
        $this->detail_list = new BootstrapDatagridWrapper(new TDataGrid);
        $this->detail_list->setId('mapaReserva_list');
        $this->detail_list->generateHiddenFields();
        // $this->detail_list->datatable = 'true';
        // $this->detail_list->style = "margin-bottom: 10px";
        
        $column_uniqid = new TDataGridColumn('uniqid', 'Uniqid', 'left');
        $column_id = new TDataGridColumn('id', 'Id', 'left') ;
        $column_n_quarto = new TDataGridColumn('n_quarto', 'QTO', 'center', 100) ;
        $column_valor_quarto = new TDataGridColumn('valor_quarto', 'VALOR', 'left', 100);
        $column_valor_consumo = new TDataGridColumn('valor_consumo', 'CONSUMO', 'left', 100);
        $column_valor_total = new TDataGridColumn('={valor_quarto}+{valor_consumo}', 'TOTAL', 'center', 100);
        $column_dtcadastro = new TDataGridColumn('dtcadastro', 'DATA', 'center', 100);
        $column_status = new TDataGridColumn('status', 'STATUS', 'center', 100);
        
        $column_valor_quarto->setTransformer(function ($value) {
            return Convert::toMonetario($value);
        });

        $column_valor_consumo->setTransformer(function ($value) {
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
                return Convert::toDateTimeBR($value);
        });

        // items
        $this->detail_list->addColumn( $column_uniqid)->setVisibility(false);
        $this->detail_list->addColumn( $column_id )->setVisibility(false);
        $this->detail_list->addColumn( $column_n_quarto );
        $this->detail_list->addColumn( $column_valor_quarto );
        $this->detail_list->addColumn( $column_valor_consumo );
        $this->detail_list->addColumn( $column_valor_total );
        // $this->detail_list->addColumn( $column_status );
        $this->detail_list->addColumn( $column_dtcadastro );

        // detail actions
        $action2 = new TDataGridAction([$this, 'onDetailEdit'] );
        $action2->setFields( ['uniqid', '*'] );
        
        $action1 = new TDataGridAction([$this, 'onDetailDelete']);
        $action1->setFields(['uniqid', '*']);
        $action1->setDisplayCondition( array($this, 'displayColumnToday') );
        
        $action3 = new TDataGridAction(['CartProdutoList', 'onReload'],['id_mapa_reserva' => '{id}']);
        $action3->setDisplayCondition( array($this, 'displayColumn') );
        $action3->setDisplayCondition( array($this, 'displayColumnToday') );
        
        $this->detail_list->addAction($action3, 'Produtos', 'fa:cart-plus green');
        $this->detail_list->addAction($action1, _t('Delete'), 'far:trash-alt red');
        if(TSession::getValue('userid') == 1){
            $this->detail_list->addAction($action2, _t('Edit'), 'fa:edit blue');
        }

        
        
        $this->detail_list->createModel();
        
        $panel = new TPanelGroup;
        $panel->add($this->detail_list);
        $panel->getBody()->style = 'overflow-x:auto';
        $this->form->addFields( [$panel] );
        
        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }
    
    public function displayColumn( $object )
    {
        if (!empty($object->id))
        {
            return TRUE;
        }
        return FALSE;
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
     * Clear form
     * @param $param URL parameters
     */
    public function onClear($param)
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Add detail item
     * @param $param URL parameters
     */
    public function onDetailAdd( $param )
    {
        try
        {
            $this->form->validate();
            $data = $this->form->getData();
            TTransaction::open('app');
            
            /** validation sample
            if (empty($data->fieldX))
            {
                throw new Exception('The field fieldX is required');
            }
            **/
            // soma o valor do produto e adiciona na grid 
            $produto_valor = 0;
            if(isset($param['detail_produto_id'])){
                foreach ($param['detail_produto_id'] as $key => $value) {
                    $produto_valor += Produto::find($value)->valor;
                }
            }
            $uniqid = !empty($data->detail_uniqid) ? $data->detail_uniqid : uniqid();
            
            $grid_data = [];
            $grid_data['uniqid'] = $uniqid;
            $grid_data['id'] = $data->detail_id;
            $grid_data['n_quarto'] = $data->detail_n_quarto;
            $grid_data['valor_quarto'] = ($data->detail_valor_quarto)?: 30 ;
            $grid_data['valor_consumo'] = ($data->detail_valor_consumo)?: 0 ;
            $grid_data['status'] = ($data->detail_status)?: 1;
            // $grid_data['dtcadastro'] = date('d/m/Y');
            
            // insert row dynamically
            $row = $this->detail_list->addItem( (object) $grid_data );
            $row->id = $uniqid;
            
            TDataGrid::replaceRowById('mapaReserva_list', $uniqid, $row);
            
            // clear detail form fields
            $data->detail_uniqid = '';
            $data->detail_id = '';
            $data->detail_n_quarto = '';
            $data->detail_valor_quarto = '';
            $data->detail_valor_consumo = '';
            $data->detail_status = '';
            $data->detail_dtcadastro = '';
            
            // send data, do not fire change/exit events
            TForm::sendData( 'form_Reserva', $data, false, false );
            TTransaction::close();

        }
        catch (Exception $e)
        {
            $this->form->setData( $this->form->getData());
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Edit detail item
     * @param $param URL parameters
     */
    public static function onDetailEdit( $param )
    {
        $data = new stdClass;
        $data->detail_uniqid = $param['uniqid'];
        $data->detail_id = $param['id'];
        $data->detail_n_quarto = $param['n_quarto'];
        $data->detail_valor_quarto = $param['valor_quarto'];
        $data->detail_valor_consumo = $param['valor_consumo'];
        $data->detail_status = $param['status'];
        // $data->detail_dtcadastro = $param['dtcadastro'];
        
        // send data, do not fire change/exit events
        TForm::sendData( 'form_Reserva', $data, false, false );
    }
    
    /**
     * Ask before deletion
     */
    public static function onDetailDelete($param)
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
            $key = $param['id']; // get the parameter $key
            TTransaction::open('app'); // open a transaction with database
            
            $object = new MapaReserva($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            
            // clear detail form fields
            $data = new stdClass;
            $data->detail_uniqid = '';
            $data->detail_id = '';
            $data->detail_n_quarto = '';
            $data->detail_valor_quarto = '';
            $data->detail_valor_consumo = '';
            $data->detail_status = '';
            $data->detail_dtcadastro = '';
            
            // send data, do not fire change/exit events
            TForm::sendData( 'form_Reserva', $data, false, false );
            // remove row
            TDataGrid::removeRowById('mapaReserva_list', $param['uniqid']);
            
            TTransaction::close(); // close the transaction
            
            $pos_action = new TAction([__CLASS__, 'onEdit'],['id' => TSession::getValue('form_reserva_form_id'), 'key' => TSession::getValue('form_reserva_form_id')]);
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'), $pos_action); // success message
        }
        catch (Exception $e) // in case of exception
        {
            // new TMessage('error', $e->getMessage()); // shows the exception error message
            new TMessage('error', 'Existem produtos no carrinho ',new TAction([__CLASS__, 'onEdit'],['id' => TSession::getValue('form_reserva_form_id'), 'key' => TSession::getValue('form_reserva_form_id')])); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
   
    /**
     * Load Master/Detail data from database to form
     */
    public function onEdit($param)
    {
        try
        {
            TTransaction::open('app');
            TSession::setValue('form_cart_produto_list_obj', null);
            if (isset($param['key']))
            {
                TSession::setValue('form_reserva_form_id',$param['key']);
                $key = $param['key'];
                
                $object = new Reserva($key);
                $items  = MapaReserva::where('reserva_id', '=', $key)->load();

                foreach( $items as $item )
                {
                    
                    $item->uniqid = uniqid();
                    $row = $this->detail_list->addItem( $item );
                    $row->id = $item->uniqid;
                }

                // esconde o botão de salvar quando a data for diferente da de hoje
                if(Convert::toDateBR($object->dtcadastro) == date('d/m/Y')){
                    $this->form->addAction( 'Salvar',  new TAction([$this, 'onSave'], ['static'=>'1']), 'fa:save green');
                }
                $this->form->addAction( 'Voltar', new TAction(['ReservaList', 'onReload']), 'fa:eraser red');

                $this->form->setData($object);
                TTransaction::close();
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Save the Master/Detail data from form to database
     */
    public function onSave($param)
    {
        try
        {
            // open a transaction with database
            TTransaction::open('app');
            $master_id = $param['id'];
            
            $data = $this->form->getData();
            // $this->form->validate();

            if( isset($param['mapaReserva_list_id']) )
            {
                foreach( $param['mapaReserva_list_id'] as $key => $item_id )
                {
                    $detail = MapaReserva::where('reserva_id', '=', $master_id)
                                    ->where('id','=',$param['mapaReserva_list_id'][$key])->first();

                    if(!$detail)
                        $detail = new MapaReserva;
                    $detail->n_quarto  = $param['mapaReserva_list_n_quarto'][$key];
                    $detail->valor_quarto  = $param['mapaReserva_list_valor_quarto'][$key];
                    $detail->valor_consumo  = $param['mapaReserva_list_valor_consumo'][$key];
                    // $detail->status  = $param['mapaReserva_list_status'][$key];
                    $detail->status  = 1;
                    $detail->reserva_id = $master_id;
                    $detail->store();
                }
            }
            TTransaction::close(); // close the transaction
            
            TForm::sendData('form_Reserva', (object) ['id' => $master_id]);
            
            new TMessage('info', 'Registro salvo com sucesso', new TAction([$this, 'onEdit'],['id' => TSession::getValue('form_reserva_form_id'), 'key' => TSession::getValue('form_reserva_form_id')]));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback();
        }
    }
}
