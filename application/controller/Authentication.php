<?php namespace app\controller;

use \Exception;
use \app\model\UserModel;

class Authentication extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $get = $this->request->getQueryParams();
        $url = $this->request->getUri()->getBaseUrl();

        $this->view
        ->addCss($url . '/assets/dist/css/authentication.css')
        ->addJs($url . '/assets/dist/js/authentication.js');

        if (isset($get['redirected'])) {
            if ($get['redirected']) {
                $this->view->setResponse($this->response->withStatus(401));
            }
        }

        return $this->view->render('login.twig');
    }

    public function signin()
    {
        try {
            if (strtolower($this->request->getMethod()) != "post") {
                throw new Exception('Request method invalid');
            }

            $post = $this->request->getParsedBody();

            $this->auth->attempt($post['email'], $post['password']);

            return $this->response->withJson([
                'message' => 'Login berhasil!'
            ]);
        } catch (Exception $err) {
            return $this->response->withStatus(500)->withJson([
                'message' => $err->getMessage()
            ]);
        }
    }

    public function signout()
    {
        $this->auth->release();

        return $this->response->withRedirect($this->url . '/authentication');
    }

    public function reset()
    {
        $get = $this->request->getQueryParams();
        $url = $this->request->getUri()->getBaseUrl();

        $this->view
        ->addCss($url . '/assets/dist/css/authentication.css')
        ->addJs($url . '/assets/dist/js/authentication.js');

        return $this->view->render('reset-password.twig');
    }

    public function submitReset()
    {
        try {
            if (strtolower($this->request->getMethod()) != "post") {
                throw new Exception('Request method invalid');
            }

            $post = $this->request->getParsedBody();

            $this->auth->recover($post['email']);

            $this->flash->addMessage('reset', 'Check your inbox email anda, apabila alamat email anda terdaftar anda akan menerima password sementara.');
        } catch (Exception $err) {
            $this->flash->addMessage('error', $err->getMessage());
        }

        return $this->response->withRedirect($this->url . '/authentication');
    }
}
