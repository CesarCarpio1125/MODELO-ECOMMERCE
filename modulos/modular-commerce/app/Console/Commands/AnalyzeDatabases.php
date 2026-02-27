<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyzeDatabases extends Command
{
    protected $signature = 'db:analyze {--source=docker} {--target=native}';
    protected $description = 'Analyze database structures for migration planning';

    public function handle()
    {
        $source = $this->option('source');
        $target = $this->option('target');

        $this->info("=== Database Analysis: {$source} → {$target} ===");
        $this->newLine();

        // Analyze source database
        $this->analyzeSourceDatabase($source);

        // Analyze target database  
        $this->analyzeTargetDatabase($target);

        // Compare and suggest migration strategy
        $this->compareDatabases($source, $target);

        return Command::SUCCESS;
    }

    private function analyzeSourceDatabase(string $source)
    {
        $this->info("📊 Analyzing {$source} database...");
        
        try {
            // Get all tables (SQLite syntax)
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
            $tableNames = array_map(fn($table) => $table->name, $tables);
            
            $this->line("Tables found: " . count($tableNames));
            
            foreach ($tableNames as $table) {
                $this->analyzeTable($table, $source);
            }
            
        } catch (\Exception $e) {
            $this->error("Error analyzing {$source} database: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function analyzeTargetDatabase(string $target)
    {
        $this->info("📊 Analyzing {$target} database...");
        
        try {
            // Get all tables (SQLite syntax)
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
            $tableNames = array_map(fn($table) => $table->name, $tables);
            
            $this->line("Tables found: " . count($tableNames));
            
            foreach ($tableNames as $table) {
                $this->analyzeTable($table, $target);
            }
            
        } catch (\Exception $e) {
            $this->error("Error analyzing {$target} database: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function analyzeTable(string $tableName, string $context)
    {
        try {
            $count = DB::table($tableName)->count();
            $this->line("  - {$tableName}: {$count} records");
            
            // Show important tables with more detail
            if (in_array($tableName, ['users', 'vendors', 'products', 'orders'])) {
                $this->showTableDetails($tableName);
            }
            
        } catch (\Exception $e) {
            $this->line("  - {$tableName}: Error analyzing - " . $e->getMessage());
        }
    }

    private function showTableDetails(string $tableName)
    {
        try {
            $sample = DB::table($tableName)->limit(3)->get();
            
            if ($sample->isNotEmpty()) {
                $columns = array_keys((array) $sample->first());
                $this->line("    Columns: " . implode(', ', $columns));
                
                if ($tableName === 'users') {
                    $this->line("    Sample users:");
                    foreach ($sample as $user) {
                        $this->line("      ID: {$user->id}, Email: {$user->email}, Role: {$user->role}");
                    }
                }
            }
        } catch (\Exception $e) {
            $this->line("    Could not get sample data");
        }
    }

    private function compareDatabases(string $source, string $target)
    {
        $this->info("🔍 Comparing databases...");
        
        try {
            // Get tables from both databases (SQLite syntax)
            $sourceTables = array_map(fn($table) => $table->name, DB::select("SELECT name FROM sqlite_master WHERE type='table'"));
            $targetTables = array_map(fn($table) => $table->name, DB::select("SELECT name FROM sqlite_master WHERE type='table'"));
            
            $this->line("Source tables: " . count($sourceTables));
            $this->line("Target tables: " . count($targetTables));
            
            // Find missing tables
            $missingInTarget = array_diff($sourceTables, $targetTables);
            $missingInSource = array_diff($targetTables, $sourceTables);
            
            if (!empty($missingInTarget)) {
                $this->warn("Tables missing in target:");
                foreach ($missingInTarget as $table) {
                    $this->line("  - {$table}");
                }
            }
            
            if (!empty($missingInSource)) {
                $this->warn("Tables missing in source:");
                foreach ($missingInSource as $table) {
                    $this->line("  - {$table}");
                }
            }
            
            // Show data counts comparison
            $this->showDataComparison($sourceTables, $targetTables);
            
        } catch (\Exception $e) {
            $this->error("Error comparing databases: " . $e->getMessage());
        }
    }

    private function showDataComparison(array $sourceTables, array $targetTables)
    {
        $this->newLine();
        $this->info("📈 Data Comparison:");
        
        $commonTables = array_intersect($sourceTables, $targetTables);
        
        foreach ($commonTables as $table) {
            try {
                $count = DB::table($table)->count();
                $this->line("  {$table}: {$count} records");
            } catch (\Exception $e) {
                $this->line("  {$table}: Error counting records");
            }
        }
    }
}
