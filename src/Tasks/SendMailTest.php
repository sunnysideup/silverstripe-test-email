<?php

namespace Sunnysideup\EmailTest\Tasks;

use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Convert;
use SilverStripe\Dev\BuildTask;

class SendMailTest extends BuildTask
{
    protected $title = 'Test if emails are working';

    private static $segment = 'testemail';

    public function run($request)
    {
        $from = $request->getVar('from') ?? 'webmaster@' . Director::host();
        $to = $request->getVar('to') ?? 'support@' . Director::host();
        $subject = $request->getVar('subject') ?? 'testing email';
        $message = $request->getVar('message') ?? 'Message goes here';

        echo '
            <style>
                input {width: 80vw; max-width: 500px; padding: 5px;}
            </style>
            <form action="" method="get">
                from: <br/><input name="from" value="' . Convert::raw2att($from) . '" /><br/><br/>
                to: <br/><input name="to" value="' . Convert::raw2att($to) . '" /><br/><br/>
                subject: <br/><input name="subject" value="' . Convert::raw2att($subject) . '" /><br/><br/>
                message: <br/><input name="message" value="' . Convert::raw2att($message) . '" /><br/><br/>
                <input type="submit" />
            </form>
        ';
        if(isset($_GET['from'])) {
            echo '<h1>Outcome</h1>';
            $outcome = mail($to, $subject, $message);
            echo 'PHP mail sent: ' . ($outcome ? 'YES' : 'NO') . $this->newLine();
            $email = new Email($from, $to, $subject, $message);
            $outcome = $email->sendPlain();
            echo 'Silverstripe e-mail sent: ' . ($outcome ? 'YES' : 'NO') . $this->newLine();
        }
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
