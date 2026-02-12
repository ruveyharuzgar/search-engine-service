<?php

namespace App\Command;

use App\Entity\NotificationUser;
use App\Repository\NotificationUserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-notification-user',
    description: 'Add admin user for notifications'
)]
class AddNotificationUserCommand extends Command
{
    public function __construct(
        private NotificationUserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Adding Admin Notification User');

        // Check if user already exists
        $existingUser = $this->userRepository->findOneBy(['email' => 'ruveyharuzgar.108@gmail.com']);
        
        if ($existingUser) {
            $io->warning('User already exists!');
            return Command::SUCCESS;
        }

        // Create admin user
        $user = new NotificationUser();
        $user->setName('Rüveyha Rüzgar')
            ->setEmail('ruveyharuzgar.108@gmail.com')
            ->setPhone('+905523650801')
            ->setNotificationChannels(['email', 'sms'])
            ->setNotificationTypes(['error', 'success', 'warning', 'info'])
            ->setIsActive(true);

        $this->userRepository->save($user);

        $io->success('Admin notification user added successfully!');
        $io->table(
            ['Field', 'Value'],
            [
                ['Name', $user->getName()],
                ['Email', $user->getEmail()],
                ['Phone', $user->getPhone()],
                ['Channels', implode(', ', $user->getNotificationChannels())],
                ['Types', implode(', ', $user->getNotificationTypes())],
                ['Active', $user->isActive() ? 'Yes' : 'No'],
            ]
        );

        return Command::SUCCESS;
    }
}
