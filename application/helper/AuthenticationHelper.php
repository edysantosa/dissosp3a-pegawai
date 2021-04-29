<?php namespace app\helper;

use \app\model\UserModel as User;
use \app\model\AgentModel;
use \app\helper\EmailHelper;
use \Exception;

class AuthenticationHelper
{
    private $container;

    final public function __construct($container)
    {
        $this->container = $container;
    }

    public function attempt($email, $password)
    {
        $user = User::with('group')
        ->where('email', $email)->first();

        if (!$user) {
            throw new Exception('Akun tidak ditemukan');
        }

        $user->makeVisible('password');
        if (!password_verify($password, $user->password)) {
            // throw new Exception('Password invalid');
        }

        if ($user->status != 1 && $user->status != 2  && $user->status != 3) {
            throw new Exception('Your account can\'t be authenticated');
        }

        $session = $this->container->session;

        if (empty($user->image)) {
            $image = $this->container->baseUrl . '/assets/dist/images/placeholder.jpg';
        } else {
            $image = $this->container->baseUrl . '/assets/images/user/' . $user->image;
        }

        $arrSession = [
            'userId' => $user->userId,
            'email' => $user->email,
            'name' => $user->name,
            'status' => $user->status,
            'groupId' => $user->groupId,
            'position' => $user->group->groupName,
            'image' => $image
        ];
        $session->set('user', $arrSession);
        return true;
    }

    public function recover($email)
    {
        $user = User::where('email', $email)
                    ->whereIn('status', [1, 2])
                    ->first();

        if($user){
            // Generate random password
            $newPassword = '123456'; //$this->randomPassword(8);
            $hashPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            User::where('userId', $user->userId)->update([
                'password' => $hashPassword
            ]);

            // Send email forgot password
            $email = new EmailHelper();
            $content = trim(sprintf('
                
                <p>Anda meminta reset password dari sistem NAMA_SISTEM. Silahkan login dengan password baru di bawah, dan segera ubah password sesuai keinginan anda di menu ubah password
                </p>
                <b><i>%s</i></b>
            
            ', $newPassword));
            $email->send([$user->email] , [], [], [], "Password baru", $content);
            return true;
        }else{
            return false;
        }
    }

    public function newPassword($userId)
    {
        $user = User::where('userId', $userId)->first();

        if($user){
            // Generate random password
            $newPassword = $this->randomPassword(8);
            $hashPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            User::where('userId', $user->userId)->update([
                'password' => $hashPassword
            ]);

            // Send email
            $email = new EmailHelper();
            $content = trim(sprintf('
                
                <p>Akun baru sudah dibuat untuk sistem NAMA_SISTEM. Silahkan login dengan password di bawah, dan segera ubah password sesuai keinginan anda di menu ubah password
                </p>
                <b><i>%s</i></b>
            
            ', $newPassword));
            $email->send([$user->email] , [], [], [], "New Password", $content);
            return true;
        }else{
            return false;
        }
    }

    private function randomPassword($length = 8)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $password;
    }

    public function recognized()
    {
        $session = $this->container->session;

        if (!$session->exists('user')) {
            return false;
        } else {
            if ($session->user['status'] == 1 || $session->user['status'] == 2 || $session->user['status'] == 3) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function release()
    {
        $this->container->session->destroy();
    }
}
