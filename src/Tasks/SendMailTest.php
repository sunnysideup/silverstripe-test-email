<?php

namespace Sunnysideup\EmailTest\Tasks;

use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Dev\BuildTask;

class SendMailTest extends BuildTask
{
    protected $title = 'Test if emails are working';

    private static $segment = 'testemail';

    public function run($request)
    {
        $from = $_GET['from'] ?? 'webmaster@' . Director::baseURL();
        $to = $_GET['to'] ?? 'support@sunnysideup.co.nz';
        $subject = $_GET['subject'] ?? 'testing email';
        $message = $_GET['message'] ?? 'Message goes here';
        echo 'from: ' . $from . $this->newLine();
        echo 'to: ' . $to . $this->newLine();
        echo 'subject: ' . $subject . $this->newLine();
        echo 'message: ' . $message . $this->newLine();

        $outcome = mail($to, $subject, $message);
        echo 'PHP mail sent: ' . ($outcome ? 'YES' : 'NO') . $this->newLine();
        $email = new Email($from, $to, $subject, $message);
        $outcome = $email->sendPlain();
        echo 'SS e-mail sent: ' . ($outcome ? 'YES' : 'NO') . $this->newLine();
    }

    protected function newLine()
    {
        if (Director::is_cli()) {
            return '

            ';
        }
        return '<br /><br />';
    }
}
