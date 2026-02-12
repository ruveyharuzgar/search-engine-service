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
    name: 'app:send-real-email',
    description: 'Send a real email to test the system'
)]
class SendRealEmailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Sending Real Email');
        $io->text('To: ruveyharuzgar.108@gmail.com');

        try {
            $email = (new Email())
                ->from('noreply@searchengine.com')
                ->to('ruveyharuzgar.108@gmail.com')
                ->subject('🔔 Test Notification from Search Engine')
                ->html($this->buildEmailHtml());

            $io->text('Attempting to send email...');
            
            $this->mailer->send($email);
            
            $io->success('✅ Email sent successfully!');
            $io->note([
                'Check your inbox: ruveyharuzgar.108@gmail.com',
                'Also check MailHog: http://localhost:8025',
                'Check spam folder if not in inbox'
            ]);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('❌ Failed to send email!');
            $io->text('Error: ' . $e->getMessage());
            $io->text('Class: ' . get_class($e));
            
            if ($e->getPrevious()) {
                $io->text('Previous: ' . $e->getPrevious()->getMessage());
            }
            
            return Command::FAILURE;
        }
    }

    private function buildEmailHtml(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: #28a745; color: white; padding: 20px; border-radius: 5px 5px 0 0;">
            <h2 style="margin: 0;">🔔 Search Engine Notification</h2>
        </div>
        <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px;">
            <div style="padding: 15px; background: #f8f9fa; border-left: 4px solid #28a745; margin-bottom: 20px;">
                <h3>Test Email Başarılı!</h3>
                <p>Bu email, Search Engine bildirim sisteminin çalıştığını göstermektedir.</p>
                <p><strong>Sistem Özellikleri:</strong></p>
                <ul>
                    <li>✅ Email bildirimleri aktif</li>
                    <li>✅ SMS bildirimleri (simüle)</li>
                    <li>✅ Veritabanı entegrasyonu</li>
                    <li>✅ Çoklu kanal desteği</li>
                </ul>
            </div>
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666;">
                <p>Bu otomatik bir test bildirimidir.</p>
                <p>Zaman: {$this->getCurrentTime()}</p>
                <p>Email: ruveyharuzgar.108@gmail.com</p>
                <p>Telefon: +90 552 365 0801</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getCurrentTime(): string
    {
        return (new \DateTime())->format('Y-m-d H:i:s');
    }
}
