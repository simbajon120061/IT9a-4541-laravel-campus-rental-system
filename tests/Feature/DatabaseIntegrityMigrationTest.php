<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseIntegrityMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_integrity_migrations_apply_expected_columns(): void
    {
        $this->assertFalse(Schema::hasColumn('items', 'category'));
        $this->assertTrue(Schema::hasColumn('items', 'deleted_at'));

        $this->assertTrue(Schema::hasColumn('users', 'restricted_at'));
        $this->assertTrue(Schema::hasColumn('users', 'restricted_by'));
        $this->assertTrue(Schema::hasColumn('users', 'deleted_at'));

        $this->assertTrue(Schema::hasColumn('rentals', 'approved_at'));
        $this->assertTrue(Schema::hasColumn('rentals', 'active_at'));
        $this->assertTrue(Schema::hasColumn('rentals', 'completed_at'));
        $this->assertTrue(Schema::hasColumn('rentals', 'cancelled_at'));
        $this->assertTrue(Schema::hasColumn('rentals', 'deleted_at'));
    }
}
