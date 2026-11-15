<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * simplificar o enum de estado dos subscritores.
 *   confirmed   -> active
 *   unsubscribed -> cancelled
 *   pending     -> pending (inalterado)
 *
 * Compatibilidade: MySQL/MariaDB (produção e dev) e SQLite (testes).
 * A operação é reversível via down().
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql' || DB::connection()->getDriverName() === 'mariadb') {
            // Expandir o enum ANTES de escrever os novos valores (strict mode).
            DB::statement("ALTER TABLE `newsletter_subscribers` MODIFY COLUMN `status` ENUM('pending','confirmed','unsubscribed','active','cancelled') NOT NULL DEFAULT 'pending'");
            DB::statement("UPDATE `newsletter_subscribers` SET `status` = 'active' WHERE `status` = 'confirmed'");
            DB::statement("UPDATE `newsletter_subscribers` SET `status` = 'cancelled' WHERE `status` = 'unsubscribed'");
            DB::statement("ALTER TABLE `newsletter_subscribers` MODIFY COLUMN `status` ENUM('pending','active','cancelled') NOT NULL DEFAULT 'pending'");
            return;
        }

        // SQLite: reconstruir a tabela com a nova definição
        DB::statement('CREATE TABLE `newsletter_subscribers_new` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            `email` VARCHAR(190) NOT NULL UNIQUE,
            `token` VARCHAR(64) NOT NULL,
            `status` VARCHAR CHECK (`status` IN (\'pending\',\'active\',\'cancelled\')) NOT NULL DEFAULT \'pending\',
            `confirmed_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP NULL,
            `updated_at` TIMESTAMP NULL
        )');
        DB::statement("INSERT INTO `newsletter_subscribers_new` (id, email, token, status, confirmed_at, created_at, updated_at)
            SELECT id, email, token,
                CASE status WHEN 'confirmed' THEN 'active' WHEN 'unsubscribed' THEN 'cancelled' ELSE status END,
                confirmed_at, datetime(created_at), datetime(updated_at)
            FROM `newsletter_subscribers`");
        DB::statement('DROP TABLE `newsletter_subscribers`');
        DB::statement('ALTER TABLE `newsletter_subscribers_new` RENAME TO `newsletter_subscribers`');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql' || DB::connection()->getDriverName() === 'mariadb') {
            // Expandir o enum antes de escrever os valores antigos (strict mode).
            DB::statement("ALTER TABLE `newsletter_subscribers` MODIFY COLUMN `status` ENUM('pending','active','cancelled','confirmed','unsubscribed') NOT NULL DEFAULT 'pending'");
            DB::statement("UPDATE `newsletter_subscribers` SET `status` = 'confirmed' WHERE `status` = 'active'");
            DB::statement("UPDATE `newsletter_subscribers` SET `status` = 'unsubscribed' WHERE `status` = 'cancelled'");
            DB::statement("ALTER TABLE `newsletter_subscribers` MODIFY COLUMN `status` ENUM('pending','confirmed','unsubscribed') NOT NULL DEFAULT 'pending'");
            return;
        }

        DB::statement('CREATE TABLE `newsletter_subscribers_new` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            `email` VARCHAR(190) NOT NULL UNIQUE,
            `token` VARCHAR(64) NOT NULL,
            `status` VARCHAR CHECK (`status` IN (\'pending\',\'confirmed\',\'unsubscribed\')) NOT NULL DEFAULT \'pending\',
            `confirmed_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP NULL,
            `updated_at` TIMESTAMP NULL
        )');
        DB::statement("INSERT INTO `newsletter_subscribers_new` (id, email, token, status, confirmed_at, created_at, updated_at)
            SELECT id, email, token,
                CASE status WHEN 'active' THEN 'confirmed' WHEN 'cancelled' THEN 'unsubscribed' ELSE status END,
                confirmed_at, datetime(created_at), datetime(updated_at)
            FROM `newsletter_subscribers`");
        DB::statement('DROP TABLE `newsletter_subscribers`');
        DB::statement('ALTER TABLE `newsletter_subscribers_new` RENAME TO `newsletter_subscribers`');
    }
};
