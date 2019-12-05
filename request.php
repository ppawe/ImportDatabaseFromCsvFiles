<?php
$conn = new mysqli($_POST["mysqlHostName"], $_POST["mysqlUserName"], $_POST["mysqlPassword"], $_POST["DbNameExport"]);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to export database successfully<br>";

$tables = ["ps_customer","ps_customer_group","ps_customer_message","ps_customer_thread","ps_product","ps_product_attribute","ps_product_attribute_combination","ps_product_attribute_shop","ps_product_lang","ps_product_shop","ps_category","ps_category_group","ps_category_lang","ps_category_product","ps_category_shop","ps_orders","ps_order_carrier","ps_order_cart_rule","ps_order_detail","ps_order_detail_tax","ps_order_history","ps_order_invoice","ps_order_invoice_payment","ps_order_invoice_tax","ps_order_message","ps_order_message_lang","ps_order_payment","ps_or","er_return_state","ps_order_return_state_lang","ps_order_slip","ps_order_state","ps_order_state_lang","ps_guest","ps_delivery","ps_employee","ps_employee_shop","ps_group","ps_group_lang","ps_group_shop","ps_info","ps_info_lang","ps_lang","ps_lang_shop","ps_pack","ps_search_index","ps_search_word","ps_sekeyword","ps_specific_price","ps_specific_price_priority","ps_specific_price_rule","ps_state","ps_tax","ps_tax_lang","ps_tax_rule","ps_tax_rules_group","ps_tax_rules_group_shop","ps_zone"];
if (isset($_POST["logs"])) {
    array_push($tables, "ps_log");
}
if (isset($_POST["messages"])) {
    array_push($tables, "ps_message");
}

$oldMask = umask(0);
if (!is_dir($_POST["folder_path"])) {
    mkdir($_POST["folder_path"], 0777);
}
umask($oldMask);

echo "Exportiere alte Daten...<br>";

foreach ($tables as $table) {
    $conn->query("SELECT * FROM " . $table . " INTO OUTFILE '" . $_POST["folder_path"] . "/" . $table . ".csv' FIELDS ENCLOSED BY ',' TERMINATED BY ';' ESCAPED BY '\"' LINES TERMINATED BY '\\r\\n'");
}

echo "Export finished.<br>";

$conn = new mysqli($_POST["mysqlHostName"], $_POST["mysqlUserName"], $_POST["mysqlPassword"], $_POST["DbNameImport"]);

if ($conn->connect_error) {
    echo "Dying!";
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
        unlink($file);
    } else {
        echo "Path not found!<br>";
    }
}
rmdir($_POST["folder_path"]);
echo "Done!";
