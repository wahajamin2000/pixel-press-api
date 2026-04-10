<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * SIMPLE APPROACH: Break down into clear steps
     */
    public function up(): void
    {
        // STEP 1: Make product fields nullable
        DB::statement('ALTER TABLE order_items MODIFY product_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE order_items MODIFY product_name VARCHAR(255) NULL');
        DB::statement('ALTER TABLE order_items MODIFY product_sku VARCHAR(255) NULL');

        // STEP 2: Add type column if not exists
        if (!Schema::hasColumn('order_items', 'type')) {
            DB::statement("
                ALTER TABLE order_items
                ADD COLUMN type VARCHAR(255) NOT NULL DEFAULT 'product'
                COMMENT 'Type of item: product or gangsheet'
                AFTER product_id
            ");
        }

        // STEP 3: Update existing records
        if (Schema::hasColumn('order_items', 'is_gangsheet_item')) {
            DB::statement("UPDATE order_items SET type = 'gangsheet' WHERE is_gangsheet_item = 1");
        }

        // STEP 4: Drop is_gangsheet_item column if exists
        if (Schema::hasColumn('order_items', 'is_gangsheet_item')) {
            DB::statement('ALTER TABLE order_items DROP COLUMN is_gangsheet_item');
        }

        // STEP 5: Handle existing gangsheet_id column
        if (Schema::hasColumn('order_items', 'gangsheet_id')) {
            // Get foreign key name
            $fkResult = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'order_items'
                AND COLUMN_NAME = 'gangsheet_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            // Drop foreign key if exists
            if (!empty($fkResult)) {
                $fkName = $fkResult[0]->CONSTRAINT_NAME;
                DB::statement("ALTER TABLE order_items DROP FOREIGN KEY `{$fkName}`");
                echo "Dropped foreign key: {$fkName}\n";
            }

            // Get index name
            $idxResult = DB::select("
                SELECT DISTINCT INDEX_NAME
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'order_items'
                AND COLUMN_NAME = 'gangsheet_id'
                AND INDEX_NAME != 'PRIMARY'
            ");

            // Drop index if exists
            if (!empty($idxResult)) {
                $idxName = $idxResult[0]->INDEX_NAME;
                try {
                    DB::statement("ALTER TABLE order_items DROP INDEX `{$idxName}`");
                    echo "Dropped index: {$idxName}\n";
                } catch (\Exception $e) {
                    // Index might have been dropped with foreign key
                    echo "Index already dropped or doesn't exist\n";
                }
            }

            // Drop the column
            DB::statement('ALTER TABLE order_items DROP COLUMN gangsheet_id');
        }

        // STEP 6: Add new gangsheet_id with foreign key
        DB::statement("
            ALTER TABLE order_items
            ADD COLUMN gangsheet_id BIGINT UNSIGNED NULL
            COMMENT 'Reference to gangsheet record'
            AFTER type
        ");

        // Add foreign key constraint
        DB::statement("
            ALTER TABLE order_items
            ADD CONSTRAINT order_items_gangsheet_id_foreign
            FOREIGN KEY (gangsheet_id)
            REFERENCES gangsheet_products(id)
            ON DELETE SET NULL
        ");

        // STEP 7: Add index on type column
        $typeIndexExists = DB::select("
            SELECT COUNT(*) as count
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'order_items'
            AND INDEX_NAME = 'order_items_type_index'
        ");

        if ($typeIndexExists[0]->count == 0) {
            DB::statement('ALTER TABLE order_items ADD INDEX order_items_type_index (type)');
        }

        echo "Migration completed successfully!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key
        DB::statement('ALTER TABLE order_items DROP FOREIGN KEY order_items_gangsheet_id_foreign');

        // Drop index on type
        DB::statement('ALTER TABLE order_items DROP INDEX order_items_type_index');

        // Drop columns
        DB::statement('ALTER TABLE order_items DROP COLUMN type');
        DB::statement('ALTER TABLE order_items DROP COLUMN gangsheet_id');

        // Restore old columns
        DB::statement("
            ALTER TABLE order_items
            ADD COLUMN is_gangsheet_item TINYINT(1) NOT NULL DEFAULT 0
            AFTER product_id
        ");

        DB::statement("
            ALTER TABLE order_items
            ADD COLUMN gangsheet_id BIGINT UNSIGNED NULL
            AFTER is_gangsheet_item
        ");

        // Make product fields required again
        DB::statement('ALTER TABLE order_items MODIFY product_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE order_items MODIFY product_name VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE order_items MODIFY product_sku VARCHAR(255) NOT NULL');
    }
};
