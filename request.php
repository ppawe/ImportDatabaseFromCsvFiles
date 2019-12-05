<?php
$conn = new mysqli($_POST["mysqlHostName"], $_POST["mysqlUserName"], $_POST["mysqlPassword"], $_POST["DbNameExport"]);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to export database successfully\n\n";

$tables = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = '" . $_POST["DbNameExport"] . "'")->fetch_all();

$oldMask = umask(0);
if (!is_dir($_POST["folder_path"])) {
    mkdir($_POST["folder_path"], 0777);
}
umask($oldMask);

foreach ($tables as $table) {
    $conn->query("SELECT * FROM " . $table[0] . " INTO OUTFILE '" . $_POST["folder_path"] . "/" . $table[0] . ".csv' FIELDS ENCLOSED BY ',' TERMINATED BY ';' ESCAPED BY '\"' LINES TERMINATED BY '\\r\\n'");

    echo "\n\n";
}

$conn = new mysqli($_POST["mysqlHostName"], $_POST["mysqlUserName"], $_POST["mysqlPassword"], $_POST["DbNameImport"]);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to import database successfully<br>";

$files = glob($_POST["folder_path"] . "/*.{csv}", GLOB_BRACE);
foreach ($files as $file) {
    if (file_exists($file)) {
        $path = explode("/", $file);
        $name = $path[sizeof($path) - 1];
        $conn->query("TRUNCATE TABLE " . basename($name, 'csv'));
        $success = $conn->query("LOAD DATA LOCAL INFILE '" . $file . "' INTO TABLE " . basename($name, '.csv') . " FIELDS ENCLOSED BY ',' TERMINATED BY ';' ESCAPED BY '\"' LINES TERMINATED BY '\\r\\n'");
        echo " ";
        var_dump($success);
        echo "<br>";
        if (!$success) {
            echo $conn->error . "<br>";
        }
    } else {
        echo "Path not found!<br>";
    }
}
echo "Done!";
