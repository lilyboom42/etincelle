<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241104184347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscriber DROP FOREIGN KEY FK_AD005B6971F7E88B');
        $this->addSql('DROP INDEX IDX_AD005B6971F7E88B ON subscriber');
        $this->addSql('ALTER TABLE subscriber DROP event_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscriber ADD event_id INT NOT NULL');
        $this->addSql('ALTER TABLE subscriber ADD CONSTRAINT FK_AD005B6971F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_AD005B6971F7E88B ON subscriber (event_id)');
    }
}
