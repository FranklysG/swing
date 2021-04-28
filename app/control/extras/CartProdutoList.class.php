<?php
/**
 * ProdutoList Listing
 * @author  <your name here>
 */
class CartProdutoList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $datagrid_cosumido; // listing
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
        $this->form->setFormTitle('<strong>CARRINHO</strong>');
        $this->form->setProperty('class', 'cartprodutolist_css');
        $this->form->setFieldSizes('100%');
        
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
        $row = $this->form->addFields( [new TLabel('<br>NUMERO DO QUARTO'), $detail_n_quarto],
                                        [new TLabel('<br>VALOR'), $detail_valor]
                                        );
        
        $row->layout = ['col-sm-6','col-sm-6'];

       
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        
        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_nome = new TDataGridColumn('nome', 'NOME', 'left');
        $column_qtd = new TDataGridColumn('qtd', 'QTD', 'center');
        $column_qtd_estoque = new TDataGridColumn('qtd_estoque', 'QTD ESTOQUE', 'center');
        $column_check = new TDataGridColumn('check', '', 'right');
        $column_valor = new TDataGridColumn('valor_venda_uni', 'VALOR', 'right');

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
        $this->datagrid->addColumn($column_qtd);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_qtd_estoque);
        // $this->datagrid->addColumn($column_check);
        $this->datagrid->addColumn($column_valor);

        $action1 = new TDataGridAction([$this, 'onAddItem'],['static'=>'1']);
        $action1->setFields(['id', 'valor_venda_uni']);
        $action1->setDisplayCondition( array($this, 'displayColumnPlus') );
        $action2 = new TDataGridAction([$this, 'onDelItem'],['static'=>'1']);
        $action2->setFields(['id', 'valor_venda_uni']);
        $action2->setDisplayCondition( array($this, 'displayColumnMinus') );
        
        $this->datagrid->addAction($action1, 'adicionar',   'fa:plus-circle green');
        $this->datagrid->addAction($action2 ,'remover', 'fa:minus-circle red');
        
        // creates a Datagrid of products consumidos 
        $this->datagrid_cosumido = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid_cosumido->style = 'width: 100%';
        $this->datagrid_cosumido->datatable = 'true';
        
        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_entrada_id = new TDataGridColumn('entrada_id', 'Id', 'right');
        $column_nome = new TDataGridColumn('nome', 'NOME', 'left');
        $column_qtd = new TDataGridColumn('qtd', 'QTD', 'center');
        $column_qtd_estoque = new TDataGridColumn('qtd_estoque', 'QTD ESTOQUE', 'center');
        $column_check = new TDataGridColumn('check', '', 'right');
        $column_valor = new TDataGridColumn('valor_venda_uni', 'VALOR', 'right');

        $column_valor->setTransformer(function ($value) {
            return Convert::toMonetario($value);
        });

        // add the columns to the DataGrid
        // $this->datagrid_cosumido->addColumn($column_id);
        $this->datagrid_cosumido->addColumn($column_qtd);
        $this->datagrid_cosumido->addColumn($column_nome);
        // $this->datagrid_cosumido->addColumn($column_qtd_estoque);
        // $this->datagrid_cosumido->addColumn($column_check);
        $this->datagrid_cosumido->addColumn($column_valor);

        $action4 = new TDataGridAction([$this, 'onDelItem'],['static'=>'1']);
        $action4->setFields(['entrada_id', 'valor_venda_uni']);
        $action4->setDisplayCondition( array($this, 'displayColumnMinus') );
        
        $this->datagrid_cosumido->addAction($action4 ,'remover', 'fa:minus-circle red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        $this->datagrid_cosumido->createModel();

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
       
        // $this->form->addHeaderActionLink('', new TAction([$this, 'onClose']), 'fa:times red');
        $this->form->addHeaderActionLink( _t('Close'), new TAction(array($this, 'onClose')), 'fa:times red');
        // $this->form->addFields([$this->datagrid]);
        parent::add($this->form);
        parent::add(TPanelGroup::pack('<strong>CONSUMIDOS NO QUARTO</strong>', $this->datagrid_cosumido));
        parent::add(TPanelGroup::pack('<strong>PORDUTOS EM ESTOQUE</strong>', $this->datagrid, $this->pageNavigation));

    }
    
    public function displayColumnPlus( $object )
    {
        if ($object->qtd_estoque >= 1)
        {
            return TRUE;
        }
        return FALSE;
    }

    public function displayColumnMinus( $object )
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
                $mapa_reserva->valor_consumo += $param['valor_venda_uni'];
                $mapa_reserva->store(); 
                
                $entrada = Entrada::find($param['id']);
                $entrada->qtd_estoque -= 1;
                if($entrada->qtd_estoque == 0){
                    // status 1 quer dizer que não tem mais produto 
                    $entrada->qtd_estoque = 0;
                    $entrada->status = 0;
                    new TMessage('info',"Sem produto no estoque");
                }
                
                // incrementa o produto na tabela de consumo
                $consumo = new Consumo;
                $consumo->produto_id = $param['id'];
                $consumo->entrada_id = $param['id'];
                $consumo->mapa_reserva_id = $data->id_mapa_reserva;
                $consumo->store();
                
                $entrada->store(); 

                $data->detail_valor = $mapa_reserva->valor_quarto + $mapa_reserva->valor_consumo;
                $obj = new stdClass;
                $obj->check = 1;
                TForm::sendData('form_cart_produto_list', $obj, false, false);
                TSession::setValue('form_cart_produto_list_obj', $data);
                
                
                $page = '';
                $offset = '';
                if(isset($param['page'])){
                    $page = $param['page'];
                }
                if(isset($param['offset'])){
                    $offset = $param['offset'];
                }
                TScript::create("__adianti_load_page('index.php?class=CartProdutoList&method=onReload&offset={$offset}&limit=10&direction=asc&page={$page}&first_page=1&order=id');");
            }
            TTransaction::close();
        }  catch (Exception $e)
        {
            new TMessage('info', $e->getMessage());
            TTransaction::rollback();
        }
       
    }

    public function onDelItem($param)
    {
        try {
            TTransaction::open('app');
            if(empty($param['id'])){
                $param['id'] = $param['entrada_id'];
            }
            // atualiza o valor do mapa_reserva de acordo com a remoção do produto
            $data = TSession::getValue('form_cart_produto_list_obj');
            $mapa_reserva = MapaReserva::find($data->id_mapa_reserva);
            $mapa_reserva->valor_consumo -= $param['valor_venda_uni'];
            $mapa_reserva->store(); 
            
            // Remove o produto selecionado
            $consumo = Consumo::where('mapa_reserva_id','=',$data->id_mapa_reserva)
                                        ->where('entrada_id','=',$param['id'])->first();

            $consumo->delete();
            
            $entrada = Entrada::find($param['id']);
            $entrada->qtd_estoque += 1;
            $entrada->status = 1;
            $entrada->store(); 

            $data->detail_valor = $mapa_reserva->valor_quarto + $mapa_reserva->valor_consumo;
            $obj = new stdClass;
            $obj->check = 1;
            TForm::sendData('form_cart_produto_list', $obj, false, false);
            TSession::setValue('form_cart_produto_list_obj', $data);
            
            $page = '';
            $offset = '';
            if(isset($param['page'])){
                $page = $param['page'];
            }
            if(isset($param['offset'])){
                $offset = $param['offset'];
            }
            TScript::create("__adianti_load_page('index.php?class=CartProdutoList&method=onReload&offset={$offset}&limit=10&direction=asc&page={$page}&first_page=1&order=id');");
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
                $obj->detail_valor = $obj->valor_quarto + $obj->valor_consumo;
                $this->form->setData($obj);
                TSession::setValue('form_cart_produto_list_obj',$obj);
            }else{
                $this->form->setData(TSession::getValue('form_cart_produto_list_obj'));
               
            }
             
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }

            // creates a repository for Produto
            $repository = new TRepository('Entrada');
            $limit = 10;
            
            $criteria = new TCriteria;
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            // tipo da entrada 1 = consumo
            $criteria->add(new TFilter('tipo_entrada_saida_id','=',1));
            $criteria->add(new TFilter('qtd_estoque','>',0));
            // status 0 diz que o produto ainda ta disponivel
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $cart_products = [];
            if($objects){
                foreach ($objects as $object) {

                    $cart_products[$object->produto_id] = [
                        'id' => $object->id,
                        'produto_id' => $object->produto_id,
                        'nome' => $object->descricao,
                        'qtd_estoque' => $object->qtd_estoque,
                        'valor_venda_uni' => $object->valor_venda_uni
                    ];
                }
            }
            
            // verifica se o id do quarto ta vindo no TEntry na aba do carrinho, se não tiver 
            // ele pega pelo param
            $ids = [];
            
            ($data)?
                $id_mapa_reserva = $data->id_mapa_reserva
            :
                $id_mapa_reserva = $param['id_mapa_reserva']
            ;
            
            // pega o id de todos os produtos consumidos por esse quarto ....
            $consumos = Consumo::where('mapa_reserva_id','=',$id_mapa_reserva)->load();
            if(isset($consumos)){
                foreach ($consumos as $value) {
                    $ids[] = $value->entrada_id;
                   
                }
            }
            
            $this->datagrid->clear();
            if ($cart_products)
            {
                foreach ($cart_products as $object)
                {
                    // inicia a quantidade de produtos do carrinho no 0
                    $object['qtd'] = 0;
                    $object['check'] = 0;
                    foreach ($ids as $value) {
                        // se já tiver o id do produto ele soma mais um e se
                        // não tiver ele adiciona um na contagem
                        if($object['id'] == $value){
                            $object['qtd'] += 1;
                            // check serve pra controlar o botão de menos
                            $object['check'] = 1;
                        }
                    }
                    $this->datagrid->addItem((object)$object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit

            // creates a repository for Produto
            $repository = new TRepository('Consumo');
            $limit = 10;
            
            $criteria = new TCriteria;
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            
            // tipo da entrada 1 = consumo
            $criteria->add(new TFilter('mapa_reserva_id','=',$id_mapa_reserva));
            // status 0 diz que o produto ainda ta disponivel
            $objects = $repository->load($criteria, FALSE);
            
            $cart_products = [];
            if($objects){
                foreach ($objects as $object) {
                    $cart_products[$object->entrada_id] = [
                        'id' => $object->id,
                        'entrada_id' => $object->entrada_id,
                        'nome' => $object->entrada->descricao,
                        'qtd_estoque' => $object->entrada->qtd_estoque,
                        'valor_venda_uni' => $object->entrada->valor_venda_uni
                    ];
                }
            }
            
            if ($cart_products)
            {
                foreach ($cart_products as $object)
                {
                    // inicia a quantidade de produtos do carrinho no 0
                    $object['qtd'] = 0;
                    $object['check'] = 0;
                    foreach ($ids as $value) {
                        // se já tiver o id do produto ele soma mais um e se
                        // não tiver ele adiciona um na contagem
                        if(isset($object['entrada_id'])){
                            if($object['entrada_id'] == $value){
                                $object['qtd'] += 1;
                                $object['check'] = 1;
                                // check serve pra controlar o botão de menos
                            }
                        }
                    }
                    $this->datagrid_cosumido->addItem((object)$object);
                }
            }
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
