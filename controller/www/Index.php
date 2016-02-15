<?php

	class Index {

	function __invoke()
	{
		echo "hello world!";
	}

	function ___construct()
	{
	$segment[0] = 'index';
	require($site_module_path.'page.php');

	$teazers = '';
	$tpltz = new template('teazer', Template::SOURCE_DB);
	$rs = $db->query('SELECT `id`, `name`, `desc`, `img`, `url`, `img`, `newwindow`, `external` FROM `teaser` WHERE `show`<>0 ORDER BY `num`') or die('Teazers: '.$db->lastError);
	for ($i=0; $i<10; $i++) {
		$img  = '0.gif';
		$url  = '';
		$name = '';
		if ($sa = $db->fetch($rs)) {
			$name = $sa['name'];
			if ($sa['img']) {
				$img  = $sa['img'];
				$url  = ($external ? '' : $site_root).$sa['url'];
				$name = htmlspecialchars($sa['name']);
			}
		}
			$desc = $sa['desc'];
		unset($teazer_content);
		$tpltt = new template('teazer_desc', Template::SOURCE_DB);
		if (preg_match_all('/\{id=(\d{1,})\}/', $desc, $matches, PREG_SET_ORDER)) {
			foreach($matches as $match) {
				$tp_id = intval($match[1]);
				if (!isset($teazer_content[$tp_id])) {
					$teazer_content[$tp_id]['search'] = $match[0];
					$rstp = $db->query(
"SELECT `prod`.`name`, `prod`.`id`, `prod`.`url`, `prod_img`.`id` as `img_id`, `prod`.`price`, `prod`.`price_old`, `manufacturer`.`title` AS `vname`, `model`.`name` AS `mname`
FROM `prod`
INNER JOIN `prod_img` ON `prod_img`.`prod_id`=`prod`.`id` AND `prod_img`.`num`=1
INNER JOIN `manufacturer` ON `manufacturer`.`id`=`prod`.`manufacturer_id`
INNER JOIN `model` ON `model`.`id`=`prod`.`model_id`
WHERE `prod`.`show`<>0 AND `prod`.`id`=$tp_id") or die("Prod Teazer: {$match[0]}".$db->lastError);
					if ($satp = $db->fetch($rstp)) {
						$name = $satp['name'].' '.$i18n->getText('for').' '.$satp['vname'].' '.$satp['mname'];
						$teazer_content[$tp_id]['replace'] = $tpltt->apply (
							array (
								'id'            => $satp['id'],
								'name'          => $name,
								'thumb100'      => $satp['img_id'].'_100.jpg',
								'url'           => $satp['url'],
								'price'         => price($satp['price']),
								'price_old'     => price($satp['price_old']),
								'title'         => htmlspecialchars($name),
								'alt'           => htmlspecialchars($name),
								'l_buy_now'     => $i18n->getText('buy_now')
							)
						);
					}
				}
				if (isset($teazer_content)) {
					foreach ($teazer_content as $tc) {
						$desc = str_replace($tc['search'], $tc['replace'], $desc);
					}
				}
			}
		}
/*
echo "<!--";
print_r($teazer_content);
echo "-->";
*/
		$teazers .= $tpltz->apply (
			array (
				'id'    => $sa['id'],
				'img'   => $img,
				'title' => $name,
				'alt'   => $name,
				'desc'  => $desc,
				'url'   => $url,
				'newwindow' => $sa['newwindow']
			)
		);
	}

	$manufacturers = '<ul>';
	$availableManufacturers = $registry->get('availableManufacturers');
	foreach ($availableManufacturers['by_url'] as $sav) {
		$manufacturers .= '<li><a href="{site_root}manufacturer/'.$sav['url'].'" title="'.htmlspecialchars($sav['title']).'">'.$sav['title'].'</a></li>';
	}
	$manufacturers .= '</ul>';

	$categories = '<ul>';
	$rsc = $db->query('SELECT `name`, `url` FROM `prod_cls` WHERE `show`<>0 ORDER BY `num`') or die('Prod CLS: '.$db->error());
	while ($sac = $db->fetch($rsc)) {
		$categories .= '<li><a href="{site_root}catalog/'.$sac['url'].'" title="'.htmlspecialchars($sac['name']).'">'.$sac['name'].'</a></li>';
	}
	$categories .= '</ul>';

	$tpln   = new template('index_new', Template::SOURCE_DB);
	$tplni  = new template('index_new_item', Template::SOURCE_DB);
	$tplnci = new template('index_new_common_item', Template::SOURCE_DB);

	$sql = 
"SELECT `pc`.`name` AS `name`, `pc`.`id`, `pc`.`url`, `pci`.`id` as `img_id`, `pc`.`price`, `pc`.`price_old`, '' AS `vname`, '' AS `mname`
 FROM `prod_common` `pc`
 INNER JOIN `prod_common_img` `pci` ON `pci`.`prod_common_id`=`pc`.`id` AND `pci`.`num`=1
 WHERE `pc`.`show`<>0 AND `pc`.`new`=1
 UNION
 (SELECT `prod`.`name`, `prod`.`id`, `prod`.`url`, `prod_img`.`id` as `img_id`, `prod`.`price`, `prod`.`price_old`, `manufacturer`.`title` AS `vname`, `model`.`name` AS `mname`
 FROM `prod` 
 INNER JOIN `prod_img` ON `prod_img`.`prod_id`=`prod`.`id` AND `prod_img`.`num`=1
 INNER JOIN `manufacturer` ON `manufacturer`.`id`=`prod`.`manufacturer_id`
 INNER JOIN `model` ON `model`.`id`=`prod`.`model_id`
 WHERE `prod`.`show`<>0 AND `prod`.`new`=1 ORDER BY RAND() LIMIT 7)";

	$rsn = $db->query($sql) or die('New Prod: '.$db->lastError);

	$new_items = '';
	while ($san = $db->fetch($rsn)) {
		if ($san['vname']) {
			$name = $san['name'].' '.$i18n->getText('for').' '.$san['vname'].' '.$san['mname'];
			$ctpl = $tplni;
		}
		else {
			$name = $san['name'];
			$ctpl = $tplnci;
		}
		$new_items .= $ctpl->apply (
			array (
				'id'            => $san['id'],
				'name'          => $name,
				'thumb100'      => $san['img_id'].'_100.jpg',
				'url'           => $san['url'],
				'l_price'       => $i18n->getText('price'),
				'l_buy_now'     => $i18n->getText('add_to_backet'),
				'price'         => price($san['price']),
				'price_old'     => price($san['price_old']),
				'title'         => htmlspecialchars($name),
				'alt'           => htmlspecialchars($name)
			)
		);
	}
	$new_prod = $tpln->apply(
		array(
			'header' => $i18n->getText('new_products'),
			'items'  => $new_items
		)
	);


	$tpl = new template($content_main, Template::SOURCE_VARIABLE);
	$content_main = $tpl->apply (
		array (
			'manufacturers' => $manufacturers,
			'categories'    => $categories,
			'new_prod'      => $new_prod
		)
	);

	}
}