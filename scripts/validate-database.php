<?php

/**
 * Database Validation Script
 * 
 * This script runs comprehensive database validation including:
 * - Schema documentation generation
 * - Schema integrity validation
 * - Seeder compatibility testing
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

class DatabaseValidator
{
    private array $results = [];
    
    public function run(): int
    {
        $this->printHeader();
        
        $this->runDocumentationGeneration();
        $this->runSchemaValidation();
        $this->runSeederTests();
        $this->runSchemaTests();
        
        $this->printSummary();
        
        return $this->hasErrors() ? 1 : 0;
    }
    
    private function printHeader(): void
    {
        echo "\n";
        echo "🔍 BoxPos Database Validation Suite\n";
        echo "==================================\n\n";
    }
    
    private function runDocumentationGeneration(): void
    {
        echo "📚 Generating database documentation...\n";
        
        try {
            Artisan::call('db:generate-docs', ['--output' => 'database-schema-documentation.md']);
            $this->results['documentation'] = [
                'status' => 'success',
                'message' => 'Database documentation generated successfully'
            ];
            echo "✅ Documentation generated: database-schema-documentation.md\n\n";
        } catch (Exception $e) {
            $this->results['documentation'] = [
                'status' => 'error',
                'message' => 'Failed to generate documentation: ' . $e->getMessage()
            ];
            echo "❌ Documentation generation failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function runSchemaValidation(): void
    {
        echo "🔍 Running schema validation...\n";
        
        try {
            $exitCode = Artisan::call('db:validate-schema');
            $output = Artisan::output();
            
            if ($exitCode === 0) {
                $this->results['schema_validation'] = [
                    'status' => 'success',
                    'message' => 'Schema validation passed'
                ];
                echo "✅ Schema validation passed\n";
            } else {
                $this->results['schema_validation'] = [
                    'status' => 'error',
                    'message' => 'Schema validation failed'
                ];
                echo "❌ Schema validation failed\n";
            }
            
            echo $output . "\n";
        } catch (Exception $e) {
            $this->results['schema_validation'] = [
                'status' => 'error',
                'message' => 'Schema validation error: ' . $e->getMessage()
            ];
            echo "❌ Schema validation error: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function runSeederTests(): void
    {
        echo "🌱 Running seeder compatibility tests...\n";
        
        try {
            $exitCode = $this->runPhpUnit('tests/Feature/DatabaseSeederCompatibilityTest.php');
            
            if ($exitCode === 0) {
                $this->results['seeder_tests'] = [
                    'status' => 'success',
                    'message' => 'Seeder compatibility tests passed'
                ];
                echo "✅ Seeder compatibility tests passed\n\n";
            } else {
                $this->results['seeder_tests'] = [
                    'status' => 'error',
                    'message' => 'Seeder compatibility tests failed'
                ];
                echo "❌ Seeder compatibility tests failed\n\n";
            }
        } catch (Exception $e) {
            $this->results['seeder_tests'] = [
                'status' => 'error',
                'message' => 'Seeder test error: ' . $e->getMessage()
            ];
            echo "❌ Seeder test error: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function runSchemaTests(): void
    {
        echo "🏗️ Running schema structure tests...\n";
        
        try {
            $exitCode = $this->runPhpUnit('tests/Feature/DatabaseSchemaValidationTest.php');
            
            if ($exitCode === 0) {
                $this->results['schema_tests'] = [
                    'status' => 'success',
                    'message' => 'Schema structure tests passed'
                ];
                echo "✅ Schema structure tests passed\n\n";
            } else {
                $this->results['schema_tests'] = [
                    'status' => 'error',
                    'message' => 'Schema structure tests failed'
                ];
                echo "❌ Schema structure tests failed\n\n";
            }
        } catch (Exception $e) {
            $this->results['schema_tests'] = [
                'status' => 'error',
                'message' => 'Schema test error: ' . $e->getMessage()
            ];
            echo "❌ Schema test error: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function runPhpUnit(string $testFile): int
    {
        $command = "vendor/bin/phpunit {$testFile} --verbose";
        
        $output = [];
        $exitCode = 0;
        
        exec($command, $output, $exitCode);
        
        foreach ($output as $line) {
            echo $line . "\n";
        }
        
        return $exitCode;
    }
    
    private function printSummary(): void
    {
        echo "📋 Validation Summary\n";
        echo "===================\n\n";
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($this->results as $test => $result) {
            $status = $result['status'] === 'success' ? '✅' : '❌';
            $testName = ucwords(str_replace('_', ' ', $test));
            
            echo "{$status} {$testName}: {$result['message']}\n";
            
            if ($result['status'] === 'success') {
                $successCount++;
            } else {
                $errorCount++;
            }
        }
        
        echo "\n";
        echo "Total Tests: " . count($this->results) . "\n";
        echo "Passed: {$successCount}\n";
        echo "Failed: {$errorCount}\n\n";
        
        if ($errorCount === 0) {
            echo "🎉 All database validations passed!\n";
        } else {
            echo "⚠️  Some validations failed. Please review the errors above.\n";
        }
    }
    
    private function hasErrors(): bool
    {
        foreach ($this->results as $result) {
            if ($result['status'] === 'error') {
                return true;
            }
        }
        
        return false;
    }
}

// Run the validator if this script is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $validator = new DatabaseValidator();
    exit($validator->run());
}