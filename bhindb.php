<?php

/*
Plugin URI: http://shop.pencarian-aman.com/BhinDB
Plugin Name: BhinDB, Bhinneka Database for Bhinneka Affiliates
Description: Display Products from Bhinneka.com Database and display them with your affiliate ID. For affiliates, this plugin with replace the products link to your affiliates link.
Version: 2.0
Date: September 15,2014
Author: Daniel Fernando
/*
Licence: GNU General Public License v3.0
More info: http://www.gnu.org/copyleft/gpl.html
*/

require_once("simple_html_dom.php");

function bhindb_truncate($string, $limit, $break=".", $pad="") { 
    if(strlen($string) <= $limit) return $string;
    if (false !== ($breakpoint = strpos($string, $break, $limit))) {
      if($breakpoint < strlen($string) - 1) {
	    $string = substr($string, 0, $breakpoint) . $pad;
	  }
    }
    return $string;
}

function bhindb_strreplaceassoc(array $replace, $subject) { 
   return str_replace(array_keys($replace), array_values($replace), $subject);    
} 

function bhindb_shortcode( $atts ) {
	extract(shortcode_atts(array('category' => 'none','description' => 'yes','column' => '2','maxitem' => '4'), $atts));
	add_option( 'bhindb_userkey', '' );
	add_option( 'bhindb_secret', '' );
	add_option( 'bhindb_pricemarkup', '' );
	add_option( 'bhindb_pricecol', '' );  
	add_option( 'bhindb_affiliatecode', '' ); 
	
	$userkey = get_option('bhindb_userkey');
	$secret = get_option('bhindb_secret');
	//$pricemarkup = get_option('bhindb_pricemarkup');
	$pricecol = get_option('bhindb_pricecol');
	$affiliatecode = get_option('bhindb_affiliatecode');
	
	if ($pricecol == "") {$pricecol = "FF0000";}
	else {$pricecol = $pricecol;}
	
	$time = gmdate("Y-m-d\TH:i:s\Z");
	
	$uri = "category=" .$category;
	$uri .= "&userkey=$userkey";
	$uri .= "&secret=$secret";
	$uri .= "&pricemarkup=$pricemarkup";
	$uri .= "&column=$column";
	$uri .= "&pricecol=$pricecol";
	$uri .= "&affiliatecode=$affiliatecode";
	$uri .= "&description=$description";
	$uri .= "&maxitem=$maxitem";
	$uri = str_replace(' ','%20', $uri);
	$uri = str_replace(',','%2C', $uri);
	$uri = str_replace(':','%3A', $uri);
	$uri = str_replace('*','%2A', $uri);
	$uri = str_replace('~','%7E', $uri);
	$uri = str_replace('+','%20', $uri);
	$sign = explode('&',$uri);
	
	$host = implode("&", $sign);
	$bhindb_gator = "http://shop.pencarian-aman.com/bhindb_gator.php?".$host;
	$host = "GET\nshop.pencarian-aman.com/bhindb_gator.php?".$host;
	$signed = urlencode(base64_encode(hash_hmac("sha256", $host, $secret, True)));
	$uri .= "&Signature=$signed";
	$ch = curl_init($uri); 
	
	$detail_xml = file_get_html($bhindb_gator);
	//echo "--------------".$detail_xml."==================";
	
	curl_setopt($ch, CURLOPT_HEADER, false); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	$xml = curl_exec($ch); 
	curl_close($ch); 
	
	$pxml = simplexml_load_string($detail_xml); 
	//print_r($pxml);
	$breaklist=0;
	
	//$all = &$pxml->Items->Item;
	//$param = array();
	
	$content = "<h2>$category</h2>";
	foreach($pxml as $item) {
		$replace = array( 
		  '[id]' => $item->id, 
		  '[sku]' => $item->sku, 
		  '[title]' => $item->title,
		  '[link]' => $item->affiliate_url,
		  '[thumbnail]' => $item->thumbnail,
		  '[img]' => $item->thumbnail,
		  '[pricecol]' => $pricecol,
		  '[item_description]' => ($item->product_description==''?'':$item->product_description . "..."),
		  '[price]' => $item->product_price,
		  //'[lprice]' => $item->product_price_number + $pricemarkup,
		  '[product_price_ppn]' => $item->product_price_ppn,
		  '[manufacture_url]' => $item->manufacture_url,
		  '[avab]' => '',
		  '[seed]' => ''	     
		  );
		  /*
		$id = $item->id;
		$sku = $item->sku;
		$title = $item->title;
		$thumbnail = $item->thumbnail;
		$img = $item->thumbnail;
		$item_description = $item->product_description;
		$price = $item->product_price;
		$lprice = $item->product_price_number + 25000;
		$product_price_ppn = $item->product_price_ppn;
		$manufacture_url = $item->manufacture_url;*/
			
		//if ($item_description == "") {$item_description = "";} else { $item_description = $item_description . "...";}
		
		$avab = bhindb_truncate($avab, 69, " ");
		
		$content_1column_with_description = 
			'<div style="width:100%;display:inline-block;">
			<div style="float:left;width:15%;">
			<a href="[link]" rel="nofollow" style="font-size:12px;text-decoration:none;font-weight:600;float:left;"><img src="[img]" style="margin:0;padding:0;float:left;border:none;" /></div>
			<div style="float:left;margin:0px 0 10px 10;width:75%;">
			<a href="[link]" rel="nofollow" style="font-size:12px;text-decoration:none;font-weight:600;" target="_new">[title]</a><br>
			<span style="color: #[pricecol];font-size:11px;text-decoration:none;font-weight:500;">[price]</span> 
			<!--<strike style="color:#444;"><span style="font-size:11px;text-decoration:none;font-weight:500;">[lprice]</span></strike>--><br>
			<a href="[link]" rel="nofollow" style="font-size:12px;text-decoration:none;font-weight:600;float:left;"><span style="font-size:10px;text-decoration:none;font-weight:500;">[item_description]</span>
			<div style="font-size:12px;clear:both;">[avab]</div><a href="[$link]" rel="nofollow" style="text-decoration:none;font-weight:600;">[seed]</a></div></div>';
	
		$content_1column_without_description = 
			'<div style="width:100%;display:inline-block;" >
			<div style="float:left;width:15%;">
			<a href="[link]" rel="nofollow" style="font-size:12px;text-decoration:none;font-weight:600;float:left;"><img src="[img]" style="margin:0;padding:0;float:left;border:none;" /></div>
			<div style="float:left;margin:0px 0 10px 0;width:75%;">
			<a href="[link]" rel="nofollow" style="font-size:12px;text-decoration:none;font-weight:600;" target="_new">[title]</a><br>
			<span style="color: #[pricecol];font-size:11px;text-decoration:none;font-weight:500;">[price]</span> 
			<!--<strike style="color:#444;"><span style="font-size:11px;text-decoration:none;font-weight:500;">[lprice]</span></strike>-->
			<div style="font-size:12px;clear:both;">[avab]</div><a href="[link]" rel="nofollow" style="text-decoration:none;font-weight:600;">[seed]</a></div></div>';
		
		$content_2column_with_description = 
			'<div style="margin-bottom:20px;float:right;width:48%;margin-right:2%;">
			<div style="width:100%;" >
			<a href="[link]" rel="nofollow" style="font-size:12px;text-decoration:none;font-weight:600;float:left;"><img src="[img]" style="margin:0;padding:0;float:left;border:none;" /></div>
			<div style="float:left;margin:0px 0 10px 10; padding:5px; width:75%;">
			<a href="[link]" rel="nofollow" style="font-size:12px;text-decoration:none;font-weight:600;float:left;">[title]</a><br>
			<span style="color: #[pricecol];font-size:11px;text-decoration:none;font-weight:500;">[price]</span>  
			<!--<strike style="color:#444;"><span style="font-size:11px;text-decoration:none;font-weight:500;">[lprice]</span></strike>--><br></div>
			<a href="[link]" rel="nofollow" style="font-size:12px;text-decoration:none;font-weight:600;float:left;"><span style="font-size:10px;text-decoration:none;font-weight:500;">[item_description]</span>
			<div style="font-size:12px;clear:both;">[avab]</div><a href="[$link]" rel="nofollow" style="text-decoration:none;font-weight:600;">[seed]</a></div>';
	
		$content_2column_without_description = 
			'<div style="margin-bottom:20px;float:right;width:48%;margin-right:2%;">
			<div style="width:100%;" >
			<a href="[link]" rel="nofollow" style="font-size:12px;text-decoration:none;font-weight:600;float:left;"><img src="[img]" style="margin:0;padding:0;float:left;border:none;" /></div>
			<div style="float:left;margin:0px 0 10px 10; padding:5px; width:75%;">
			<a href="[link]" rel="nofollow" style="font-size:12px;text-decoration:none;font-weight:600;float:left;">[title]</a><br>
			<span style="color: #[pricecol];font-size:11px;text-decoration:none;font-weight:500;">[price]</span>  
			<!--<strike style="color:#444;"><span style="font-size:11px;text-decoration:none;font-weight:500;">[lprice]</span></strike>--><br></div>
			<div style="font-size:12px;clear:both;">[avab]</div><a href="[$link]" rel="nofollow" style="text-decoration:none;font-weight:600;">[seed]</a></div>';
			
		$content_3column_with_description = 
			'<div style="margin-bottom:20px;float:right;width:29%;margin-right:1%;">
			<div style="width:100%;" >
			<a href="[link]" rel="nofollow" style="font-size:12px;text-decoration:none;font-weight:600;float:left;"><img src="[img]" style="margin:0;padding:0;float:left;border:none;" /></div>
			<div style="float:left;margin:0px 0 10px 10; padding:5px; width:75%;">
			<a href="[link]" rel="nofollow" style="font-size:12px;text-decoration:none;font-weight:600;float:left;">[title]</a><br>
			<span style="color: #[pricecol];font-size:11px;text-decoration:none;font-weight:500;">[price]</span>  
			<!--<strike style="color:#444;"><span style="font-size:11px;text-decoration:none;font-weight:500;">[lprice]</span></strike>--><br></div>
			<div style="clear:both;"><a href="[link]" rel="nofollow" style="font-size:12px;text-decoration:none;font-weight:600;float:left;"><span style="font-size:10px;text-decoration:none;font-weight:500;">[item_description]</span></div>
			<div style="font-size:12px;clear:both;">[avab]</div><a href="[$link]" rel="nofollow" style="text-decoration:none;font-weight:600;">[seed]</a></div>';
	
		$content_3column_without_description = 
			'<div style="margin-bottom:20px;float:right;width:29%;margin-right:1%;">
			<div style="width:100%;" >
			<a href="[link]" rel="nofollow" style="font-size:12px;text-decoration:none;font-weight:600;float:left;"><img src="[img]" style="margin:0;padding:0;float:left;border:none;" /></div>
			<div style="float:left;margin:0px 0 10px 10; padding:5px; width:75%;">
			<a href="[link]" rel="nofollow" style="font-size:12px;text-decoration:none;font-weight:600;float:left;">[title]</a><br>
			<span style="color: #[pricecol];font-size:11px;text-decoration:none;font-weight:500;">[price]</span>  
			<!--<strike style="color:#444;"><span style="font-size:11px;text-decoration:none;font-weight:500;">[lprice]</span></strike>--><br></div>
			<div style="font-size:12px;clear:both;">[avab]</div><a href="[$link]" rel="nofollow" style="text-decoration:none;font-weight:600;">[seed]</a></div>';
			
		if ($column == "1") {
			if ($description == "yes") { $content .= bhindb_strreplaceassoc($replace,$content_1column_with_description); }
			else { $content .= bhindb_strreplaceassoc($replace,$content_1column_without_description); }
		}
		if ($column == "2") {
			if ($description == "yes") {
				$content .= bhindb_strreplaceassoc($replace,$content_2column_with_description);	
			}
			else {
				$content .= bhindb_strreplaceassoc($replace,$content_2column_without_description);
			}
			$i++;
			if(($i % 2)==0){$content .= '<div style="clear:both"></div>';}
		}
		if ($column == "3") {
			if ($description == "yes") {
				$content .= bhindb_strreplaceassoc($replace,$content_3column_with_description);	
			}
			else {
				$content .= bhindb_strreplaceassoc($replace,$content_3column_without_description);
			}
			$i++;
			if(($i % 3)==0){$content .= '<div style="clear:both"></div>';}
		}
	}
	return $content;
}



