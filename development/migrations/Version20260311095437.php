<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260311095437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity_syncs (id UUID NOT NULL, external_id VARCHAR(255) NOT NULL, raw_data JSON NOT NULL, synced_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, sync_type VARCHAR(255) NOT NULL, integration_account_id UUID NOT NULL, training_day_id UUID DEFAULT NULL, workout_session_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_B5F04A6FD4F46039 ON activity_syncs (integration_account_id)');
        $this->addSql('CREATE INDEX IDX_B5F04A6F3A2E8649 ON activity_syncs (training_day_id)');
        $this->addSql('CREATE INDEX IDX_B5F04A6FD1BA355 ON activity_syncs (workout_session_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_sync_account_external ON activity_syncs (integration_account_id, external_id, sync_type)');
        $this->addSql('CREATE TABLE cardio_entries (id UUID NOT NULL, cardio_type VARCHAR(255) NOT NULL, duration_seconds INT NOT NULL, distance_meters NUMERIC(8, 2) DEFAULT NULL, avg_speed_kmh NUMERIC(5, 2) DEFAULT NULL, incline_pct NUMERIC(4, 1) DEFAULT NULL, notes TEXT DEFAULT NULL, avg_heart_rate INT DEFAULT NULL, calories_burned INT DEFAULT NULL, workout_session_id UUID NOT NULL, weekly_cardio_plan_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_48A4C621D1BA355 ON cardio_entries (workout_session_id)');
        $this->addSql('CREATE INDEX IDX_48A4C62129351E23 ON cardio_entries (weekly_cardio_plan_id)');
        $this->addSql('CREATE TABLE exercise_blocks (id UUID NOT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(255) NOT NULL, sort_order INT NOT NULL, session_template_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_BBA20013F98311CF ON exercise_blocks (session_template_id)');
        $this->addSql('CREATE TABLE exercise_entries (id UUID NOT NULL, sort_order INT NOT NULL, notes TEXT DEFAULT NULL, workout_session_id UUID NOT NULL, exercise_id UUID NOT NULL, planned_exercise_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_F7ED4480D1BA355 ON exercise_entries (workout_session_id)');
        $this->addSql('CREATE INDEX IDX_F7ED4480E934951A ON exercise_entries (exercise_id)');
        $this->addSql('CREATE INDEX IDX_F7ED44801151D3F3 ON exercise_entries (planned_exercise_id)');
        $this->addSql('CREATE INDEX idx_exercise_entry_workout_session ON exercise_entries (workout_session_id, sort_order)');
        $this->addSql('CREATE TABLE exercises (id UUID NOT NULL, name VARCHAR(200) NOT NULL, description TEXT DEFAULT NULL, instructions TEXT DEFAULT NULL, video_url VARCHAR(500) DEFAULT NULL, equipment VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE exercise_primary_muscles (exercise_id UUID NOT NULL, muscle_group_id UUID NOT NULL, PRIMARY KEY (exercise_id, muscle_group_id))');
        $this->addSql('CREATE INDEX IDX_B02B59C1E934951A ON exercise_primary_muscles (exercise_id)');
        $this->addSql('CREATE INDEX IDX_B02B59C144004D0 ON exercise_primary_muscles (muscle_group_id)');
        $this->addSql('CREATE TABLE exercise_secondary_muscles (exercise_id UUID NOT NULL, muscle_group_id UUID NOT NULL, PRIMARY KEY (exercise_id, muscle_group_id))');
        $this->addSql('CREATE INDEX IDX_5FBAD6C8E934951A ON exercise_secondary_muscles (exercise_id)');
        $this->addSql('CREATE INDEX IDX_5FBAD6C844004D0 ON exercise_secondary_muscles (muscle_group_id)');
        $this->addSql('CREATE TABLE integration_accounts (id UUID NOT NULL, provider VARCHAR(255) NOT NULL, external_user_id VARCHAR(255) DEFAULT NULL, access_token TEXT DEFAULT NULL, refresh_token TEXT DEFAULT NULL, token_expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, scopes JSON NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_D8125B46A76ED395 ON integration_accounts (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_integration_user_provider ON integration_accounts (user_id, provider)');
        $this->addSql('CREATE TABLE mesocycles (id UUID NOT NULL, name VARCHAR(200) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, num_weeks INT NOT NULL, objective TEXT DEFAULT NULL, notes TEXT DEFAULT NULL, step_goal_training_day INT NOT NULL, step_goal_rest_day INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_4D3CA3A5A76ED395 ON mesocycles (user_id)');
        $this->addSql('CREATE TABLE metabolic_entries (id UUID NOT NULL, week_number INT DEFAULT NULL, rounds_completed INT DEFAULT NULL, time_seconds INT DEFAULT NULL, result TEXT DEFAULT NULL, notes TEXT DEFAULT NULL, workout_session_id UUID NOT NULL, weekly_metabolic_plan_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_6C770339D1BA355 ON metabolic_entries (workout_session_id)');
        $this->addSql('CREATE INDEX IDX_6C770339C53247E5 ON metabolic_entries (weekly_metabolic_plan_id)');
        $this->addSql('CREATE TABLE muscle_groups (id UUID NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_113C5C255E237E06 ON muscle_groups (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_113C5C25989D9B62 ON muscle_groups (slug)');
        $this->addSql('CREATE TABLE planned_exercises (id UUID NOT NULL, sort_order INT NOT NULL, notes TEXT DEFAULT NULL, is_superset BOOLEAN DEFAULT false NOT NULL, superset_group VARCHAR(2) DEFAULT NULL, exercise_block_id UUID NOT NULL, exercise_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_8AE58D95EF8122E4 ON planned_exercises (exercise_block_id)');
        $this->addSql('CREATE INDEX IDX_8AE58D95E934951A ON planned_exercises (exercise_id)');
        $this->addSql('CREATE TABLE planned_sets (id UUID NOT NULL, sort_order INT NOT NULL, set_type VARCHAR(255) NOT NULL, reps_min INT DEFAULT NULL, reps_max INT DEFAULT NULL, rir INT DEFAULT NULL, rir_to_failure BOOLEAN DEFAULT false NOT NULL, rest_seconds INT DEFAULT NULL, weight_modifier VARCHAR(255) DEFAULT NULL, notes TEXT DEFAULT NULL, planned_exercise_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_2FD5284C1151D3F3 ON planned_sets (planned_exercise_id)');
        $this->addSql('CREATE TABLE session_templates (id UUID NOT NULL, name VARCHAR(200) NOT NULL, type VARCHAR(255) NOT NULL, sort_order INT NOT NULL, notes TEXT DEFAULT NULL, mesocycle_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_2CCA388412C12F16 ON session_templates (mesocycle_id)');
        $this->addSql('CREATE TABLE set_entries (id UUID NOT NULL, sort_order INT NOT NULL, weight_kg NUMERIC(6, 2) NOT NULL, reps_completed INT NOT NULL, rir_actual INT DEFAULT NULL, to_failure BOOLEAN DEFAULT false NOT NULL, duration_seconds INT DEFAULT NULL, notes TEXT DEFAULT NULL, exercise_entry_id UUID NOT NULL, planned_set_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_AA545522BC5AE9AA ON set_entries (exercise_entry_id)');
        $this->addSql('CREATE INDEX IDX_AA545522E7175FF2 ON set_entries (planned_set_id)');
        $this->addSql('CREATE INDEX idx_set_entry_exercise_entry ON set_entries (exercise_entry_id, sort_order)');
        $this->addSql('CREATE TABLE training_days (id UUID NOT NULL, date DATE NOT NULL, type VARCHAR(255) NOT NULL, step_goal INT NOT NULL, steps_actual INT DEFAULT NULL, notes TEXT DEFAULT NULL, sleep_hours NUMERIC(4, 2) DEFAULT NULL, resting_heart_rate INT DEFAULT NULL, total_calories_day INT DEFAULT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_E47EBB25A76ED395 ON training_days (user_id)');
        $this->addSql('CREATE INDEX idx_training_day_date ON training_days (date)');
        $this->addSql('CREATE UNIQUE INDEX uq_training_day_user_date ON training_days (user_id, date)');
        $this->addSql('CREATE TABLE users (id UUID NOT NULL, email VARCHAR(180) NOT NULL, name VARCHAR(100) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE TABLE weekly_cardio_plans (id UUID NOT NULL, week_number INT NOT NULL, format_type VARCHAR(255) NOT NULL, duration_minutes INT DEFAULT NULL, description TEXT DEFAULT NULL, intervals JSON NOT NULL, exercise_block_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_E3723524EF8122E4 ON weekly_cardio_plans (exercise_block_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_cardio_block_week ON weekly_cardio_plans (exercise_block_id, week_number)');
        $this->addSql('CREATE TABLE weekly_metabolic_plans (id UUID NOT NULL, week_number INT NOT NULL, format_type VARCHAR(255) NOT NULL, duration_minutes INT DEFAULT NULL, total_rounds INT DEFAULT NULL, rest_between_rounds_seconds INT DEFAULT NULL, description TEXT DEFAULT NULL, exercises JSON NOT NULL, exercise_block_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_233A343DEF8122E4 ON weekly_metabolic_plans (exercise_block_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_metabolic_block_week ON weekly_metabolic_plans (exercise_block_id, week_number)');
        $this->addSql('CREATE TABLE workout_sessions (id UUID NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, finished_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, perceived_effort INT DEFAULT NULL, notes TEXT DEFAULT NULL, total_calories_burned INT DEFAULT NULL, avg_heart_rate INT DEFAULT NULL, max_heart_rate INT DEFAULT NULL, training_day_id UUID NOT NULL, session_template_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_421170A5F98311CF ON workout_sessions (session_template_id)');
        $this->addSql('CREATE INDEX idx_workout_session_training_day ON workout_sessions (training_day_id)');
        $this->addSql('ALTER TABLE activity_syncs ADD CONSTRAINT FK_B5F04A6FD4F46039 FOREIGN KEY (integration_account_id) REFERENCES integration_accounts (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE activity_syncs ADD CONSTRAINT FK_B5F04A6F3A2E8649 FOREIGN KEY (training_day_id) REFERENCES training_days (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE activity_syncs ADD CONSTRAINT FK_B5F04A6FD1BA355 FOREIGN KEY (workout_session_id) REFERENCES workout_sessions (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE cardio_entries ADD CONSTRAINT FK_48A4C621D1BA355 FOREIGN KEY (workout_session_id) REFERENCES workout_sessions (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE cardio_entries ADD CONSTRAINT FK_48A4C62129351E23 FOREIGN KEY (weekly_cardio_plan_id) REFERENCES weekly_cardio_plans (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE exercise_blocks ADD CONSTRAINT FK_BBA20013F98311CF FOREIGN KEY (session_template_id) REFERENCES session_templates (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE exercise_entries ADD CONSTRAINT FK_F7ED4480D1BA355 FOREIGN KEY (workout_session_id) REFERENCES workout_sessions (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE exercise_entries ADD CONSTRAINT FK_F7ED4480E934951A FOREIGN KEY (exercise_id) REFERENCES exercises (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE exercise_entries ADD CONSTRAINT FK_F7ED44801151D3F3 FOREIGN KEY (planned_exercise_id) REFERENCES planned_exercises (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE exercise_primary_muscles ADD CONSTRAINT FK_B02B59C1E934951A FOREIGN KEY (exercise_id) REFERENCES exercises (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_primary_muscles ADD CONSTRAINT FK_B02B59C144004D0 FOREIGN KEY (muscle_group_id) REFERENCES muscle_groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_secondary_muscles ADD CONSTRAINT FK_5FBAD6C8E934951A FOREIGN KEY (exercise_id) REFERENCES exercises (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_secondary_muscles ADD CONSTRAINT FK_5FBAD6C844004D0 FOREIGN KEY (muscle_group_id) REFERENCES muscle_groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE integration_accounts ADD CONSTRAINT FK_D8125B46A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE mesocycles ADD CONSTRAINT FK_4D3CA3A5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE metabolic_entries ADD CONSTRAINT FK_6C770339D1BA355 FOREIGN KEY (workout_session_id) REFERENCES workout_sessions (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE metabolic_entries ADD CONSTRAINT FK_6C770339C53247E5 FOREIGN KEY (weekly_metabolic_plan_id) REFERENCES weekly_metabolic_plans (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE planned_exercises ADD CONSTRAINT FK_8AE58D95EF8122E4 FOREIGN KEY (exercise_block_id) REFERENCES exercise_blocks (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE planned_exercises ADD CONSTRAINT FK_8AE58D95E934951A FOREIGN KEY (exercise_id) REFERENCES exercises (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE planned_sets ADD CONSTRAINT FK_2FD5284C1151D3F3 FOREIGN KEY (planned_exercise_id) REFERENCES planned_exercises (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE session_templates ADD CONSTRAINT FK_2CCA388412C12F16 FOREIGN KEY (mesocycle_id) REFERENCES mesocycles (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE set_entries ADD CONSTRAINT FK_AA545522BC5AE9AA FOREIGN KEY (exercise_entry_id) REFERENCES exercise_entries (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE set_entries ADD CONSTRAINT FK_AA545522E7175FF2 FOREIGN KEY (planned_set_id) REFERENCES planned_sets (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE training_days ADD CONSTRAINT FK_E47EBB25A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE weekly_cardio_plans ADD CONSTRAINT FK_E3723524EF8122E4 FOREIGN KEY (exercise_block_id) REFERENCES exercise_blocks (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE weekly_metabolic_plans ADD CONSTRAINT FK_233A343DEF8122E4 FOREIGN KEY (exercise_block_id) REFERENCES exercise_blocks (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE workout_sessions ADD CONSTRAINT FK_421170A53A2E8649 FOREIGN KEY (training_day_id) REFERENCES training_days (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE workout_sessions ADD CONSTRAINT FK_421170A5F98311CF FOREIGN KEY (session_template_id) REFERENCES session_templates (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity_syncs DROP CONSTRAINT FK_B5F04A6FD4F46039');
        $this->addSql('ALTER TABLE activity_syncs DROP CONSTRAINT FK_B5F04A6F3A2E8649');
        $this->addSql('ALTER TABLE activity_syncs DROP CONSTRAINT FK_B5F04A6FD1BA355');
        $this->addSql('ALTER TABLE cardio_entries DROP CONSTRAINT FK_48A4C621D1BA355');
        $this->addSql('ALTER TABLE cardio_entries DROP CONSTRAINT FK_48A4C62129351E23');
        $this->addSql('ALTER TABLE exercise_blocks DROP CONSTRAINT FK_BBA20013F98311CF');
        $this->addSql('ALTER TABLE exercise_entries DROP CONSTRAINT FK_F7ED4480D1BA355');
        $this->addSql('ALTER TABLE exercise_entries DROP CONSTRAINT FK_F7ED4480E934951A');
        $this->addSql('ALTER TABLE exercise_entries DROP CONSTRAINT FK_F7ED44801151D3F3');
        $this->addSql('ALTER TABLE exercise_primary_muscles DROP CONSTRAINT FK_B02B59C1E934951A');
        $this->addSql('ALTER TABLE exercise_primary_muscles DROP CONSTRAINT FK_B02B59C144004D0');
        $this->addSql('ALTER TABLE exercise_secondary_muscles DROP CONSTRAINT FK_5FBAD6C8E934951A');
        $this->addSql('ALTER TABLE exercise_secondary_muscles DROP CONSTRAINT FK_5FBAD6C844004D0');
        $this->addSql('ALTER TABLE integration_accounts DROP CONSTRAINT FK_D8125B46A76ED395');
        $this->addSql('ALTER TABLE mesocycles DROP CONSTRAINT FK_4D3CA3A5A76ED395');
        $this->addSql('ALTER TABLE metabolic_entries DROP CONSTRAINT FK_6C770339D1BA355');
        $this->addSql('ALTER TABLE metabolic_entries DROP CONSTRAINT FK_6C770339C53247E5');
        $this->addSql('ALTER TABLE planned_exercises DROP CONSTRAINT FK_8AE58D95EF8122E4');
        $this->addSql('ALTER TABLE planned_exercises DROP CONSTRAINT FK_8AE58D95E934951A');
        $this->addSql('ALTER TABLE planned_sets DROP CONSTRAINT FK_2FD5284C1151D3F3');
        $this->addSql('ALTER TABLE session_templates DROP CONSTRAINT FK_2CCA388412C12F16');
        $this->addSql('ALTER TABLE set_entries DROP CONSTRAINT FK_AA545522BC5AE9AA');
        $this->addSql('ALTER TABLE set_entries DROP CONSTRAINT FK_AA545522E7175FF2');
        $this->addSql('ALTER TABLE training_days DROP CONSTRAINT FK_E47EBB25A76ED395');
        $this->addSql('ALTER TABLE weekly_cardio_plans DROP CONSTRAINT FK_E3723524EF8122E4');
        $this->addSql('ALTER TABLE weekly_metabolic_plans DROP CONSTRAINT FK_233A343DEF8122E4');
        $this->addSql('ALTER TABLE workout_sessions DROP CONSTRAINT FK_421170A53A2E8649');
        $this->addSql('ALTER TABLE workout_sessions DROP CONSTRAINT FK_421170A5F98311CF');
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
