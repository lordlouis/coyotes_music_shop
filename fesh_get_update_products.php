<?php
/**
 * Este codigo debe de estar en un template de fesh.
 * Ejemplo: Theme > Theme Editor > error / not_found.tpl  
 * Para ejecutar este archivo:
 * ejemplo : https://coyotesmusicshop.com.mx/force404?get_products_model=1
 */

if (isset($_GET['get_products_model']) && $_GET['get_products_model'] == '1') {
    ob_end_clean();
    ob_clean();
	$csv='';
    $query = $this->db->query("SELECT model from " . DB_PREFIX . "product");
    $row = $query->rows;
    foreach ($query->rows as $result) {
 		$csv.=  $result['model']  . PHP_EOL;
    }
    echo $csv;
    exit();
}
if (isset($_GET['update_products_model']) && $_GET['update_products_model'] == '1' && is_array($_POST)) {
    ob_end_clean();
    ob_clean();
    $count = 0;
    foreach ($_POST as $json) {
        $content = json_decode($json, true);
        $price = (float)$content['price'];
        $model = $this->db->escape($content['model']);
        $status = (int)$content['status'];
        $tax_class_id = (int)$content['tax_class_id'];
		$query = "UPDATE " . DB_PREFIX . "product SET date_modified = now(), price = " . $price . ", tax_class_id = " . $tax_class_id . ", status = " . $status . " WHERE model = '" . $model . "'";
        $result = $this->db->query($query);
        if($result === true){
            $count++;
        }
    }
    echo "updated " . $count . " products";
    exit();
}
?>