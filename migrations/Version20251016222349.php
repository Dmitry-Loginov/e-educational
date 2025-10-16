<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251016222349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer CHANGE path_image answer_fle_path VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE lesson DROP test');
        $this->addSql('ALTER TABLE subject CHANGE group_id group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE subject RENAME INDEX idx_9775e708a76ed395 TO IDX_FBCE3E7AA76ED395');
        $this->addSql('ALTER TABLE subject RENAME INDEX idx_9775e708fe54d947 TO IDX_FBCE3E7AFE54D947');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer CHANGE answer_fle_path path_image VARCHAR(500) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE lesson ADD test LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE subject CHANGE group_id group_id INT NOT NULL');
        $this->addSql('ALTER TABLE subject RENAME INDEX idx_fbce3e7afe54d947 TO IDX_9775E708FE54D947');
        $this->addSql('ALTER TABLE subject RENAME INDEX idx_fbce3e7aa76ed395 TO IDX_9775E708A76ED395');
    }
}
