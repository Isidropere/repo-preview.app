<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function dumpTable($table) {
    if (!Schema::hasTable($table)) return "";
    $rows = DB::table($table)->get();
    $sql = "-- Dump of table $table\n";
    foreach($rows as $row) {
        $keys = [];
        $values = [];
        foreach((array)$row as $key => $val) {
            $keys[] = "`$key`";
            if ($val === null) {
                $values[] = "NULL";
            } else {
                $values[] = "'" . addslashes((string)$val) . "'";
            }
        }
        $sql .= "INSERT INTO `$table` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
    }
    $sql .= "\n";
    return $sql;
}

$output = dumpTable('cont_cuentas');
$output .= dumpTable('cont_diarios');
$output .= dumpTable('cont_diario_detalles');
file_put_contents('contabilidad_dump.sql', $output);
echo "Dumped " . strlen($output) . " bytes";
