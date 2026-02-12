<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:test-email',
    description: 'Test email sending directly'
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Testing Direct Email Send');

        try {
            $email = (new Email())
                ->from('noreply@searchengine.com')
                ->to('ruveyharuzgar.108@gmail.com')
                ->subject('🧪 Test Email from Search Engine')
                ->html('<h1>Test Email</h1><p>If you see this, email system is working!</p>');

            $this->mailer->send($email);
            
            $io->success('Email sent successfully!');
            $io->note('Check MailHog at: http://localhost:8025');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to send email: ' . $e->getMessage());
            $io->note('Error details: ' . $e->getTraceAsString());
            
            return Command::FAILURE;
        }
    }
}
