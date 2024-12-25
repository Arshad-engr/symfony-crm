<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241120104952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `tasks` (id INT NOT NULL AUTO_INCREMENT, 
        PRIMARY KEY(id),name TEXT NOT NULL,description LONGTEXT DEFAULT NULL,status ENUM("pending", "completed") DEFAULT NULL,
        user_id TEXT DEFAULT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME) CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

    }

    public function down(Schema $schema): void
    {
       // this down() migration is auto-generated, please modify it to your needs
       $this->addSql('DROP TABLE `tasks`');

    }
}
