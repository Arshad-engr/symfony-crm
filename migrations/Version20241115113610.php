<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241115113610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `users` (id INT NOT NULL AUTO_INCREMENT, 
        PRIMARY KEY(id),first_name VARCHAR(50),last_name VARCHAR(50),
        email VARCHAR(50) NOT NULL,password LONGTEXT NOT NULL,address LONGTEXT,
        phone VARCHAR(50),dob DATETIME,
        profile LONGTEXT,roles TEXT,
        created_at DATETIME NOT NULL,
        updated_at DATETIME) CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
     
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `users`');
       
    }
}
