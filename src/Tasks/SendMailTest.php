<?php

declare(strict_types=1);

namespace Sunnysideup\EmailTest\Tasks;

use Override;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use SilverStripe\PolyExecution\PolyOutput;
use Exception;
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
    protected string $title = 'Test if emails are working';

    protected static string $commandName = 'testemail';

    #[Override]
    public function getOptions(): array
    {
        return [
            new InputOption('from', 'f', InputOption::VALUE_OPTIONAL, 'From email address'),
            new InputOption('to', 't', InputOption::VALUE_OPTIONAL, 'To email address'),
            new InputOption('subject', 's', InputOption::VALUE_OPTIONAL, 'Email subject', 'testing email'),
            new InputOption('message', 'm', InputOption::VALUE_OPTIONAL, 'Email message body', 'Message goes here'),
        ];
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

        $from    = $input->getOption('from')    ?: $adminEmail;
        $to      = $input->getOption('to')      ?: $adminEmail;
        $subject = $input->getOption('subject') ?: 'testing email';
        $message = $input->getOption('message') ?: 'Message goes here';

        $mailProvider = Injector::inst()->get(MailerInterface::class);

        $output->writeln('from: ' . Convert::raw2att((string) $from));
        $output->writeln('to: ' . Convert::raw2att((string) $to));
        $output->writeln('subject: ' . Convert::raw2att((string) $subject));
        $output->writeln('message: ' . Convert::raw2att((string) $message));
        $output->writeln('Change values like this: sake dev/tasks/testemail --to=a@b.com --from=c@d.com --subject=test --message=hello');

        $output->writeln('==========================');
        $output->writeln('Outcome');
        $output->writeln('==========================');

        // Attempt 1: raw PHP mail()
        $outcome = mail((string) $to, $subject . ' raw mail', (string) $message);
        $output->writeln('PHP mail sent: ' . ($outcome ? 'NO' : 'CHECK EMAIL TO VERIFY'));

        // Attempt 2: SilverStripe sendPlain()
        try {
            $email = Email::create($from, $to, $subject . ' silverstripe message', $message);
            $email->sendPlain();
            $outcome = true;
        } catch (Exception $exception) {
            $outcome = false;
            $output->writeForHtml('<div>Mail send error: <span style="color:red">' . $exception->getMessage() . '</span></div>');
        }

        $output->writeln('Silverstripe e-mail #1 sent: ' . ($outcome === false ? 'NO' : 'CHECK EMAIL TO VERIFY'));
        $output->writeln('Mail Service Provider: ' . $mailProvider::class);

        // Attempt 3: SilverStripe send() with text()
        $output->writeln('Attempt #2');

        $email = Email::create($from, $to, $subject);
        $email->text('My plain text email content');
        try {
            $email->send();
            $outcome = true;
        } catch (TransportExceptionInterface $transportException) {
            $outcome = false;
            $output->writeForHtml('<div>Mail send error: <span style="color:red">' . $transportException->getMessage() . '</span></div>');
        }

        $output->writeln('Silverstripe e-mail #2 sent: ' . ($outcome === false ? 'NO' : 'CHECK EMAIL TO VERIFY'));
        $output->writeln('Mail Service Provider: ' . $mailProvider::class);

        return Command::SUCCESS;
    }
}
