<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250908162157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE section_has_tasks (id INT AUTO_INCREMENT NOT NULL, task_id INT NOT NULL, section_id INT NOT NULL, position INT DEFAULT NULL, INDEX IDX_C99C05AC8DB60186 (task_id), INDEX IDX_C99C05ACD823E37A (section_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE section_has_tasks ADD CONSTRAINT FK_C99C05AC8DB60186 FOREIGN KEY (task_id) REFERENCES `task` (id)');
        $this->addSql('ALTER TABLE section_has_tasks ADD CONSTRAINT FK_C99C05ACD823E37A FOREIGN KEY (section_id) REFERENCES `section` (id)');
        $this->addSql('ALTER TABLE section_task DROP FOREIGN KEY FK_D7C0372E8DB60186');
        $this->addSql('ALTER TABLE section_task DROP FOREIGN KEY FK_D7C0372ED823E37A');
        $this->addSql('DROP TABLE section_task');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE section_task (section_id INT NOT NULL, task_id INT NOT NULL, INDEX IDX_D7C0372E8DB60186 (task_id), INDEX IDX_D7C0372ED823E37A (section_id), PRIMARY KEY(section_id, task_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE section_task ADD CONSTRAINT FK_D7C0372E8DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE section_task ADD CONSTRAINT FK_D7C0372ED823E37A FOREIGN KEY (section_id) REFERENCES section (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE section_has_tasks DROP FOREIGN KEY FK_C99C05AC8DB60186');
        $this->addSql('ALTER TABLE section_has_tasks DROP FOREIGN KEY FK_C99C05ACD823E37A');
        $this->addSql('DROP TABLE section_has_tasks');
    }
}
