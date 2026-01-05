<?php

use App\Models\DataSource;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('data_elements', function (Blueprint $table) {
            $table->dropColumn([
                'owner_team',
                'quality_rules_ref',
                'catalog_column_id',
                'cde_category',
                'pii_flag',
                'personal_data_category',
                'special_category_flag',
            ]);
            $table->string('data_steward')->nullable()->after('business_definition');
            $table->string('status')->nullable()->after('data_steward');
            $table->foreignIdFor(DataSource::class)->nullable()->after('status')->constrained()->cascadeOnDelete();
            $table->string('database_name')->nullable()->after('data_source_id');
            $table->string('schema_name')->nullable()->after('database_name');
            $table->string('table_name')->nullable()->after('schema_name');
            $table->string('column_name')->nullable()->after('table_name');
            $table->json('used_in_datasets')->nullable()->after('column_name');
            $table->boolean('is_nullable')->default(false)->after('used_in_datasets');
            $table->boolean('is_unique')->default(false)->after('is_nullable');
            $table->string('default_value')->nullable()->after('is_unique');
            $table->string('validation_rule')->nullable()->after('default_value');
            $table->string('sample_values')->nullable()->after('validation_rule');
            $table->boolean('contains_personal_data')->default(false)->after('sensitivity');
            $table->string('personal_data_type')->nullable()->after('contains_personal_data');
            $table->boolean('contains_sensitive_data')->default(false)->after('personal_data_type');
            $table->string('default_masking_method')->nullable()->after('contains_sensitive_data');
            $table->json('cde_categories')->nullable()->after('default_masking_method');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_elements', function (Blueprint $table) {
            $table->dropForeign(['data_source_id']);
            $table->dropColumn([
                'data_steward',
                'status',
                'data_source_id',
                'database_name',
                'schema_name',
                'table_name',
                'column_name',
                'used_in_datasets',
                'is_nullable',
                'is_unique',
                'default_value',
                'validation_rule',
                'sample_values',
                'contains_personal_data',
                'personal_data_type',
                'contains_sensitive_data',
                'default_masking_method',
                'cde_categories',
            ]);
            $table->string('owner_team')->nullable()->after('cde_flag');
            $table->string('quality_rules_ref')->nullable()->after('owner_team');
            $table->unsignedBigInteger('catalog_column_id')->nullable()->after('quality_rules_ref');
            $table->string('cde_category')->nullable()->after('cde_flag');
            $table->boolean('pii_flag')->default(false)->after('cde_category');
            $table->string('personal_data_category')->nullable()->after('pii_flag');
            $table->boolean('special_category_flag')->default(false)->after('personal_data_category');
        });

    }
};
