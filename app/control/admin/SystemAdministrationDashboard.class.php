<?php
/**
 * SystemAdministrationDashboard
 *
 * @version    1.0
 * @package    control
 * @subpackage log
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemAdministrationDashboard extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();
        
        try
        {
            $html = new THtmlRenderer('app/resources/system_admin_dashboard.html');
            
            TTransaction::open('permission');
            $indicator1 = new THtmlRenderer('app/resources/info-box.html');
            $indicator2 = new THtmlRenderer('app/resources/info-box.html');
            $indicator3 = new THtmlRenderer('app/resources/info-box.html');
            $indicator4 = new THtmlRenderer('app/resources/info-box.html');
            $indicator5 = new THtmlRenderer('app/resources/info-box.html');
            $indicator6 = new THtmlRenderer('app/resources/info-box.html');
           
            // mapa_reservas semanais
            $repositoy = new TRepository('ViewFaturamento');
            $objects = $repositoy->load();
            
            foreach ($objects as $value) {
                $indicator1->enableSection('main', ['title' => 'Ocupados Hoje',    'icon' => 'users',       'background' => 'orange', 'value' => $value->ocupados_hoje]);
                $indicator2->enableSection('main', ['title' => 'Entradas Mês', 'icon' => 'coins',       'background' => 'blue',  'value' => Convert::toMonetario($value->est_entrada_mensal + $value->est_entrada_mensal_quarto + $value->venda_externa_mensal)]);
                $indicator3->enableSection('main', ['title' => 'Saida Mês', 'icon' => 'money-check-alt',       'background' => 'red',  'value' => Convert::toMonetario($value->est_saida_mensal)]);
                $indicator4->enableSection('main', ['title' => 'Luc. Liq Sem',   'icon' => 'hand-holding-usd',      'background' => 'orange',   'value' => Convert::toMonetario(($value->est_entrada_semanal + $value->est_entrada_semanal_quarto + $value->venda_externa_semanal) - ($value->est_saida_semanal))]);
                $indicator5->enableSection('main', ['title' => 'Luc. Liq Mês',    'icon' => 'receipt', 'background' => 'purple', 'value' => Convert::toMonetario(($value->est_entrada_mensal + $value->est_entrada_mensal_quarto + $value->venda_externa_mensal) - ($value->est_saida_mensal))]);
                $indicator6->enableSection('main', ['title' => 'Luc. Liq Anual', 'icon' => 'money-bill-wave',       'background' => 'green',  'value' => Convert::toMonetario(($value->est_entrada_anual  + $value->est_entrada_anual_quarto + $value->venda_externa_anual) - ($value->est_saida_anual))]);
            }
            
            
            $chart = new THtmlRenderer('app/resources/google_column_chart.html');
            $data[] = [ 'Mês', 'Cliente'];
        
            // média de ocupação mensal
            $meses = AppUtil::calendario();
            $objects = MapaReserva::getObjects();
            $data_count = [];
            if($objects){
                foreach ($objects as $key => $value) {
                    if(empty($data_count[date_parse($value->dtcadastro)['month']])){
                        $data_count[date_parse($value->dtcadastro)['month']] = 1 ;
                    }else{
                        $data_count[date_parse($value->dtcadastro)['month']] += 1 ;
                    }
                }
            }
            
            foreach($data_count as $key => $value){
                $data[] = [ Convert::rMes($key),   $value];
            }
            
            // replace the main section variables
            $chart->enableSection('main', array('data'   => json_encode($data),
                                    'width'  => '100%',
                                    'height'  => '300px',
                                    'title'  => 'Média de ocupação mensal',
                                    'ytitle' => 'Clientes', 
                                    'xtitle' => 'Mês',
                                    'uniqid' => uniqid()));
            
            $html->enableSection('main', ['indicator1' => $indicator1,
                                          'indicator2' => $indicator2,
                                          'indicator3' => $indicator3,
                                          'indicator4' => $indicator4,
                                          'indicator5' => $indicator5,
                                          'indicator6' => $indicator6,
                                          'chart1'     => $chart,
                                          'chart2'     => ''] );
            
            $container = new TVBox;
            $container->style = 'width: 100%';
            $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
            $container->add($html);
            
            parent::add($container);
            TTransaction::close();
        }
        catch (Exception $e)
        {
            parent::add($e->getMessage());
        }
    }
}
