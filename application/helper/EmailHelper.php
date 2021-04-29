<?php namespace app\helper;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as phpmailerException;
use \Exception;
use \sys\Config;

class EmailHelper extends \sys\Helper
{
    protected $mail;
    public function __construct()
    {
        $config = static::$container['settings']['emailServer'];

        //Create a new PHPMailer instance
        $this->mail = new PHPMailer;

        if ($config['protocol'] == 'smtp') {
            //Tell PHPMailer to use SMTP
            $this->mail->isSMTP();
            //Whether to use SMTP authentication
            $this->mail->SMTPAuth = true;
        }

        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $this->mail->SMTPDebug = $config['smtpDebug'];

        //Set the hostname of the mail server
        $this->mail->Host = $config['smtpHost'];
        // use
        // $this->mail->Host = gethostbyname('smtp.gmail.com');
        // if your network does not support SMTP over IPv6

        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $this->mail->Port = $config['smtpPort'];

        //Set the encryption system to use - ssl (deprecated) or tls
        $this->mail->SMTPSecure = $config['smtpCrypto'];
          
        //Username to use for SMTP authentication - use full email address for gmail
        $this->mail->Username = $config['smtpUser'];

        //Password to use for SMTP authentication
        $this->mail->Password = $config['smtpPass'];

        // Set email format to HTML
        $this->mail->isHTML(true);

        //Set who the message is to be sent from
        $this->mail->setFrom($config['smtpUser'], $config['smtpName']);

        //Set an alternative reply-to address
        // $mail->addReplyTo('replyto@example.com', 'First Last');
    }

    /**
     * Fungsi mengirim email
     * @param  array  $recipient  Recipient list
     * @param  array  $cc         CC list
     * @param  array  $bcc        BCC list
     * @param  array  $attachment Path of file to be attached
     * @param  string $subject    Email subject
     * @param  string $content    Email content, preferably in html format
     * @return boolean            success or not
     */
    public function send($recipient = [], $cc = [], $bcc = [], $attachment = [], $subject = '', $content = '')
    {
        $mail = $this->mail;
        try {
            if (!is_array($recipient) || !is_array($cc) || !is_array($bcc)) {
                throw new Exception('Recipient, CC, or BCC should be array');
            } elseif (!is_string($subject) || !is_string($content)) {
                throw new Exception('Wrong value for subject or array');
            }

            // Content
            $mail->Subject = $subject;

            $body = sprintf('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                    <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                        <title>Mail</title>
                        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                    </head>
                    <body style="margin: 0; padding: 0;">
                    %s
                    </body>
                    </html>', $content);
            $mail->Body = $body;
            $mail->AltBody = strip_tags($content);
            
            // Attachments
            foreach ($attachment as $value) {
                $mail->addAttachment($value);
            }

            // Recipients
            $isTestEmail = static::$container->settings['emailServer']['isTestEmail'];
            if (!$isTestEmail) {
                foreach ($recipient as $value) {
                    $mail->addAddress($value);
                }
                foreach ($cc as $value) {
                    $mail->addCC($value);
                }
                foreach ($bcc as $value) {
                    $mail->addBCC($value);
                }
            } else {
                $mail->addAddress('celak.pande@gmail.com');
            }

            // var_dump($recipient);
            // die();
            // if ($mail->send()) {
            //     $this->saveMail($mail);
            //     return true;
            // } else {
            //     return false;
            // }
            return true;
        } catch (phpmailerException $e) {
            return $e->errorMessage();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    //Section 2: IMAP
    //IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
    //Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
    //You can use imap_getmailboxes($imapStream, '/imap/ssl') to get a list of available folders or labels, this can
    //be useful if you are trying to get this working on a non-Gmail IMAP server.
    private function saveMail($mail)
    {
        $config = static::$container['settings']['emailServer'];

        //You can change 'Sent Mail' to any other folder or tag
        // $path = "{imap.gmail.com:993/imap/ssl}[Gmail]/Surat Terkirim";
        $path = "{imap.gmail.com:993/imap/ssl}SYSTEMSENT";
        //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
        $imapStream = imap_open($path, $config['smtpUser'], $config['smtpPass']);
        $result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
        imap_close($imapStream);
        return $result;
    }

    // Ini untuk mendapatkan daftar mailbox di gmail
    private function getInboxList()
    {
        $config = static::$container['settings']['emailServer'];
        $path = "{imap.gmail.com:993/imap/ssl}";
        $imapStream = imap_open($path, $config['smtpUser'], $config['smtpPass']);
        return imap_getmailboxes($imapStream, "{imap.gmail.com:993/imap/ssl}", "*");
    }
}
