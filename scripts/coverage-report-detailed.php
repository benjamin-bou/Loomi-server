<?php

/**
 * Générateur de rapport de couverture de code détaillé
 * Analyse les tests et le code source pour estimer la couverture
 */

class CoverageReportGenerator
{
    private $projectRoot;
    private $testResults = [];
    private $sourceFiles = [];
    private $testFiles = [];

    public function __construct($projectRoot)
    {
        $this->projectRoot = $projectRoot;
    }

    public function generate()
    {
        echo "🔍 GÉNÉRATION DU RAPPORT DE COUVERTURE DÉTAILLÉ\n";
        echo "=" . str_repeat("=", 50) . "\n\n";

        $this->analyzeTests();
        $this->analyzeSourceCode();
        $this->generateDetailedReport();
        $this->generateHtmlReport();
        $this->generateRecommendations();
    }

    private function analyzeTests()
    {
        echo "📋 ANALYSE DES TESTS...\n";

        $testDir = $this->projectRoot . '/tests';
        $testFiles = $this->scanDirectory($testDir, '.php');

        foreach ($testFiles as $file) {
            $content = file_get_contents($file);
            $className = $this->extractClassName($content);
            $methods = $this->extractTestMethods($content);
            $testType = $this->getTestType($file);

            $this->testFiles[$file] = [
                'class' => $className,
                'methods' => $methods,
                'type' => $testType,
                'coverage_targets' => $this->extractCoverageTargets($content)
            ];
        }

        echo "✅ " . count($this->testFiles) . " fichiers de tests analysés\n\n";
    }

    private function analyzeSourceCode()
    {
        echo "🔍 ANALYSE DU CODE SOURCE...\n";

        $sourceDir = $this->projectRoot . '/app';
        $sourceFiles = $this->scanDirectory($sourceDir, '.php');

        foreach ($sourceFiles as $file) {
            $content = file_get_contents($file);
            $className = $this->extractClassName($content);
            $methods = $this->extractMethods($content);
            $complexity = $this->calculateComplexity($content);

            $this->sourceFiles[$file] = [
                'class' => $className,
                'methods' => $methods,
                'complexity' => $complexity,
                'lines' => count(explode("\n", $content)),
                'tested' => $this->isClassTested($className)
            ];
        }

        echo "✅ " . count($this->sourceFiles) . " fichiers source analysés\n\n";
    }

    private function generateDetailedReport()
    {
        echo "📊 RAPPORT DE COUVERTURE DÉTAILLÉ\n";
        echo "=" . str_repeat("=", 40) . "\n\n";

        $totalClasses = count($this->sourceFiles);
        $testedClasses = array_sum(array_column($this->sourceFiles, 'tested'));
        $coverage = $totalClasses > 0 ? ($testedClasses / $totalClasses) * 100 : 0;

        echo "🎯 COUVERTURE GLOBALE\n";
        echo "Classes testées: {$testedClasses}/{$totalClasses}\n";
        echo "Pourcentage: " . number_format($coverage, 1) . "%\n";
        echo $this->getCoverageStatus($coverage) . "\n\n";
        echo "📋 DÉTAIL PAR TYPE DE TEST\n";
        $testTypes = ['Unit' => 0, 'Feature' => 0, 'Integration' => 0, 'Other' => 0];
        foreach ($this->testFiles as $file => $data) {
            if (isset($testTypes[$data['type']])) {
                $testTypes[$data['type']]++;
            } else {
                $testTypes['Other']++;
            }
        }

        foreach ($testTypes as $type => $count) {
            echo "- Tests {$type}: {$count}\n";
        }

        echo "\n🔍 CLASSES NON TESTÉES\n";
        foreach ($this->sourceFiles as $file => $data) {
            if (!$data['tested']) {
                $relativePath = str_replace($this->projectRoot . '/', '', $file);
                echo "⚠️  {$data['class']} ({$relativePath})\n";
            }
        }

        echo "\n📈 MÉTRIQUES DÉTAILLÉES\n";
        $totalLines = array_sum(array_column($this->sourceFiles, 'lines'));
        $totalMethods = array_sum(array_map(function ($f) {
            return count($f['methods']);
        }, $this->sourceFiles));
        $totalTestMethods = array_sum(array_map(function ($f) {
            return count($f['methods']);
        }, $this->testFiles));

        echo "Lignes de code source: {$totalLines}\n";
        echo "Méthodes source: {$totalMethods}\n";
        echo "Méthodes de test: {$totalTestMethods}\n";
        echo "Ratio test/code: " . number_format($totalTestMethods / max($totalMethods, 1), 2) . "\n\n";
    }

