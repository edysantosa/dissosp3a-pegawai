<?php namespace app\controller;

use \app\model\MenuModel;
use \app\model\LogModel;

abstract class Base extends \sys\Controller
{
    protected $url;
    public function __construct()
    {
        $this->response
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache');

        $this->view->setResponse($this->response);

        $this->url = $url = $this->baseUrl;

        $this->sitebase->versionstamp = sha1('07-09-20:1');
        $this->sitebase->url = $this->baseUrl;
        $this->sitebase->controller = $this->mvc->controller;
        $this->sitebase->action = $this->mvc->action;
        $this->sitebase->groupid = $this->session->user['groupId'];
        $this->view->controller = [ $this->mvc->controller,  $this->mvc->action];


        $menu = MenuModel::with(['groupPermission', 'children' => function ($query) {
            $query->whereHas('groupPermission', function ($query) {
                $query->where('groupId', $this->session->user['groupId']);
            });
        }, 'children.groupPermission'])
        ->where('parentId', '=', 0)
        ->whereHas('groupPermission', function ($query) {
            $query->where('groupId', $this->session->user['groupId']);
        })->get()->groupBy('groupName');

        foreach ($menu as $key => $menuItem) {
            if (!empty($menuItem->children)) {
                foreach ($menuItem->children as $key => $child) {
                    if ($child->groupPermission->groupId != $this->session->user['groupId']) {
                        $menuItem->children->pull($key);
                    }
                }
            }
        }

        $breadCrumb = [];
        $topTitle='';
        if (strtolower($this->sitebase->controller) == "\app\controller\home") {
            $breadCrumb[] = ['name' => 'Home', 'path' => '', 'icon' => 'icon-home2'];
            $breadCrumb[] = 'Dashboard';
            $topTitle = 'Dashboard';
            $menu['main']['0']->active = true;
        } else if (strtolower($this->sitebase->controller) == "profile") {
            $breadCrumb[] = ['name' => 'My Account', 'path' => '', 'icon' => 'icon-user-plus'];
            $breadCrumb[] = 'My Profile';
            $topTitle = 'My Profile';
            $menu['main']['0']->active = true;
        }
        foreach ($menu as $key => $value) {
            foreach ($value as $menuItem) {
                if (!empty($menuItem->children->toArray())) {
                    foreach ($menuItem->children as $subMenu) {
                        if ($subMenu->controller == $this->sitebase->controller) {
                            $breadCrumb[] = $subMenu->toArray();
                            $menuItem->active=true;
                            $subMenu->active=true;
                            $topTitle = $subMenu->name;
                            break;
                        }
                    }
                } else {
                    if ($menuItem->controller == $this->sitebase->controller) {
                        $menuItem->active=true;
                        $breadCrumb[] = $menuItem->toArray();
                        $topTitle = $menuItem->name;
                        break;
                    }
                }
            }
        }
        $subTitle = '';
        if (strtolower($this->sitebase->action) != 'index') {
            $subTitle = implode(' ', preg_split('/(?=[A-Z])/', $this->sitebase->action));
             $breadCrumb[] = $subTitle;
             $subTitle = $subTitle;
        }

        // echo "<pre>";
        // var_dump($this->sitebase->action);
        // die();

        $this->view->setTwig('menu', $menu);
        $this->view->setTwig('breadCrumb', $breadCrumb);
        $this->view->setTwig('topTitle', $topTitle);
        $this->view->setTwig('subTitle', $subTitle);

        // Tampilkan 10 log terakhir
        $logs = LogModel::with(['user'])->orderBy('activityDate', 'DESC')->take(10)->get();
        $this->view->setTwig('logs', $logs);

        // echo '<pre>';
        // var_dump($this->session->user);
        // die();
    }
}
