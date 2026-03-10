<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260310142816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity_syncs (id BINARY(16) NOT NULL, external_id VARCHAR(255) NOT NULL, raw_data JSON NOT NULL, synced_at DATETIME NOT NULL, sync_type VARCHAR(255) NOT NULL, integration_account_id BINARY(16) NOT NULL, training_day_id BINARY(16) DEFAULT NULL, workout_session_id BINARY(16) DEFAULT NULL, INDEX IDX_B5F04A6FD4F46039 (integration_account_id), INDEX IDX_B5F04A6F3A2E8649 (training_day_id), INDEX IDX_B5F04A6FD1BA355 (workout_session_id), UNIQUE INDEX uq_sync_account_external (integration_account_id, external_id, sync_type), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cardio_entries (id BINARY(16) NOT NULL, cardio_type VARCHAR(255) NOT NULL, duration_seconds INT NOT NULL, distance_meters NUMERIC(8, 2) DEFAULT NULL, avg_speed_kmh NUMERIC(5, 2) DEFAULT NULL, incline_pct NUMERIC(4, 1) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, avg_heart_rate INT DEFAULT NULL, calories_burned INT DEFAULT NULL, workout_session_id BINARY(16) NOT NULL, weekly_cardio_plan_id BINARY(16) DEFAULT NULL, INDEX IDX_48A4C621D1BA355 (workout_session_id), INDEX IDX_48A4C62129351E23 (weekly_cardio_plan_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE exercise_blocks (id BINARY(16) NOT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(255) NOT NULL, sort_order INT NOT NULL, session_template_id BINARY(16) NOT NULL, INDEX IDX_BBA20013F98311CF (session_template_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE exercise_entries (id BINARY(16) NOT NULL, sort_order INT NOT NULL, notes LONGTEXT DEFAULT NULL, workout_session_id BINARY(16) NOT NULL, exercise_id BINARY(16) NOT NULL, planned_exercise_id BINARY(16) DEFAULT NULL, INDEX IDX_F7ED4480D1BA355 (workout_session_id), INDEX IDX_F7ED4480E934951A (exercise_id), INDEX IDX_F7ED44801151D3F3 (planned_exercise_id), INDEX idx_exercise_entry_workout_session (workout_session_id, sort_order), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE exercises (id BINARY(16) NOT NULL, name VARCHAR(200) NOT NULL, description LONGTEXT DEFAULT NULL, instructions LONGTEXT DEFAULT NULL, video_url VARCHAR(500) DEFAULT NULL, equipment VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE exercise_primary_muscles (exercise_id BINARY(16) NOT NULL, muscle_group_id BINARY(16) NOT NULL, INDEX IDX_B02B59C1E934951A (exercise_id), INDEX IDX_B02B59C144004D0 (muscle_group_id), PRIMARY KEY (exercise_id, muscle_group_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE exercise_secondary_muscles (exercise_id BINARY(16) NOT NULL, muscle_group_id BINARY(16) NOT NULL, INDEX IDX_5FBAD6C8E934951A (exercise_id), INDEX IDX_5FBAD6C844004D0 (muscle_group_id), PRIMARY KEY (exercise_id, muscle_group_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE integration_accounts (id BINARY(16) NOT NULL, provider VARCHAR(255) NOT NULL, external_user_id VARCHAR(255) DEFAULT NULL, access_token LONGTEXT DEFAULT NULL, refresh_token LONGTEXT DEFAULT NULL, token_expires_at DATETIME DEFAULT NULL, scopes JSON NOT NULL, is_active TINYINT DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id BINARY(16) NOT NULL, INDEX IDX_D8125B46A76ED395 (user_id), UNIQUE INDEX uq_integration_user_provider (user_id, provider), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE mesocycles (id BINARY(16) NOT NULL, name VARCHAR(200) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, num_weeks INT NOT NULL, objective LONGTEXT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, step_goal_training_day INT NOT NULL, step_goal_rest_day INT NOT NULL, created_at DATETIME NOT NULL, user_id BINARY(16) NOT NULL, INDEX IDX_4D3CA3A5A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE metabolic_entries (id BINARY(16) NOT NULL, week_number INT DEFAULT NULL, rounds_completed INT DEFAULT NULL, time_seconds INT DEFAULT NULL, result LONGTEXT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, workout_session_id BINARY(16) NOT NULL, weekly_metabolic_plan_id BINARY(16) DEFAULT NULL, INDEX IDX_6C770339D1BA355 (workout_session_id), INDEX IDX_6C770339C53247E5 (weekly_metabolic_plan_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE muscle_groups (id BINARY(16) NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_113C5C255E237E06 (name), UNIQUE INDEX UNIQ_113C5C25989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE planned_exercises (id BINARY(16) NOT NULL, sort_order INT NOT NULL, notes LONGTEXT DEFAULT NULL, is_superset TINYINT DEFAULT 0 NOT NULL, superset_group VARCHAR(2) DEFAULT NULL, exercise_block_id BINARY(16) NOT NULL, exercise_id BINARY(16) NOT NULL, INDEX IDX_8AE58D95EF8122E4 (exercise_block_id), INDEX IDX_8AE58D95E934951A (exercise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE planned_sets (id BINARY(16) NOT NULL, sort_order INT NOT NULL, set_type VARCHAR(255) NOT NULL, reps_min INT DEFAULT NULL, reps_max INT DEFAULT NULL, rir INT DEFAULT NULL, rir_to_failure TINYINT DEFAULT 0 NOT NULL, rest_seconds INT DEFAULT NULL, weight_modifier VARCHAR(255) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, planned_exercise_id BINARY(16) NOT NULL, INDEX IDX_2FD5284C1151D3F3 (planned_exercise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE session_templates (id BINARY(16) NOT NULL, name VARCHAR(200) NOT NULL, type VARCHAR(255) NOT NULL, sort_order INT NOT NULL, notes LONGTEXT DEFAULT NULL, mesocycle_id BINARY(16) NOT NULL, INDEX IDX_2CCA388412C12F16 (mesocycle_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE set_entries (id BINARY(16) NOT NULL, sort_order INT NOT NULL, weight_kg NUMERIC(6, 2) NOT NULL, reps_completed INT NOT NULL, rir_actual INT DEFAULT NULL, to_failure TINYINT DEFAULT 0 NOT NULL, duration_seconds INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, exercise_entry_id BINARY(16) NOT NULL, planned_set_id BINARY(16) DEFAULT NULL, INDEX IDX_AA545522BC5AE9AA (exercise_entry_id), INDEX IDX_AA545522E7175FF2 (planned_set_id), INDEX idx_set_entry_exercise_entry (exercise_entry_id, sort_order), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE training_days (id BINARY(16) NOT NULL, date DATE NOT NULL, type VARCHAR(255) NOT NULL, step_goal INT NOT NULL, steps_actual INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, sleep_hours NUMERIC(4, 2) DEFAULT NULL, resting_heart_rate INT DEFAULT NULL, total_calories_day INT DEFAULT NULL, user_id BINARY(16) NOT NULL, INDEX IDX_E47EBB25A76ED395 (user_id), INDEX idx_training_day_date (date), UNIQUE INDEX uq_training_day_user_date (user_id, date), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id BINARY(16) NOT NULL, email VARCHAR(180) NOT NULL, name VARCHAR(100) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE weekly_cardio_plans (id BINARY(16) NOT NULL, week_number INT NOT NULL, format_type VARCHAR(255) NOT NULL, duration_minutes INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, intervals JSON NOT NULL, exercise_block_id BINARY(16) NOT NULL, INDEX IDX_E3723524EF8122E4 (exercise_block_id), UNIQUE INDEX uq_cardio_block_week (exercise_block_id, week_number), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE weekly_metabolic_plans (id BINARY(16) NOT NULL, week_number INT NOT NULL, format_type VARCHAR(255) NOT NULL, duration_minutes INT DEFAULT NULL, total_rounds INT DEFAULT NULL, rest_between_rounds_seconds INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, exercises JSON NOT NULL, exercise_block_id BINARY(16) NOT NULL, INDEX IDX_233A343DEF8122E4 (exercise_block_id), UNIQUE INDEX uq_metabolic_block_week (exercise_block_id, week_number), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE workout_sessions (id BINARY(16) NOT NULL, started_at DATETIME NOT NULL, finished_at DATETIME DEFAULT NULL, perceived_effort INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, total_calories_burned INT DEFAULT NULL, avg_heart_rate INT DEFAULT NULL, max_heart_rate INT DEFAULT NULL, training_day_id BINARY(16) NOT NULL, session_template_id BINARY(16) DEFAULT NULL, INDEX IDX_421170A5F98311CF (session_template_id), INDEX idx_workout_session_training_day (training_day_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE activity_syncs ADD CONSTRAINT FK_B5F04A6FD4F46039 FOREIGN KEY (integration_account_id) REFERENCES integration_accounts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activity_syncs ADD CONSTRAINT FK_B5F04A6F3A2E8649 FOREIGN KEY (training_day_id) REFERENCES training_days (id)');
        $this->addSql('ALTER TABLE activity_syncs ADD CONSTRAINT FK_B5F04A6FD1BA355 FOREIGN KEY (workout_session_id) REFERENCES workout_sessions (id)');
        $this->addSql('ALTER TABLE cardio_entries ADD CONSTRAINT FK_48A4C621D1BA355 FOREIGN KEY (workout_session_id) REFERENCES workout_sessions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cardio_entries ADD CONSTRAINT FK_48A4C62129351E23 FOREIGN KEY (weekly_cardio_plan_id) REFERENCES weekly_cardio_plans (id)');
        $this->addSql('ALTER TABLE exercise_blocks ADD CONSTRAINT FK_BBA20013F98311CF FOREIGN KEY (session_template_id) REFERENCES session_templates (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_entries ADD CONSTRAINT FK_F7ED4480D1BA355 FOREIGN KEY (workout_session_id) REFERENCES workout_sessions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_entries ADD CONSTRAINT FK_F7ED4480E934951A FOREIGN KEY (exercise_id) REFERENCES exercises (id)');
        $this->addSql('ALTER TABLE exercise_entries ADD CONSTRAINT FK_F7ED44801151D3F3 FOREIGN KEY (planned_exercise_id) REFERENCES planned_exercises (id)');
        $this->addSql('ALTER TABLE exercise_primary_muscles ADD CONSTRAINT FK_B02B59C1E934951A FOREIGN KEY (exercise_id) REFERENCES exercises (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_primary_muscles ADD CONSTRAINT FK_B02B59C144004D0 FOREIGN KEY (muscle_group_id) REFERENCES muscle_groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_secondary_muscles ADD CONSTRAINT FK_5FBAD6C8E934951A FOREIGN KEY (exercise_id) REFERENCES exercises (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_secondary_muscles ADD CONSTRAINT FK_5FBAD6C844004D0 FOREIGN KEY (muscle_group_id) REFERENCES muscle_groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE integration_accounts ADD CONSTRAINT FK_D8125B46A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mesocycles ADD CONSTRAINT FK_4D3CA3A5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE metabolic_entries ADD CONSTRAINT FK_6C770339D1BA355 FOREIGN KEY (workout_session_id) REFERENCES workout_sessions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE metabolic_entries ADD CONSTRAINT FK_6C770339C53247E5 FOREIGN KEY (weekly_metabolic_plan_id) REFERENCES weekly_metabolic_plans (id)');
        $this->addSql('ALTER TABLE planned_exercises ADD CONSTRAINT FK_8AE58D95EF8122E4 FOREIGN KEY (exercise_block_id) REFERENCES exercise_blocks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE planned_exercises ADD CONSTRAINT FK_8AE58D95E934951A FOREIGN KEY (exercise_id) REFERENCES exercises (id)');
        $this->addSql('ALTER TABLE planned_sets ADD CONSTRAINT FK_2FD5284C1151D3F3 FOREIGN KEY (planned_exercise_id) REFERENCES planned_exercises (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session_templates ADD CONSTRAINT FK_2CCA388412C12F16 FOREIGN KEY (mesocycle_id) REFERENCES mesocycles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE set_entries ADD CONSTRAINT FK_AA545522BC5AE9AA FOREIGN KEY (exercise_entry_id) REFERENCES exercise_entries (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE set_entries ADD CONSTRAINT FK_AA545522E7175FF2 FOREIGN KEY (planned_set_id) REFERENCES planned_sets (id)');
        $this->addSql('ALTER TABLE training_days ADD CONSTRAINT FK_E47EBB25A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE weekly_cardio_plans ADD CONSTRAINT FK_E3723524EF8122E4 FOREIGN KEY (exercise_block_id) REFERENCES exercise_blocks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE weekly_metabolic_plans ADD CONSTRAINT FK_233A343DEF8122E4 FOREIGN KEY (exercise_block_id) REFERENCES exercise_blocks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workout_sessions ADD CONSTRAINT FK_421170A53A2E8649 FOREIGN KEY (training_day_id) REFERENCES training_days (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE workout_sessions ADD CONSTRAINT FK_421170A5F98311CF FOREIGN KEY (session_template_id) REFERENCES session_templates (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity_syncs DROP FOREIGN KEY FK_B5F04A6FD4F46039');
        $this->addSql('ALTER TABLE activity_syncs DROP FOREIGN KEY FK_B5F04A6F3A2E8649');
        $this->addSql('ALTER TABLE activity_syncs DROP FOREIGN KEY FK_B5F04A6FD1BA355');
        $this->addSql('ALTER TABLE cardio_entries DROP FOREIGN KEY FK_48A4C621D1BA355');
        $this->addSql('ALTER TABLE cardio_entries DROP FOREIGN KEY FK_48A4C62129351E23');
        $this->addSql('ALTER TABLE exercise_blocks DROP FOREIGN KEY FK_BBA20013F98311CF');
        $this->addSql('ALTER TABLE exercise_entries DROP FOREIGN KEY FK_F7ED4480D1BA355');
        $this->addSql('ALTER TABLE exercise_entries DROP FOREIGN KEY FK_F7ED4480E934951A');
        $this->addSql('ALTER TABLE exercise_entries DROP FOREIGN KEY FK_F7ED44801151D3F3');
        $this->addSql('ALTER TABLE exercise_primary_muscles DROP FOREIGN KEY FK_B02B59C1E934951A');
        $this->addSql('ALTER TABLE exercise_primary_muscles DROP FOREIGN KEY FK_B02B59C144004D0');
        $this->addSql('ALTER TABLE exercise_secondary_muscles DROP FOREIGN KEY FK_5FBAD6C8E934951A');
        $this->addSql('ALTER TABLE exercise_secondary_muscles DROP FOREIGN KEY FK_5FBAD6C844004D0');
        $this->addSql('ALTER TABLE integration_accounts DROP FOREIGN KEY FK_D8125B46A76ED395');
        $this->addSql('ALTER TABLE mesocycles DROP FOREIGN KEY FK_4D3CA3A5A76ED395');
        $this->addSql('ALTER TABLE metabolic_entries DROP FOREIGN KEY FK_6C770339D1BA355');
        $this->addSql('ALTER TABLE metabolic_entries DROP FOREIGN KEY FK_6C770339C53247E5');
        $this->addSql('ALTER TABLE planned_exercises DROP FOREIGN KEY FK_8AE58D95EF8122E4');
        $this->addSql('ALTER TABLE planned_exercises DROP FOREIGN KEY FK_8AE58D95E934951A');
        $this->addSql('ALTER TABLE planned_sets DROP FOREIGN KEY FK_2FD5284C1151D3F3');
        $this->addSql('ALTER TABLE session_templates DROP FOREIGN KEY FK_2CCA388412C12F16');
        $this->addSql('ALTER TABLE set_entries DROP FOREIGN KEY FK_AA545522BC5AE9AA');
        $this->addSql('ALTER TABLE set_entries DROP FOREIGN KEY FK_AA545522E7175FF2');
        $this->addSql('ALTER TABLE training_days DROP FOREIGN KEY FK_E47EBB25A76ED395');
        $this->addSql('ALTER TABLE weekly_cardio_plans DROP FOREIGN KEY FK_E3723524EF8122E4');
        $this->addSql('ALTER TABLE weekly_metabolic_plans DROP FOREIGN KEY FK_233A343DEF8122E4');
        $this->addSql('ALTER TABLE workout_sessions DROP FOREIGN KEY FK_421170A53A2E8649');
        $this->addSql('ALTER TABLE workout_sessions DROP FOREIGN KEY FK_421170A5F98311CF');
        $this->addSql('DROP TABLE activity_syncs');
        $this->addSql('DROP TABLE cardio_entries');
        $this->addSql('DROP TABLE exercise_blocks');
        $this->addSql('DROP TABLE exercise_entries');
        $this->addSql('DROP TABLE exercises');
        $this->addSql('DROP TABLE exercise_primary_muscles');
        $this->addSql('DROP TABLE exercise_secondary_muscles');
        $this->addSql('DROP TABLE integration_accounts');
        $this->addSql('DROP TABLE mesocycles');
        $this->addSql('DROP TABLE metabolic_entries');
        $this->addSql('DROP TABLE muscle_groups');
        $this->addSql('DROP TABLE planned_exercises');
        $this->addSql('DROP TABLE planned_sets');
        $this->addSql('DROP TABLE session_templates');
        $this->addSql('DROP TABLE set_entries');
        $this->addSql('DROP TABLE training_days');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE weekly_cardio_plans');
        $this->addSql('DROP TABLE weekly_metabolic_plans');
        $this->addSql('DROP TABLE workout_sessions');
    }
}
