<?php namespace app\controller;

use \app\helper\GetSetHelper;
use \app\model\RouteScheduleExtraSeatModel;
use \app\model\RouteModel;
use \Carbon\Carbon;

class Home extends Base
{
    public function index()
    {
        $get = $this->request->getQueryParams();
        $url = $this->request->getUri()->getBaseUrl();

        $this->view
        ->addCss($url . '/assets/dist/css/home.css')
        ->addJs($url . '/assets/dist/js/home.js');

        return $this->view->render('home.twig');
    }
}
