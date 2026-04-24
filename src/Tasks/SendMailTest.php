<?php

namespace Sunnysideup\EmailTest\Tasks;

use Exception;
use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Kernel;
use SilverStripe\Dev\BuildTask;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

/**
 * @internal
 * @coversNothing
 */
class SendMailTest extends BuildTask
{
    protected $title = 'Test if emails are working';

    private static $segment = 'testemail';

    public function run($request)
    {
        /** @var Kernel $kernel */
        $kernel = Injector::inst()->get(Kernel::class);
        $kernel->setEnvironment('dev');

        $adminEmail = Config::inst()->get(Email::class, 'admin_email');
        if (is_array($adminEmail)) {
            $keys = array_keys($adminEmail);
            $adminEmail = array_pop($keys);
        }

        $from = $request->requestVar('from') ?: $adminEmail;
        $to = $request->requestVar('to') ?: $adminEmail;
        $subject = $request->requestVar('subject') ?: 'testing email';
        $message = $request->requestVar('message') ?: 'Message goes here';
        $mailProvider = Injector::inst()->get(MailerInterface::class);
        if (Director::is_cli()) {
            echo '

from: ' . Convert::raw2att($from) . '

to: ' . Convert::raw2att($to) . '

subject:' . Convert::raw2att($subject) . '" /><br/><br/>

message: ' . Convert::raw2att($message) . '

Change values like this: sake dev/tasks/testemail to=a@b.com from=c@d.com subject=test message=hello
            ';
        } else {
            echo '
                <style>
                    input {width: 80vw; max-width: 500px; padding: 5px;}
                </style>
                <form action="" method="' . $this->formMethod() . '">
                    from: <br/><input name="from" value="' . Convert::raw2att($from) . '" /><br/><br/>
                    to: <br/><input name="to" value="' . Convert::raw2att($to) . '" /><br/><br/>
                    subject: <br/><input name="subject" value="' . Convert::raw2att($subject) . '" /><br/><br/>
                    message: <br/><input name="message" value="' . Convert::raw2att($message) . '" /><br/><br/>
                    <input type="submit" />
                </form>
            ';
        }

        if ($request->requestVar('from')) {
            if (Director::is_cli()) {
                echo '
==========================
Outcome
==========================
                ';
            } else {
                echo '<h1>Outcome</h1>';
            }

            $outcome = mail((string) $to, $subject . ' raw mail', (string) $message);
            echo 'PHP mail sent: ' . ($outcome ? 'NO' : 'CHECK EMAIL TO VERIFY') . $this->newLine();

            try {
                $email = Email::create($from, $to, $subject . ' silverstripe message', $message);
                $email->sendPlain();
                $outcome = true;
            } catch (Exception $e) {
                $outcome = false;
                echo '<div>Mail send error: <span style="color:red">' . $e->getMessage() . '</span></div>' . $this->newLine();
            }

            echo 'Silverstripe e-mail #1 sent: ' . ($outcome === false ? 'NO' : 'CHECK EMAIL TO VERIFY') . $this->newLine();
            echo 'Mail Service Provider: ' . $mailProvider::class . $this->newLine();
            echo '<h2>Attempt #2</h2>';
            $email = Email::create($from, $to, $subject);
            $email->text('My plain text email content');
            try {
                $email->send();
                $outcome = true;
            } catch (TransportExceptionInterface $e) {
                $outcome = false;
                echo '<div>Mail send error: <span style="color:red">' . $e->getMessage() . '</span></div>' . $this->newLine();
            }

            echo 'Silverstripe e-mail #1 sent: ' . ($outcome === false ? 'NO' : 'CHECK EMAIL TO VERIFY') . $this->newLine();
            echo 'Mail Service Provider: ' . $mailProvider::class . $this->newLine();
        }
    }

    protected function newLine(): string
    {
        if (Director::is_cli()) {
            return '

            ';
        }

        return '<br /><br />';
    }

    protected function formMethod(): string
    {
        if (Director::is_cli()) {
            return 'get';
        }

        return 'post';
    }
}
