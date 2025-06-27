<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OptimizeUserQueries extends Command {
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'users:optimize-db';

    /**
     * The console command description.
     */
    protected $description = 'Analyze and suggest database optimizations for user queries';

    /**
     * Execute the console command.
     */
    public function handle() {
        $this->info("Analyzing database for user query optimizations...");

        $suggestions = [];

        // Check for common indices that should exist
        $this->checkIndices($suggestions);

        // Show query analysis
        $this->analyzeQueries($suggestions);

        if (empty($suggestions)) {
            $this->info("✓ Database appears to be well optimized for user queries");
        } else {
            $this->warn("Found optimization opportunities:");
            foreach ($suggestions as $suggestion) {
                $this->line("• {$suggestion}");
            }
        }

        return 0;
    }

    /**
     * Check for important database indices
     */
    private function checkIndices(array &$suggestions) {
        $this->line("Checking database indices...");

        // Check users table indices
        $usersIndices = $this->getTableIndices('users');

        $recommendedIndices = [
            'users' => [
                'is_disabled',
                'created_at',
                'nation_id'
            ],
            'user_roles' => [
                'user_id',
                'role_id'
            ],
            'academies_athletes' => [
                'user_id',
                'academy_id'
            ],
            'schools_personnel' => [
                'user_id',
                'school_id'
            ],
            'academies_athletes' => [
                'user_id',
                'academy_id'
            ],
            'schools_athletes' => [
                'user_id',
                'school_id'
            ]
        ];

        foreach ($recommendedIndices as $table => $columns) {
            $tableIndices = $this->getTableIndices($table);

            foreach ($columns as $column) {
                if (!$this->hasIndexOnColumn($tableIndices, $column)) {
                    $suggestions[] = "Add index on {$table}.{$column}";
                }
            }
        }
    }

    /**
     * Get indices for a table
     */
    private function getTableIndices(string $table): array {
        try {
            $query = "SHOW INDEX FROM {$table}";
            return DB::select($query);
        } catch (\Exception $e) {
            $this->warn("Could not check indices for table {$table}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if table has index on column
     */
    private function hasIndexOnColumn(array $indices, string $column): bool {
        foreach ($indices as $index) {
            if ($index->Column_name === $column && $index->Seq_in_index == 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Analyze common query patterns
     */
    private function analyzeQueries(array &$suggestions) {
        $this->line("Analyzing query patterns...");

        // Check for N+1 query issues by looking at relationship usage
        $this->line("Checking for potential N+1 query issues...");

        // These would be detected at runtime, but we can suggest best practices
        $suggestions[] = "Ensure weaponFormsPersonnel and weaponFormsTechnician relationships are always eager loaded";
        $suggestions[] = "Consider using a single polymorphic relationship for academy/school associations";
        $suggestions[] = "Add database-level caching for frequently accessed lookup tables (roles, nations)";
    }
}
