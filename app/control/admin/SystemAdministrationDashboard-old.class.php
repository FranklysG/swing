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
            
            $today = date('Y-m-d');
            $week_open = date('Y-m-d', strtotime('-1 week'));
            $month_open = date('Y-m-01');
            $year_open = date('Y-01-01');
            
            // mapa_reservas semanais
            $repositoy_week = new TRepository('MapaReserva');
            $criteria_week = new TCriteria;
            $criteria_week->add(new TFilter('date(dtcadastro)', 'between', "{$week_open}", "{$today}"));
            $sum_week = $repositoy_week->sum($criteria_week, ['valor' => 'valor_quarto']);

            // mapa_reservas mensais
            $repositoy_month = new TRepository('MapaReserva');
            $criteria_month = new TCriteria;
            $criteria_month->add(new TFilter('date(dtcadastro)', 'between', "{$month_open}", "{$today}"));
            $sum_month = $repositoy_month->sum($criteria_month, ['valor' => 'valor_quarto']);
            
            // mapa_reservas mensais
            $repositoy_year = new TRepository('MapaReserva');
            $criteria_year = new TCriteria;
            $criteria_year->add(new TFilter('date(dtcadastro)', 'between', "{$year_open}", "{$today}"));
            $sum_year = $repositoy_year->sum($criteria_year, ['valor' => 'valor_quarto']);
            
            $indicator1->enableSection('main', ['title' => 'Ocupados Hoje',    'icon' => 'users',       'background' => 'orange', 'value' => MapaReserva::where('date(dtcadastro)','=',date('Y-m-d'))->count()]);
            $indicator2->enableSection('main', ['title' => 'Extimativa faturamento semanal',   'icon' => 'money-bill-wave',      'background' => 'blue',   'value' => Convert::toMonetario($sum_week)]);
            $indicator3->enableSection('main', ['title' => 'Extimativa faturamento mensal',    'icon' => 'receipt', 'background' => 'purple', 'value' => Convert::toMonetario($sum_month)]);
            $indicator4->enableSection('main', ['title' => 'Extimativa faturamento anaul', 'icon' => 'wallet',       'background' => 'green',  'value' => Convert::toMonetario($sum_year)]);
            
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
