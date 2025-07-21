<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250721153921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE section ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE section ADD CONSTRAINT FK_2D737AEFA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_2D737AEFA76ED395 ON section (user_id)');
        $this->addSql('ALTER TABLE task ADD section_id INT NOT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25D823E37A FOREIGN KEY (section_id) REFERENCES section (id)');
        $this->addSql('CREATE INDEX IDX_527EDB25D823E37A ON task (section_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE section DROP FOREIGN KEY FK_2D737AEFA76ED395');
        $this->addSql('DROP INDEX IDX_2D737AEFA76ED395 ON section');
        $this->addSql('ALTER TABLE section DROP user_id');
        $this->addSql('ALTER TABLE `task` DROP FOREIGN KEY FK_527EDB25D823E37A');
        $this->addSql('DROP INDEX IDX_527EDB25D823E37A ON `task`');
        $this->addSql('ALTER TABLE `task` DROP section_id');
    }
}
