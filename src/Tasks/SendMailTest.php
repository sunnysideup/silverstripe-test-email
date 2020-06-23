<?php

namespace Sunnysideup\App\Tasks;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;

class SendEmailTest extends BuildTask
{
    private static $segment = 'sendmail';

    protected $title = 'Test if emails are working';

    public function run($request)
    {
        $from = $_GET['from'] ?? 'webmaster@'. Director::baseURL();
        $to = $_GET['to'] ?? 'support@sunnysideup.co.nz';
        $subject = $_GET['subject'] ?? 'testing email';
        $message = $_GET['message'] ?? 'Message goes here';
        echo 'from: '.$from .$this->newLine();
        echo 'to: '.$to .$this->newLine();
        echo 'subject: '.$subject .$this->newLine();
        echo 'message: '.$message .$this->newLine();

        $outcome = mail($to, $subject, $message);
        echo 'PHP mail sent: ' . ($outcome ? 'YES' : 'NO') .$this->newLine();
        $email = new Email($from, $to, $subject, $message);
        $outcome = $email->sendPlain();
        echo 'SS e-mail sent: ' . ($outcome ? 'YES' : 'NO') .$this->newLine();

    }

    protected function newLine()
    {
        if(Director::is_cli()) {
            return '

            ';
        } else {
            return '<br /><br />';
        }
    }
}
