<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250829000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create content repository node graph and node type tables';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('cr_nodes')) {
            $nodes = $schema->createTable('cr_nodes');
            $nodes->addColumn('id', 'string', ['length' => 64, 'primary' => true]);
            $nodes->addColumn('workspace_id', 'string', ['length' => 64]);
            $nodes->addColumn('dimensions', 'string', ['length' => 512]);
            $nodes->addColumn('parent_id', 'string', ['length' => 64, 'notnull' => false]);
            $nodes->addColumn('segment', 'string', ['length' => 190, 'notnull' => false]);
            $nodes->addColumn('path', 'string', ['length' => 1024]);
            $nodes->addColumn('type', 'string', ['length' => 190]);
            $nodes->addColumn('properties', 'json', ['notnull' => false]);
            $nodes->addIndex(['workspace_id'], 'idx_cr_nodes_workspace');
            $nodes->addIndex(['type'], 'idx_cr_nodes_type');
            $nodes->addIndex(['path'], 'idx_cr_nodes_path');
            $nodes->addIndex(['parent_id'], 'idx_cr_nodes_parent');
        }

        if (!$schema->hasTable('cr_node_types')) {
            $types = $schema->createTable('cr_node_types');
            $types->addColumn('name', 'string', ['length' => 190, 'primary' => true]);
            $types->addColumn('properties_schema', 'json', ['notnull' => false]);
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('cr_nodes')) {
            $schema->dropTable('cr_nodes');
        }
        if ($schema->hasTable('cr_node_types')) {
            $schema->dropTable('cr_node_types');
        }
    }
}

