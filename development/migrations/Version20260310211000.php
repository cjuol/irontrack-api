<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds a unique constraint on (mesocycle_id, sort_order) in session_templates
 * to enforce at the DB level that a mesocycle cannot have two session templates
 * with the same sort order position.
 */
final class Version20260310211000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique constraint uq_session_template_mesocycle_sort_order on session_templates(mesocycle_id, sort_order)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE session_templates ADD UNIQUE INDEX uq_session_template_mesocycle_sort_order (mesocycle_id, sort_order)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE session_templates DROP INDEX uq_session_template_mesocycle_sort_order');
    }
}
