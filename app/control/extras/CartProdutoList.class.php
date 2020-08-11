<?php
/**
 * ProdutoList Listing
 * @author  <your name here>
 */
class CartProdutoList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        parent::setTargetContainer("adianti_right_panel");
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_cart_produto_list');
        $this->form->style = "width: 100%;height:70px;";
        
        // master fields
        $id = new THidden('id');
        $n_quarto = new THidden('n_quarto');
        $valor = new THidden('valor');
        $detail_n_quarto = new TEntry('detail_n_quarto');
        $detail_n_quarto->addValidation('Numero do mapa_reserva', new TRequiredValidator);
        $detail_n_quarto->setEditable(FALSE);
        $detail_valor = new TEntry('detail_valor');
        $detail_valor->setMask('9!');
        $detail_valor->setNumericMask(2,',','.', true);
        $detail_valor->setEditable(FALSE);
        
        $this->form->addFields([$id,$n_quarto,$valor]);
        $row = $this->form->addFields( [new TLabel('NUMERO DO QUARTO'), $detail_n_quarto],
                                        [new TLabel('VALOR'), $detail_valor]
                                        );
        
        $row->layout = ['col-sm-6','col-sm-6'];

       
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        
        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left');
        $column_nome = new TDataGridColumn('nome', 'NOME', 'left');
        $column_qtd = new TDataGridColumn('qtd', 'QTD', 'left');
        $column_check = new TDataGridColumn('check', '', 'left');
        $column_valor = new TDataGridColumn('valor', 'VALOR', 'left');

        $column_check->setTransformer( function($value, $object, $row) {
            $class = 'danger';
            $label = 'NÃO';
            if ($value == 1) {
                $class = 'success';
                $label = 'SIM';
            }

            $div = new TElement('span');
            $div->class = "btn btn-{$class}";
            $div->style = "text-shadow:none; font-size:12px; font-weight:bold;width:80px;";
            $div->add($label);
            return $div;
        });

        $column_valor->setTransformer(function ($value) {
            return Convert::toMonetario($value);
        });

        // add the columns to the DataGrid
        // $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_qtd);
        $this->datagrid->addColumn($column_check);
        $this->datagrid->addColumn($column_valor);


        $action1 = new TDataGridAction([$this, 'onAddItem'],['static'=>'1']);
        $action1->setFields(['id', 'valor']);
        $action2 = new TDataGridAction([$this, 'onDelItem'],['static'=>'1']);
        $action2->setDisplayCondition( array($this, 'displayColumn') );
        $action2->setFields(['id', 'valor']);
        
        $this->datagrid->addAction($action1, 'adicionar',   'fa:plus-circle green');
        $this->datagrid->addAction($action2 ,'remover', 'fa:minus-circle red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup('<strong>CARRINHO - ' . date('d/m/y').'</strong>');
        $panel->add($this->form);
        $panel->add($this->datagrid);
        // $panel->addHeaderActionLink('', new TAction([$this, 'onSave'], ['register_state' => 'false']), 'fa:cart-plus green');
        $panel->addHeaderActionLink('', new TAction([$this, 'onClose']), 'fa:times red');
        $panel->getBody()->style = "overflow-x:auto;";
        $panel->addFooter($this->pageNavigation);

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(TPanelGroup::pack('CARRINHO DE PRODUTOS',$this->datagrid, $this->pageNavigation));
        $container->add($panel);
        
        parent::add($container);

    }
    
    public function displayColumn( $object )
    {
        if ($object->check == 1)
        {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Inline record editing
     * @param $param Array containing:
     *              key: object ID value
     *              field name: object attribute to be updated
     *              value: new attribute content 
     */
    
    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue(__CLASS__ . '_filter_data', $data);
        
        $param = array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    public function onAddItem($param)
    {
        try {
            TTransaction::open('app');
            
            $data = TSession::getValue('form_cart_produto_list_obj');
            if(isset($data)){
                // atualiza o valor do mapa_reserva de acordo com a adição do produto
                $mapa_reserva = MapaReserva::find($data->id_mapa_reserva);
                $mapa_reserva->valor += $param['valor'];
                $mapa_reserva->store(); 
                
                // incrementa o produto na tabela de consumo
                $consumo = new Consumo;
                $consumo->produto_id = $param['id'];
                $consumo->mapa_reserva_id = $data->id_mapa_reserva;
                $consumo->store();
                
                $data->detail_valor = $mapa_reserva->valor;;
                $obj = new stdClass;
                $obj->check = 1;
                TForm::sendData('form_cart_produto_list', $obj, false, false);
                TSession::setValue('form_cart_produto_list_obj', $data);
                
                TScript::create("__adianti_load_page('index.php?class=CartProdutoList&method=onReload&register_state=false');");
            }
            TTransaction::close();
        }  catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
       
    }

    public function onDelItem($param)
    {
        try {
            TTransaction::open('app');
            
            // atualiza o valor do mapa_reserva de acordo com a remoção do produto
            $data = TSession::getValue('form_cart_produto_list_obj');
            $mapa_reserva = MapaReserva::find($data->id_mapa_reserva);
            $mapa_reserva->valor -= $param['valor'];
            $mapa_reserva->store(); 
            
            // Remove o produto selecionado
            $consumo = Consumo::where('mapa_reserva_id','=',$data->id_mapa_reserva)
                                        ->where('produto_id','=',$param['id'])->first();

            $consumo->delete();
            
            $obj = $data;
            $obj->detail_valor = $mapa_reserva->valor;
            $obj->check = 0;
            TForm::sendData('form_cart_produto_list', $obj, false, false);
            TSession::setValue('form_cart_produto_list_obj', $obj);
            
            TScript::create("__adianti_load_page('index.php?class=CartProdutoList&method=onReload&register_state=false');");
            TTransaction::close();
        }  catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
        
    }
    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            
            TTransaction::open('app');
            $data = TSession::getValue('form_cart_produto_list_obj');
            // verificar se vem a sessão dos additens la em cima
            if(!isset($data)){
                $key = $param['id_mapa_reserva'];
                $obj = MapaReserva::find($key);
                $obj->id_mapa_reserva = $key;
                $obj->detail_n_quarto = $obj->n_quarto;
                $obj->detail_valor = $obj->valor;
                $this->form->setData($obj);
                TSession::setValue('form_cart_produto_list_obj',$obj);
            }else{
                $this->form->setData(TSession::getValue('form_cart_produto_list_obj'));
               
            }
           
            
            // creates a repository for Produto
            $repository = new TRepository('Produto');
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
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $this->datagrid->clear();
            
            $ids = [];
            
            ($data)?
                $id_mapa_reserva = $data->id_mapa_reserva
            :
                $id_mapa_reserva = $param['id_mapa_reserva']
            ;
            
            $consumos = Consumo::where('mapa_reserva_id','=',$id_mapa_reserva)->load();
            if(isset($consumos)){
                foreach ($consumos as $value) {
                    $ids[] = $value->produto_id;
                }
            }
                  
            if ($objects)
            {
                foreach ($objects as $object)
                {
                    // iterate the collection of active records
                    $object->qtd = 0;
                    $object->check = 0;
                    foreach ($ids as $value) {
                        if($object->id == $value){
                            $object->qtd += 1;
                            $object->check = 1;
                        }
                    }
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
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public static function onClose($param)
    {
        new TMessage('info', 'Produto(s) adicionados', new TAction(['ReservaForm', 'onEdit'],['id' => TSession::getValue('form_reserva_form_id'), 'key' => TSession::getValue('form_reserva_form_id')]));
        TScript::create("Template.closeRightPanel()");
        // TApplication::loadPage('ReservaForm','onEdit',['key' => TSession::getValue('form_reserva_form_id')]);
    }

    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
}
