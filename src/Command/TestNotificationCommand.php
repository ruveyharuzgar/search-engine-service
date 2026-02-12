<?php

namespace App\Command;

use App\Service\NotificationManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-notification',
    description: 'Test notification system by sending test notifications'
)]
class TestNotificationCommand extends Command
{
    public function __construct(
        private NotificationManager $notificationManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Notification type (success, error, warning, info)', 'success');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $type = $input->getOption('type');

        $io->title('Testing Notification System');

        $messages = [
            'success' => 'Test success notification - Everything is working perfectly!',
            'error' => 'Test error notification - Something went wrong in the system!',
            'warning' => 'Test warning notification - Please check the system configuration.',
            'info' => 'Test info notification - System maintenance scheduled for tonight.'
        ];

        $message = $messages[$type] ?? $messages['success'];

        $io->info("Sending {$type} notification...");

        match($type) {
            'success' => $this->notificationManager->success($message, ['test' => true, 'timestamp' => time()]),
            'error' => $this->notificationManager->error($message, ['test' => true, 'error_code' => 500]),
            'warning' => $this->notificationManager->warning($message, ['test' => true, 'severity' => 'medium']),
            'info' => $this->notificationManager->info($message, ['test' => true, 'category' => 'maintenance']),
            default => $this->notificationManager->success($message)
        };

        $io->success('Notification sent successfully!');
        $io->note([
            'Check your email at: ruveyharuzgar.108@gmail.com',
            'Check MailHog UI at: http://localhost:8025',
            'SMS was simulated (check logs)'
        ]);

        return Command::SUCCESS;
    }
}
