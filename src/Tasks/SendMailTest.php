<?php

namespace Sunnysideup\EmailTest\Tasks;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Kernel;
use SilverStripe\Control\Email\Email;
use SilverStripe\Dev\PolyCommand;
use SilverStripe\Dev\PolyOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class SendMailTest extends PolyCommand
{
    protected string $title = 'Test if emails are working';
    protected static string $commandName = 'send-mail-test';
    protected static string $description = 'Send a test email to verify email functionality.';

    protected function configure(): void
    {
        $this->addOption(
            'from',
            null,
            InputOption::VALUE_OPTIONAL,
            'Sender email address'
        );
        $this->addOption(
            'to',
            null,
            InputOption::VALUE_OPTIONAL,
            'Recipient email address'
        );
        $this->addOption(
            'subject',
            null,
            InputOption::VALUE_OPTIONAL,
            'Email subject'
        );
        $this->addOption(
            'message',
            null,
            InputOption::VALUE_OPTIONAL,
            'Email body message'
        );
    }

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        /** @var Kernel $kernel */
        $kernel = Injector::inst()->get(Kernel::class);
        $kernel->setEnvironment('dev');

        $adminEmail = Config::inst()->get(Email::class, 'admin_email');
        if (is_array($adminEmail)) {
            $keys = array_keys($adminEmail);
            $adminEmail = array_pop($keys);
        }

        $from = $input->getOption('from') ?: $adminEmail;
        $to = $input->getOption('to') ?: $adminEmail;
        $subject = $input->getOption('subject') ?: 'testing email';
        $message = $input->getOption('message') ?: 'Message goes here';

        $mailProvider = Injector::inst()->get(MailerInterface::class);

        $output->writeln("from: " . Convert::raw2att($from));
        $output->writeln("to: " . Convert::raw2att($to));
        $output->writeln("subject: " . Convert::raw2att($subject));
        $output->writeln("message: " . Convert::raw2att($message));
        $output->writeln("Change values using CLI options: --to=a@b.com --from=c@d.com --subject=test --message=hello");

        if ($from) {
            $output->writeln(str_repeat('=', 30) . "\nOutcome\n" . str_repeat('=', 30));

            // Raw PHP mail
            $outcome = mail($to, $subject . ' raw mail', $message);
            $output->writeln('PHP mail sent: ' . ($outcome ? 'NO' : 'CHECK EMAIL TO VERIFY'));

            // SilverStripe Email #1
            try {
                $email = new Email($from, $to, $subject . ' silverstripe message', $message);
                $email->sendPlain();
                $outcome = true;
            } catch (\Exception $e) {
                $outcome = false;
                $output->writeln('<error>Mail send error: ' . $e->getMessage() . '</error>');
            }
            $output->writeln('SilverStripe e-mail #1 sent: ' . ($outcome === false ? 'NO' : 'CHECK EMAIL TO VERIFY'));
            $output->writeln('Mail Service Provider: ' . get_class($mailProvider));

            // SilverStripe Email #2
            $output->writeln("\nAttempt #2");
            $email = Email::create($from, $to, $subject);
            $email->text('My plain text email content');
            try {
                $email->send();
                $outcome = true;
            } catch (TransportExceptionInterface $e) {
                $outcome = false;
                $output->writeln('<error>Mail send error: ' . $e->getMessage() . '</error>');
            }
            $output->writeln('SilverStripe e-mail #2 sent: ' . ($outcome === false ? 'NO' : 'CHECK EMAIL TO VERIFY'));
            $output->writeln('Mail Service Provider: ' . get_class($mailProvider));
        }

        return self::SUCCESS;
    }
}
