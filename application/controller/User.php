<?php namespace app\controller;

use \Exception;
use \app\model\UserModel;
use \app\model\GroupModel;
use \app\helper\GetSetHelper;

class User extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        
        return $this->view
            ->addCss($this->url . '/assets/dist/css/user.css')
            ->addJs($this->url . '/assets/dist/js/user.js')

            ->render('user.twig');
    }

    public function add()
    {
        return $this->view
            ->addCss($this->url . '/assets/dist/css/user-edit.css')
            ->addJs($this->url . '/assets/dist/js/user-edit.js')

            ->render('userEdit.twig', [
                'group'   => GroupModel::where('groupId', '<>', 10)->get()
            ]);
    }

    public function edit($id)
    {
        $user = UserModel::with(['group'])
        ->where('userId', $id)->first();

            // return $this->response->withJson([
            //     'message' => $user
            // ]);

        if (!$user) {
            throw new \Slim\Exception\NotFoundException($this->request, $this->response);
        }

        return $this->view
            ->addCss($this->url . '/assets/dist/css/user-edit.css')
            ->addJs($this->url . '/assets/dist/js/user-edit.js')

            ->render('userEdit.twig', [
                'user'   => $user,
                'group'   => GroupModel::where('groupId', '<>', 10)->get()
            ]);
    }

    public function loadData()
    {
        $get = $this->request->getQueryParams();
        $url = $this->request->getUri()->getBaseUrl();

        $start  = (int)$get['start'];
        $length = (int)$get['length'];
        $page   = ($start / $length) + 1;
        $sort   = $get['order'];
        $search = $get['search'];
        $result = ['status' => true, 'draw' => $get['draw'], 'data' => []];

        $q = UserModel::with(['group'])
        ->where('groupId', '<>', 10)
        ->where('status', '<>', 0);
        $result['recordsTotal'] = $q->count();
        
        if ($sort) {
            $sort = explode("-", $sort);
            $field = $sort[0];
            $sortType = $sort[1];
            $q->orderBy($field, $sortType);
        }

        if ($search['value']) {
            $searches = explode(',', $search['value']);
            $q->where(function ($q) use ($searches) {
                foreach ($searches as $search) {
                    $search = trim($search);
                    $q->orWhere('User.name', 'like', '%'.$search.'%');
                    $q->orWhere('User.email', 'like', '%'.$search.'%');
                }
            });
            $q->orWhereHas('group', function ($query) use ($searches) {
                foreach ($searches as $search) {
                    $query->where('groupName', 'like', '%'.$search.'%');
                }
            });
        }
        $result['recordsFiltered'] = $q->count();

        $tableData = $q->take($length)->skip($start)->get();

        $result['data'] = $tableData;
        foreach ($tableData as $tData) {
            $tData->sequence = ++$start;
        }
        
        return json_encode($result);
    }

    public function submit()
    {
        try {
            if (strtolower($this->request->getMethod()) != "post") {
                throw new Exception('Request method invalid');
            }

            $post = $this->request->getParsedBody();

            $this->database->getConnection()->getPdo()->beginTransaction();

            switch ($post['task']) {
                case 'delete':
                    UserModel::whereIn('userId', $post['ids'])->update(['status' => 0]);
                    $message = 'User deleted';
                    break;

                case 'save':
                    $user = new UserModel;
                    $user->name = $post['name'];
                    $user->email = $post['email'];
                    $user->groupId = $post['position'];
                    $user->status = 1;

                    $user->save();

                    $this->auth->newPassword($user->userId);

                    $message = 'User updated';
                    break;

                case 'update':
                    $user = UserModel::where('userId', $post['id'])->first();
                    $user->name = $post['name'];
                    $user->email = $post['email'];
                    $user->groupId = $post['position'];
                    $user->save();

                    $message = 'User updated';
                    break;

                default:
                    throw new Exception('Invalid request');
                    break;
            }

            $this->database->getConnection()->getPdo()->commit();

            return $this->response->withJson([
                'message' => $message
            ]);
        } catch (Exception $err) {
            $this->database->getConnection()->getPdo()->rollBack();
            return $this->response->withStatus(500)->withJson([
                'message' => $err->getMessage()
            ]);
        }
    }
}
