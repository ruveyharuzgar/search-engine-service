<?php

namespace App\Command;

use App\Service\SearchService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sync-contents',
    description: 'Provider\'lardan içerikleri çeker ve veritabanına kaydeder'
)]
class SyncContentsCommand extends Command
{
    public function __construct(
        private SearchService $searchService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('İçerik Senkronizasyonu Başlatılıyor');
        
        try {
            $count = $this->searchService->syncContents();
            
            $io->success(sprintf('%d içerik başarıyla senkronize edildi!', $count));
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Senkronizasyon sırasında hata oluştu: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}
