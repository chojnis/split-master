<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250401004002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE currency_exchange (id INT AUTO_INCREMENT NOT NULL, from_currency VARCHAR(3) NOT NULL, to_currency VARCHAR(3) NOT NULL, date DATE NOT NULL, rate NUMERIC(10, 6) NOT NULL, INDEX currency_exchange_idx (from_currency, to_currency, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `group` ADD currency_id INT NOT NULL');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C538248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('CREATE INDEX IDX_6DC044C538248176 ON `group` (currency_id)');
        $this->addSql('ALTER TABLE transaction ADD exchange_rate NUMERIC(10, 6) NOT NULL, CHANGE amount amount NUMERIC(8, 2) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE currency_exchange');
        $this->addSql('ALTER TABLE transaction DROP exchange_rate, CHANGE amount amount NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY FK_6DC044C538248176');
        $this->addSql('DROP INDEX IDX_6DC044C538248176 ON `group`');
        $this->addSql('ALTER TABLE `group` DROP currency_id');
    }
}
