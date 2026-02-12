<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260212000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create notification_users table for notification system';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE notification_users (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            notification_channels JSON NOT NULL,
            notification_types JSON NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_notification_users_email (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE notification_users');
    }
}
