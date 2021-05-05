<?php namespace app\controller;

use \Exception;
use \app\model\UserModel;
use PHPImageWorkshop\ImageWorkshop;

class Profile extends Base
{
    public function __construct()
    {
        parent::__construct();
    }


    public function index()
    {
        $user = UserModel::with('group')
        ->where('userId', $this->session->user['userId'])->first();

        if (!$user) {
            throw new \Slim\Exception\NotFoundException($this->request, $this->response);
        }

        return $this->view
            ->addCss($this->url . '/assets/dist/css/profile.css')
            ->addJs($this->url . '/assets/dist/js/profile.js')

            ->render('userProfile.twig', [
                'user'   => $user
            ]);
    }

    public function submit()
    {

        try {
            if (strtolower($this->request->getMethod()) != "post") {
                throw new Exception('Request method invalid');
            }

            $post = $this->request->getParsedBody();

            switch ($post['task']) {
                case 'image':
                    return $this->uploadImage();
                    break;
                case 'update':
                    $user = UserModel::where('userId', $post['id'])->first();
                    $user->name = $post['name'];
                    $user->email = $post['email'];

                    if ($post['password']) {
                        $hashPassword = password_hash($post['password'], PASSWORD_DEFAULT);
                        $user->password = $hashPassword;
                    }

                    $user->save();
                    $message = 'Profil diupdate';
                    break;

                default:
                    throw new Exception('Invalid request');
                    break;
            }

            return $this->response->withJson([
                'message' => $message
            ]);
        } catch (Exception $err) {
            return $this->response->withStatus(500)->withJson([
                'message' => $err->getMessage()
            ]);
        }
    }


    private function uploadImage()
    {
        $post = $this->request->getParsedBody();
        try {
            $uploadedFiles = $this->request->getUploadedFiles();
            $imagePath = __DIR__ . '/../../public/assets/images/user/';

            if (!file_exists($imagePath)) {
                mkdir($imagePath, 0777, true);
            }

            $uploadedFile = $uploadedFiles['image'];
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $filename = $this->moveUploadedFile($imagePath, $uploadedFile);
            } else {
                throw new Exception('Gagal upload gambar.');
            }

            UserModel::where('userId', $post['id'])->update(['image' => $filename]);
            $message = 'Profile image updated';

            $oldSession = $this->session->user;
            $oldSession['image'] = $this->url . '/assets/images/user/' . $filename;
            $this->session->set('user', $oldSession);

            return $this->response->withJson([
                'message' => $message,
                'image' => $this->url . '/assets/images/user/' . $filename
            ]);
        } catch (Exception $err) {
            return $this->response->withStatus(500)->withJson([
                'message' => $err->getMessage()
            ]);
        }
    }

    private function moveUploadedFile($directory, $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        // $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        // RESIZE GAMBAR
        $gambar = ImageWorkshop::initFromPath($uploadedFile->file);
        $gambar->cropMaximumInPercent(0, 0, 'MM');
        $gambar->resizeInPixel(400, 400, true);
        $gambar->save($directory, $filename, true, null, 100);

        return $filename;
    }
}
