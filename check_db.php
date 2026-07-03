<?php
try {
    $db = new PDO("sqlite:/var/www/onclick2buy/database/database.sqlite");
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(", ", $tables) . "\n";
} catch(Exception $e) {
    echo $e->getMessage();
}
