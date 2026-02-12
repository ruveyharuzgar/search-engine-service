<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240315000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'İçerik tablosunu oluşturur';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contents (
            id VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            type VARCHAR(20) NOT NULL,
            metrics JSON NOT NULL,
            published_at DATETIME NOT NULL,
            tags JSON NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX idx_type (type),
            INDEX idx_published_at (published_at)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contents');
    }
}
