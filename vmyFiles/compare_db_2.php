<?php
/**
 * compare_db.php
 * Kaycompari juj databases MySQL (structure + content) w kaywarri
 * ghir les tables li fihom des différences.
 *
 * Khdmt: php compare_db.php
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
    'db'   => 'mage2store_main',
];
 
// Tables li khassahom ytnaqaw mn comparison (dynamic content, machi mohim)
// Kaykhdmo b wildcard: * kaybaddel n'importe quel séquence
$excludePatterns = [
    'cache*',
    'report_*',
    'log_*',
    '*session*',
    'admin_user_session',
    'search_query',
    'indexer_state',
    'index_event',
];
// =========================================================
 
function isExcluded(string $table, array $patterns): bool
{
    foreach ($patterns as $pattern) {
        $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/i';
        if (preg_match($regex, $table)) {
            return true;
        }
    }
    return false;
}
 
function connect(array $cfg): mysqli
{
    $conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port']);
    if ($conn->connect_error) {
        die("Connexion faillat l {$cfg['db']} ({$cfg['host']}:{$cfg['port']}): {$conn->connect_error}\n");
    }
    return $conn;
}
 
function getTables(mysqli $conn): array
{
    $tables = [];
    $res = $conn->query('SHOW TABLES');
    while ($row = $res->fetch_row()) {
        $tables[] = $row[0];
    }
    return $tables;
}
 
function checksumTable(mysqli $conn, string $table): ?string
{
    // CHECKSUM TABLE kaydir hash 3la structure + data dyal table
    $res = $conn->query("CHECKSUM TABLE `{$table}`");
    if (!$res) {
        return null;
    }
    $row = $res->fetch_assoc();
    return $row['Checksum'] ?? null;
}
 
function rowCount(mysqli $conn, string $table): ?int
{
    $res = $conn->query("SELECT COUNT(*) as c FROM `{$table}`");
    if (!$res) {
        return null;
    }
    return (int) $res->fetch_assoc()['c'];
}
 
// ---------- Bda l comparaison ----------
echo "Connexion l server1 ({$server1['db']}) w server2 ({$server2['db']})...\n\n";
 
$conn1 = connect($server1);
$conn2 = connect($server2);
 
$tables1 = getTables($conn1);
$tables2 = getTables($conn2);
 
$onlyIn1 = array_diff($tables1, $tables2);
$onlyIn2 = array_diff($tables2, $tables1);
$common  = array_intersect($tables1, $tables2);
 
$excluded = array_filter($common, fn($t) => isExcluded($t, $excludePatterns));
$common   = array_diff($common, $excluded);
 
if ($excluded) {
    echo "Tables mtnaqyin (excluded, machi mqarnin):\n  - " . implode("\n  - ", $excluded) . "\n\n";
}
 
if ($onlyIn1) {
    echo "Tables li kaynin ghi f {$server1['db']}:\n  - " . implode("\n  - ", $onlyIn1) . "\n\n";
}
if ($onlyIn2) {
    echo "Tables li kaynin ghi f {$server2['db']}:\n  - " . implode("\n  - ", $onlyIn2) . "\n\n";
}
 
echo "Kan-comparew " . count($common) . " tables mochtarkin...\n\n";
 
$different = [];
$identical = 0;
 
foreach ($common as $table) {
    $sum1 = checksumTable($conn1, $table);
    $sum2 = checksumTable($conn2, $table);
 
    if ($sum1 !== $sum2) {
        $count1 = rowCount($conn1, $table);
        $count2 = rowCount($conn2, $table);
        $different[] = [
            'table'  => $table,
            'count1' => $count1,
            'count2' => $count2,
        ];
    } else {
        $identical++;
    }
}
 
echo "===========================================\n";
echo "NATIJA\n";
echo "===========================================\n";
echo "Tables zwina (identical): {$identical}\n";
echo "Tables fihom diff\u00e9rence: " . count($different) . "\n\n";
 
if ($different) {
    printf("%-40s %-15s %-15s\n", 'Table', $server1['db'], $server2['db']);
    printf("%-40s %-15s %-15s\n", str_repeat('-', 40), str_repeat('-', 15), str_repeat('-', 15));
    foreach ($different as $d) {
        printf("%-40s %-15s %-15s\n", $d['table'], $d['count1'] . ' rows', $d['count2'] . ' rows');
    }
}
 
$conn1->close();
$conn2->close();