    private function generateHtmlReport()
    {
        echo "📄 GÉNÉRATION DU RAPPORT HTML...\n";

        $reportDir = $this->projectRoot . '/storage/logs/coverage-html';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }

        $htmlContent = $this->generateHtmlContent();
        file_put_contents($reportDir . '/index.html', $htmlContent);

        echo "✅ Rapport HTML généré: {$reportDir}/index.html\n\n";
    }

    private function generateHtmlContent()
    {
        $totalClasses = count($this->sourceFiles);
        $testedClasses = array_sum(array_column($this->sourceFiles, 'tested'));
        $coverage = $totalClasses > 0 ? ($testedClasses / $totalClasses) * 100 : 0;
        $totalTestMethods = array_sum(array_map(function ($f) {
            return count($f['methods']);
        }, $this->testFiles));

        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de Couverture - Loomi Server</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .coverage-bar { width: 100%; height: 30px; background-color: #e0e0e0; border-radius: 15px; overflow: hidden; margin: 10px 0; }
        .coverage-fill { height: 100%; background: linear-gradient(90deg, #ff4444 0%, #ffaa00 50%, #44ff44 100%); transition: width 0.3s ease; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 2em; font-weight: bold; color: #007bff; }
        .file-list { margin: 20px 0; }
        .file-item { padding: 10px; margin: 5px 0; border-left: 4px solid #007bff; background: #f8f9fa; }
        .tested { border-left-color: #28a745; }
        .untested { border-left-color: #dc3545; }
        .recommendations { background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .timestamp { text-align: center; color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 Rapport de Couverture de Code</h1>
            <h2>Loomi Server - Laravel Application</h2>
            <div class="timestamp">Généré le ' . date('d/m/Y à H:i:s') . '</div>
        </div>
        
        <div class="coverage-summary">
            <h3>📊 Résumé de la Couverture</h3>
            <div class="coverage-bar">
                <div class="coverage-fill" style="width: ' . $coverage . '%"></div>
            </div>
            <p><strong>' . number_format($coverage, 1) . '%</strong> de couverture estimée</p>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number">' . $totalClasses . '</div>
                <div>Classes Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">' . $testedClasses . '</div>
                <div>Classes Testées</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">' . count($this->testFiles) . '</div>
                <div>Fichiers de Tests</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">' . $totalTestMethods . '</div>
                <div>Méthodes de Test</div>
            </div>
        </div>
        
        <div class="file-list">
            <h3>📁 État des Classes</h3>';

        foreach ($this->sourceFiles as $file => $data) {
            $relativePath = str_replace($this->projectRoot . '/', '', $file);
            $relativePath = str_replace('\\', '/', $relativePath);
            $status = $data['tested'] ? 'tested' : 'untested';
            $statusText = $data['tested'] ? '✅ Testée' : '❌ Non testée';

            $html .= '<div class="file-item ' . $status . '">
                <strong>' . htmlspecialchars($data['class']) . '</strong> - ' . $statusText . '<br>
                <small>' . htmlspecialchars($relativePath) . ' (' . $data['lines'] . ' lignes, ' . count($data['methods']) . ' méthodes)</small>
            </div>';
        }

        $html .= '</div>
        
        <div class="recommendations">
            <h3>💡 Recommandations</h3>
            <ul>
                <li><strong>Installer une extension de couverture :</strong> PCOV ou Xdebug pour une couverture précise</li>
                <li><strong>Augmenter la couverture :</strong> Viser au moins 80% de couverture</li>
                <li><strong>Tester les cas limites :</strong> Erreurs, validations, edge cases</li>
                <li><strong>Tests d\'intégration :</strong> Ajouter des tests end-to-end</li>
            </ul>
        </div>
    </div>
</body>
</html>';

        return $html;
    }

    private function generateRecommendations()
    {
        echo "💡 RECOMMANDATIONS\n";
        echo "=" . str_repeat("=", 20) . "\n\n";

        $totalClasses = count($this->sourceFiles);
        $testedClasses = array_sum(array_column($this->sourceFiles, 'tested'));
        $coverage = $totalClasses > 0 ? ($testedClasses / $totalClasses) * 100 : 0;

        if ($coverage < 70) {
            echo "🔴 PRIORITÉ HAUTE : Couverture insuffisante\n";
            echo "- Créer des tests pour les classes non testées\n";
            echo "- Installer PCOV ou Xdebug pour une mesure précise\n\n";
        } elseif ($coverage < 85) {
            echo "🟡 PRIORITÉ MOYENNE : Bonne couverture, amélioration possible\n";
            echo "- Ajouter des tests pour les cas limites\n";
            echo "- Installer une extension de couverture pour des métriques précises\n\n";
        } else {
            echo "🟢 EXCELLENT : Très bonne couverture de code\n";
            echo "- Maintenir la qualité des tests\n";
            echo "- Considérer l'ajout de tests de mutation\n\n";
        }

        echo "🛠️  COMMANDES UTILES\n";
        echo "- Tests simples : php artisan test\n";
        echo "- Analyse détaillée : php scripts/coverage-report.php\n";
        echo "- Rapport HTML : Voir storage/logs/coverage-html/index.html\n";
        echo "- Scripts disponibles : ./coverage.sh help\n\n";
    }

    // Méthodes utilitaires
    private function scanDirectory($dir, $extension)
    {
        $files = [];
        if (!is_dir($dir)) return $files;

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if ($file->getExtension() === ltrim($extension, '.')) {
                $files[] = $file->getPathname();
            }
        }
        return $files;
    }

    private function extractClassName($content)
    {
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $matches[1];
        }
        return 'Unknown';
    }

    private function extractTestMethods($content)
    {
        preg_match_all('/public\s+function\s+(test\w+|\w+.*test.*)\s*\(/', $content, $matches);
        return $matches[1] ?? [];
    }

    private function extractMethods($content)
    {
        preg_match_all('/public\s+function\s+(\w+)\s*\(/', $content, $matches);
        return array_filter($matches[1], function ($method) {
            return !in_array($method, ['__construct', '__destruct']);
        });
    }

    private function getTestType($file)
    {
        if (strpos($file, '/Unit/') !== false || strpos($file, '\\Unit\\') !== false) return 'Unit';
        if (strpos($file, '/Feature/') !== false || strpos($file, '\\Feature\\') !== false) return 'Feature';
        if (strpos($file, '/Integration/') !== false || strpos($file, '\\Integration\\') !== false) return 'Integration';
        return 'Other';
    }

    private function extractCoverageTargets($content)
    {
        // Extraction des classes mentionnées dans les tests
        preg_match_all('/new\s+(\w+)\s*\(|(\w+)::/m', $content, $matches);
        return array_unique(array_merge($matches[1], $matches[2]));
    }

    private function isClassTested($className)
    {
        foreach ($this->testFiles as $testFile => $data) {
            if (in_array($className, $data['coverage_targets'] ?? [])) {
                return true;
            }
            // Vérification basée sur le nom du test
            if (stripos($data['class'], $className) !== false) {
                return true;
            }
        }
        return false;
    }

    private function calculateComplexity($content)
    {
        // Calcul basique de complexité cyclomatique
        $keywords = ['if', 'else', 'while', 'for', 'foreach', 'switch', 'case', 'catch', 'throw'];
        $complexity = 1; // Base complexity

        foreach ($keywords as $keyword) {
            $complexity += substr_count($content, $keyword);
        }

        return $complexity;
    }

    private function getCoverageStatus($coverage)
    {
        if ($coverage >= 80) return "🟢 Excellente couverture";
        if ($coverage >= 60) return "🟡 Couverture acceptable";
        return "🔴 Couverture insuffisante";
    }
}

// Exécution du script
echo "🚀 Démarrage de l'analyse de couverture...\n";
echo "📁 SAPI: " . php_sapi_name() . "\n";

try {
    $projectRoot = dirname(__DIR__);
    echo "📁 Répertoire du projet: {$projectRoot}\n\n";

    $generator = new CoverageReportGenerator($projectRoot);
    $generator->generate();

    echo "\n✅ Rapport de couverture généré avec succès!\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
