<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251014181001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename theme table to subject and update related columns and constraints';
    }

    public function up(Schema $schema): void
    {
        // Переименование таблицы theme в subject
        $this->addSql('RENAME TABLE theme TO subject');

        // Удаление старого внешнего ключа в таблице lesson
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F359027487');

        // Удаление старого индекса
        $this->addSql('DROP INDEX IDX_F87474F359027487 ON lesson');

        // Переименование столбца theme_id в subject_id
        $this->addSql('ALTER TABLE lesson CHANGE theme_id subject_id INT NOT NULL');

        // Создание нового индекса
        $this->addSql('CREATE INDEX IDX_F87474F323EDC87 ON lesson (subject_id)');

        // Создание нового внешнего ключа
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F323EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id)');
    }

    public function down(Schema $schema): void
    {
        // Переименование таблицы subject обратно в theme
        $this->addSql('RENAME TABLE subject TO theme');

        // Удаление нового внешнего ключа
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F323EDC87');

        // Удаление нового индекса
        $this->addSql('DROP INDEX IDX_F87474F323EDC87 ON lesson');

        // Переименование столбца subject_id обратно в theme_id
        $this->addSql('ALTER TABLE lesson CHANGE subject_id theme_id INT NOT NULL');

        // Создание старого индекса
        $this->addSql('CREATE INDEX IDX_F87474F359027487 ON lesson (theme_id)');

        // Восстановление старого внешнего ключа
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F359027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
    }
}