function add_bhindb_panel() {
	if (function_exists('add_options_page')) {
		add_options_page('BhinDB', 'BhinDB', 8, 'bhindb', 'bhindb_admin_panel');
	}
}

function bhindb_admin_panel() { 
	if ($_POST["bhindb_\165pda\x74e\144"]){
		update_option('bhindb_userkey',$_POST['bhindb_userkey']); 
		update_option('bhindb_secret',$_POST['bhindb_secret']); 
		//update_option('bhindb_pricemarkup',$_POST['bhindb_pricemarkup']);
		update_option('bhindb_pricecol',$_POST['bhindb_pricecol']);
		update_option('bhindb_affiliatecode',$_POST['bhindb_affiliatecode']);

		echo '<div id="message" style="padding:2px 2px 2px 4px; font-size:12px;" class="updated"><strong>BhinDB settings updated</strong></div>';}?>

        <div class="wrap">
        <div style="width:99%; height:570px;">
        <div style="float:left;width:65%;margin-right:4%;">
        <h3 style="color:#0066cc;">BhinDB Options</h3>	
        <form method="post" id="cj_options">
        <table cellspacing="10" cellpadding="5" > 
        	<tr valign="top">
                <td colspan="2"><strong>All will have this general settings, but only for max 100 times executions.</strong></td>
            </tr>
            <tr valign="top">
                <td colspan="2"><strong><a href="http://shop.pencarian-aman.com/contact-us/">Premium Users</a> will also have this settings activated, with unlimited executions.</strong></td>
            </tr>
            <tr valign="top">
                <td width="17%"><strong>BhinDB User Key</strong></td>
                <td><input type="text" name="bhindb_userkey" id="bhindb_userkey" value="<?php echo get_option('bhindb_userkey');?>" maxlength="100" style="width:400px;" />
                <p>To get your keys, <a href="http://shop.pencarian-aman.com/wp-login.php?action=register">register</a> your site and get a free token for that site.<br>
                <font style="color:#D3133E;">
                <li>Please make sure to fill the email and website that you want to run this plugin from. Your website will be whitelisted to ensure best performance at our side. 
                <li>Please make sure that there are no spaces before or after the token.
                <li>User Key and Secret will be manually generated. Please be patient.</font></td>
            </tr>					
            <tr valign="top">
                <td><strong>BhinDB Secret</strong></td>
                <td><input type="text" name="bhindb_secret" id="bhindb_secret" value="<?php echo get_option('bhindb_secret');?>" maxlength="100" style="width:400px;" /></br></td>
            </tr>
            <!--<tr valign="top">
                <td><strong>Price Mark Up</strong></td>
                <td><input type="text" name="bhindb_pricemarkup" id="bhindb_pricemarkup" value="<?php echo get_option('bhindb_pricemarkup');?>" maxlength="10"/>
                <p>Price being displayed is real price plus Price Mark Up. Use Number only for price mark up, or use number and % to mark up based on percentage.</br>This is a future improvement for resellers.</td>
            </tr>-->
            <tr valign="top">
                <td><strong>Price Color</strong></td>
                <td><input type="text" name="bhindb_pricecol" id="bhindb_pricecol" value="<?php echo get_option('bhindb_pricecol');?>" maxlength="7" style="width:100px;" />
                <p>Enter color code (6 characters). For example <strong>FFA500</strong> for orange or <strong>000000</strong> for black. You can find all the color codes <a href="http://quackit.com/html/html_color_codes.cfm" target="_blank">here</a> . *If left blank will be automaticaly set to red</br></td>
            </tr>
            <tr valign="top">
                <td><strong>Category</strong></td>
                <td>Select from <a href="http://shop.pencarian-aman.com/bhindb/category-available/" target="_new">this list</a> and put it in the shortcode
                <?
					global $wpdb;
					$results = $wpdb->get_results("SELECT DISTINCT product_category FROM wp_products ORDER BY product_category;");

					foreach($results as $result) {						
						//echo '<option value="'.$result->product_category.'" ';
						//if(get_option('bhindb_category')==$result->product_category) echo 'selected';
						//echo '>'.$result->product_category.'</option>';
						echo '<li>'.$result->product_category;
					}
                ?>
                </td>
			</tr>        
            <tr valign="top">
                <td><strong>Bhinneka Affiliate code</strong></td>
                <td><input type="text" name="bhindb_affiliatecode" id="bhindb_affiliatecode" value="<?php echo get_option('bhindb_affiliatecode');?>" maxlength="40" style="width:300px;" />
                <p>Enter Bhinneka Affiliate code. You must register your site in Premium Version to get this code working. You also required to get your affiliation code from here.
                </td>
            </tr>            
        </table>
        <p class="submit"><input type="submit" name="bhindb_updated" value="Update Settings &raquo;" /></p>
        </p>
        </form>
        </div>
        </div>	
    <hr />
    <div style="padding:0px 15px 15px 15px; margin:10px 0 0 0;border:3px solid #ccc;width:90%;">
        <h3 style="color:#0066cc;">Please Read - How to use bhindb</h3>
        <p style="margin:30px 0 0 0;">Code format: <strong>[bhindb category='Micro Secure Digital / Micro SD Card' description='yes' column='2' maxitem='4']</strong></p>
        <ul style="list-style:square;padding: 0 0 10px 30px;">
        <li><strong>category</strong> = products you would like to display. Please do not use special characters, like &, @ etc.</li>
        <li><strong>description</strong> = yes (show description) or no (no description will be shown).</li>
        <li><strong>column</strong> = products will be splitted to 1 to 3 columns.</li>
        <li><strong>maxitem</strong> = Maximum Item to be displayed per category</li>
        <p><span style="font-weight:600;">Note</span> - In order the code to work properly you must set the values for the <strong>category</strong>. The other are optional.
        </ul>
        <p style="margin:30px 0 30px 0;font-size:15px;"><strong>For feature requests and more help <a href="http://wordpress.org/extend/plugins/bhindb/" target="_blank">visit plugin site</a></strong></p></td>
    </div>
    </div>

<?php
}

add_shortcode('bhindb', 'bhindb_shortcode');
add_action('admin_menu', 'add_bhindb_panel'); ?>