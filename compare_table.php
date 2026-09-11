<?php
/**
 * diff_core_config.php
 * Kaywarri exactement chno tbeddel f table core_config_data bin juj databases,
 * w kayflagi les paths li machi metwaq3in ykounou dyal "theme" branch.
 *
 * Khdmt: php diff_core_config.php
 */
 
// ============ CONFIGURATION - beddel hadchi ============
$server1 = [
    'host' => 'localhost',
    'port' => 3306,
    'user' => 'root',
    'pass' => 'NewPass123!',
    'db'   => 'mage2store',
];
 
$server2 = [
    'host' => 'localhost',
    'port' => 3306,
    'user' => 'root',
    'pass' => 'NewPass123!',
    'db'   => 'mage2store_befor_theme',
];
 
// Patterns dyal paths li 3adiyin (theme-related) - matban-hom-ch f warning
$themeRelatedPatterns = [
    'design/*',
    'theme/*',
    'dev/css/*',
    'dev/js/*',
    'web/default_layouts/*',
];
// =========================================================
 // Befehel beispiel: php compare_table.php core_config_data
// Bezzaf dyal rows li ghadi ywarri b détail (bach matbanch l terminal 3aynich)

// Bezzaf dyal rows li ghadi ywarri b détail (bach matbanch l terminal 3aynich)
$maxDetailRows = 30;
// =========================================================
 
// ---------- Ta 9raya l argument ----------
if ($argc < 2) {
    echo "Usage: php diff_table.php nom_de_la_table\n";
    echo "Mtal:  php diff_table.php cms_block\n";
    exit(1);
}
$table = $argv[1];
 
function connect(array $cfg): mysqli
{
    $conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port']);
    if ($conn->connect_error) {
        die("Connexion faillat l {$cfg['db']} ({$cfg['host']}:{$cfg['port']}): {$conn->connect_error}\n");
    }
    return $conn;
}
 
function tableExists(mysqli $conn, string $table): bool
{
    $safeTable = $conn->real_escape_string($table);
    $res = $conn->query("SHOW TABLES LIKE '{$safeTable}'");
    return $res && $res->num_rows > 0;
}
 
function getPrimaryKey(mysqli $conn, string $table): array
{
    $pk = [];
    $res = $conn->query("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'");
    while ($row = $res->fetch_assoc()) {
        $pk[] = $row['Column_name'];
    }
    return $pk;
}
 
function formatRow(array $row): string
{
    $lines = [];
    foreach ($row as $col => $val) {
        $val = $val === null ? '(null)' : $val;
        $lines[] = "      {$col}: {$val}";
    }
    return implode("\n", $lines);
}
 
function getRows(mysqli $conn, string $table, array $pk): array{
    $rows = [];
    $res = $conn->query("SELECT * FROM `{$table}`");
    while ($row = $res->fetch_assoc()) {
        if ($pk) {
            $keyParts = array_map(fn($col) => $row[$col] ?? '', $pk);
            $key = implode('|', $keyParts);
        } else {
            // Bla primary key: kandirou hash 3la l ligne kamla
            $key = md5(implode('|', $row));
        }
        $rows[$key] = $row;
    }
    return $rows;
}
 
// ---------- Bda ----------
echo "Connexion l server1 ({$server1['db']}) w server2 ({$server2['db']})...\n\n";
 
$conn1 = connect($server1);
$conn2 = connect($server2);
 
if (!tableExists($conn1, $table)) {
    die("Table '{$table}' machaya kaynach f {$server1['db']}\n");
}
if (!tableExists($conn2, $table)) {
    die("Table '{$table}' machaya kaynach f {$server2['db']}\n");
}
 
$pk = getPrimaryKey($conn1, $table);
if ($pk) {
    echo "Primary key mstaamla: " . implode(', ', $pk) . "\n\n";
} else {
    echo "Ma3andhach primary key — kandirou hash 3la l ligne kamla (moins précis).\n\n";
}
 
$rows1 = getRows($conn1, $table, $pk);
$rows2 = getRows($conn2, $table, $pk);
 
$onlyIn1Keys = array_diff(array_keys($rows1), array_keys($rows2));
$onlyIn2Keys = array_diff(array_keys($rows2), array_keys($rows1));
$commonKeys  = array_intersect(array_keys($rows1), array_keys($rows2));
 
$changedRows = [];
foreach ($commonKeys as $key) {
    $r1 = $rows1[$key];
    $r2 = $rows2[$key];
    $diffCols = [];
    foreach ($r1 as $col => $val1) {
        $val2 = $r2[$col] ?? null;
        if ($val1 !== $val2) {
            $diffCols[$col] = ['s1' => $val1, 's2' => $val2];
        }
    }
    if ($diffCols) {
        $changedRows[$key] = $diffCols;
    }
}
 
// ---------- Natija ----------
echo "===========================================\n";
echo "NATIJA: {$table}\n";
echo "===========================================\n";
echo "Total server1: " . count($rows1) . " rows\n";
echo "Total server2: " . count($rows2) . " rows\n";
echo "Kaynin ghi f server1 (" . $server1['db'] . "): " . count($onlyIn1Keys) . "\n";
echo "Kaynin ghi f server2 (" . $server2['db'] . "): " . count($onlyIn2Keys) . "\n";
echo "Rows tbeddlo (nafs PK, valeurs mkhtalfin): " . count($changedRows) . "\n\n";
 
if ($onlyIn1Keys) {
    echo "--- Rows li kaynin GHI f server1 ---\n";
    $i = 0;
    foreach ($onlyIn1Keys as $key) {
        if (++$i > $maxDetailRows) {
            echo "... w " . (count($onlyIn1Keys) - $maxDetailRows) . " row(s) khrin\n";
            break;
        }
        echo "  [{$key}]\n" . formatRow($rows1[$key]) . "\n\n";
    }
    echo "\n";
}
 
if ($onlyIn2Keys) {
    echo "--- Rows li kaynin GHI f server2 ---\n";
    $i = 0;
    foreach ($onlyIn2Keys as $key) {
        if (++$i > $maxDetailRows) {
            echo "... w " . (count($onlyIn2Keys) - $maxDetailRows) . " row(s) khrin\n";
            break;
        }
        echo "  [{$key}]\n" . formatRow($rows2[$key]) . "\n\n";
    }
    echo "\n";
}
 
if ($changedRows) {
    echo "--- Rows li tbeddlo (nafs PK, valeurs mkhtalfin) ---\n";
    $i = 0;
    foreach ($changedRows as $key => $diffCols) {
        if (++$i > $maxDetailRows) {
            echo "... w " . (count($changedRows) - $maxDetailRows) . " row(s) khrin\n";
            break;
        }
        echo "  [{$key}]\n";
        foreach ($diffCols as $col => $vals) {
            $v1 = $vals['s1'] ?? '(null)';
            $v2 = $vals['s2'] ?? '(null)';
            echo "    {$col}: server1='{$v1}'  |  server2='{$v2}'\n";
        }
    }
    echo "\n";
}
 
$conn1->close();
$conn2->close();
 