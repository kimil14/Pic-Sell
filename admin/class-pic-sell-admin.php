<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://portfolio.cestre.fr
 * @since      1.0.0
 *
 * @package    Pic_Sell
 * @subpackage Pic_Sell/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 *
 * @package    Pic_Sell
 * @subpackage Pic_Sell/admin
 * @author     Benjamin CESTRE <benjamin@cestre.fr>
 */
class Pic_Sell_Admin
{

	private $plugin_name;
	private $version;

	private $menu_slug;

	private $menu_admin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		define( 'PIC_SELL_ADMIN_PATH', plugin_dir_path( __FILE__ ));
		define( 'PIC_SELL_ADMIN_URL', plugin_dir_url( __FILE__ ));

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->menu_slug = "picsell";

		add_action('admin_init', array($this, 'pic_register_settings'));
		add_action('admin_init', array($this, 'add_htaccess'));
		add_action('admin_init', array($this, 'add_post_meta_box'));

		add_action('save_post', array($this, 'save_post_meta_box')); //espace prive
		add_action('save_post', array($this, 'save_post_meta_box_offer')); //offres

		add_action('wp_ajax_fiu_upload_file', array($this, 'fiu_upload_file'));
		add_action('wp_ajax_nopriv_fiu_upload_file', array($this, 'fiu_upload_file'));

		add_action('wp_ajax_pic_template_sent_gallery', array($this, 'pic_template_sent_gallery'));
		add_action('wp_ajax_nopriv_pic_template_sent_gallery', array($this, 'pic_template_sent_gallery'));		

		add_action('wp_ajax_fiu_upload_file_video', array($this, 'fiu_upload_file_video'));
		add_action('wp_ajax_nopriv_fiu_upload_file_video', array($this, 'fiu_upload_file_video'));

		add_filter('default_content', array($this, 'set_default_values'), 10, 2);//password auto sur post espaceprive
		
		add_action('admin_menu', array($this, 'pic_admin_menu'));
		
		add_filter('manage_offre_posts_columns', array($this, 'ps_edit_column'));
		add_action( 'manage_offre_posts_custom_column', array($this, 'ps_change_row_title'), 10, 2);
		
	}


	public function function_to_perform($arg1)
	{
		foreach($arg1["mail"] as $name_template => $template){
			$arg1["mail"][$name_template] = wpautop($template);
		}
		return $arg1;
	}
		
	function ps_edit_column($columns)
	{
		$columns['pack_default'] = "Default";
		$columns['title'] = "Title pack";
		return $columns;
	}
	
	function ps_change_row_title($column, $post_id ) 
	{
		switch ( $column ) {
			case 'pack_default' :
				$default = get_post_meta($post_id, '_pack_offer_default', true);
				if ($default) {
					echo '<span style="color:green;">'; _e('Yes', 'pic_sell_plugin'); echo '</span>';
				} else {
					echo '<span style="color:red;">'; _e('No', 'pic_sell_plugin'); echo '</span>';
				}
				break;
		}
	}

	private function pic_create_main_menu($menu_slug, $name,$capability, $pos, $cb){

		$page = add_menu_page(
			$name,
			$name,
			$capability,
			$menu_slug,
			$cb,
			'',
			$pos
		);	

	}

	private function pic_create_sub_menu($menu_slug, $name,$capability, $pos, $cb, $gn=null){
		if($gn != null){
			$gn = "_".$gn;
		}else{ $gn = "";}
		
		$page = add_submenu_page(
			$menu_slug,
			$name,
			$name,
			$capability,
			$menu_slug.$gn,
			$cb,
			$pos
		);		
	}

	public function pic_register_settings() {

		register_setting("settings-pic", "builder_pic");
		register_setting("settings-pic", "paypal_pic");
		register_setting("settings-pic", "config_pic");
		register_setting("settings-pic", "template_pic", array($this, 'function_to_perform'));
		register_setting("commands-pic", "allcommands_pic");

	}

	function pic_admin_menu()
	{
		$this->pic_create_main_menu($this->menu_slug, __("Pic Sell", "pic_sell_plugin"), "manage_options",4,array($this, 'picsell_menu_dashboard'));
		$this->pic_create_sub_menu($this->menu_slug, __("Dashboard", "pic_sell_plugin"), "manage_options",1, array($this, 'picsell_menu_dashboard'));
		$this->pic_create_sub_menu($this->menu_slug, __("Settings", "pic_sell_plugin"), "manage_options",2, array($this, 'picsell_page_settings'), "settings-pic");
		$this->pic_create_sub_menu($this->menu_slug, __("Orders", "pic_sell_plugin"), "manage_options",3, array($this, 'picsell_page_commande'), "page_commandes");
	}

	
	public function picsell_page_settings(){
		$settings_fields = 'settings-pic';

        $tabs = array(
            0  => apply_filters('picsell/admin/builder/h2',__( 'Builder', 'pic_sell_plugin' )),
			1  => apply_filters('picsell/admin/config/h2',__( 'Config', 'pic_sell_plugin' )),
			2  => apply_filters('picsell/admin/menu/template_mail/h2',__( 'Template mail', 'pic_sell_plugin' ))
        );
		$tabs = apply_filters('picsell/admin/menu/settings', $tabs);

        $html = '<h2 class="nav-tab-wrapper">';
        foreach( $tabs as $tab => $name ){
           // $class = ( $tab == $current_tab ) ? 'nav-tab-active' : '';
            $html .= '<a class="nav-tab nav-pic-'.$tab.' nav-tab-'.$tab.'" href="?page='.$this->menu_slug.'_settings-pic#nav-pic-' . $tab . '">' . $name . '</a>';
        }
        $html .= '</h2>'; 

		$html .= '<form method="post" action="options.php" enctype="multipart/form-data">';

		$html .= "<input type='hidden' name='option_page' value='" . esc_attr( $settings_fields ) . "' />";
		$html .= '<input type="hidden" name="action" value="update" />';
		$html .= '<input type="hidden" name="_wp_http_referer" value="' . esc_attr(( $_SERVER['REQUEST_URI'] ) ) . '" />';
		$html .= wp_nonce_field( "$settings_fields-options" ,'_wpnonce', false, false );


        $html .= '<div class="content">';

	   $html .= "<div class='nav-pic-0'>";
	   $html .= "<h2>".$tabs[0]."</h2>";

	   $builder = get_option('builder_pic'); 
	   $header_background = isset($builder["builder"]["header"]["background"])?$builder["builder"]["header"]["background"]:"";

	   $isDisplay = !empty($builder["builder"]["header"]["background"]) ? true:false;
	   $html .= "<div class='bloc'>";
	   $html .= 	"<h3>Header</h3>";
	   $html .= 	"<label for=''>Background</label>";	
	   $html .=		"<div class='show-image'>";			
	   $html .= 		"<img src='".($isDisplay?$header_background:"")."' id='header-image-preview' style='max-height:80px;margin:6px;".($isDisplay?"":"display:none;")."' />";
	   $html .= 		"<span style='display:none;' class='header-image-preview-remove dashicons dashicons-trash'></span>";
	   $html .= 		"<span style='display:none;' class='header-image-preview-edit dashicons dashicons-edit'></span>";
	   $html .= 	"</div>";
	   $html .= 	"<input type='hidden' id='builder-header' value='{$header_background}' name='builder_pic[builder][header][background]' />";
	   $html .= 	"<a class='button picsell-upload'>Upload</a>";
	   $html .= "</div>";

	   $html .= "</div>";


	   $html .= "<div class='nav-pic-1'>";
	   $html .= "<h2>".$tabs[1]."</h2>";
   
	   $paypal = get_option('paypal_pic'); 
	   $paypal_address_mail = isset($paypal["paypal"]["adresse"])?$paypal["paypal"]["adresse"]:"";
	   $paypal_sandbox = (isset($paypal["paypal"]["sandbox"])&&$paypal["paypal"]["sandbox"])?true:false;

	   $html .= "<div class='bloc'>";
	   $html .= 	"<h3>Paypal</h3>";
	   $html .= 	"<label for='paypal-address-mail'>Adresse Mail</label>";	
	   $html .= 	"<input type='text' id='paypal-address-mail' value='{$paypal_address_mail}' name='paypal_pic[paypal][adresse]' />";
	   $html .= 	"<p>".__( 'Paypal seller email address', 'pic_sell_plugin' )."<p/>";
	   $html .= "</div>";
	   $html .= "<div class='bloc'>";
	   $html .= 	"<label for='paypal-sandbox'>Sandbox</label>";	
	   $html .= 	"<input type='checkbox' id='paypal-sandbox' " . ($paypal_sandbox ? "checked":"") . " name='paypal_pic[paypal][sandbox]' />";
	   $html .= 	"<p class='desc'>".__( 'Transform the url to access the Paypal sandbox', 'pic_sell_plugin' )."<p/>";
	   $html .= "</div>";


	   $config = get_option('config_pic'); 
	   $admin_address_mail = isset($config["config"]["adresse"])?$config["config"]["adresse"]:"";

	   $html .= "<div class='bloc'>";
	   $html .= 	"<h3>Administrateur</h3>";
	   $html .= 	"<label for='admin-address-mail'>Adresse Mail</label>";	
	   $html .= 	"<input type='text' id='admin-address-mail' value='{$admin_address_mail}' name='config_pic[config][adresse]' />";
	   $html .= "</div>";


	   $cron = get_option('cron_pic'); 
	   $galery_cron = (isset($config["cron"]["active"])&&$config["cron"]["active"])?true:false;
	   $html .= "<div class='bloc'>";
	   $html .= 	"<label for='admin-galery-cron'>".__( 'Active cron', 'pic_sell_plugin' )."</label>";
	   $html .= 	"<input type='checkbox' id='admin-galery-cron' " . ($galery_cron ? "checked":"") . " name='config_pic[cron][active]' />";
	   $html .= 	"<p class='desc'>".__( 'Enable scheduled tasks (1 visitor must be on the site to run the script).', 'pic_sell_plugin' )."<p/>";
	   $html .= 	"<p style='width:100%;'>Last Check: ".$cron."<p/>";
	   $html .= "</div>";

	   $html .= "</div>";


	   $html .= "<div class='nav-pic-2'>";
	   $html .= "<h2>".$tabs[2]."</h2>";
	   $template = get_option('template_pic'); 
	   $template_galery_ready_default = '<p>Bonjour,</p>
	   <p>Retrouvez votre séance photo, {{title}}, à cette adresse :</p><br>
	   <div style="background:#C9C9C9;padding:24px 12px;">
		 Lien : <a style="color: #0d6efd" href="{{permalink}}?utm_source=referral&utm_medium=email&utm_campaign=galleryIsReady&utm_content=link">{{permalink}}</a><br>
		 Votre mot de passe est : <b><i>{{password}}</i></b>
	   </div><br>
	   <p>Votre galerie reste accessible pendant {{dateleft}} à partir d\'aujourd\'hui.</p>
	   <p><i>PS : Sur Safari, des problèmes d\'affichage peuvent survenir.</i></p>
	   <p>Nous restons à votre disposition pour toutes informations complémentaires,<br><br>
	   <i>{{site_name}}</i>
	   </p>';

	   $template_galery_ready = isset($template["mail"]["galery_ready"]) && !empty($template["mail"]["galery_ready"])?$template["mail"]["galery_ready"]:$template_galery_ready_default;
	   $html .= "<div class='bloc'>";
	   $html .= 	"<h3>".__( 'Galery publish', 'pic_sell_plugin' )."</h3>";
	   /** */
	   ob_start();
	   $settings =   array(
		   'wpautop' => true, // enable auto paragraph?
		   'media_buttons' => false, // show media buttons?
		   'textarea_name' => "template_pic[mail][galery_ready]", // id of the target textarea
		   'textarea_rows' => get_option('default_post_edit_rows', 10), // This is equivalent to rows="" in HTML
		   'tabindex' => '',
		   'editor_css' => '', //  additional styles for Visual and Text editor,
		   'editor_class' => 'textarea_template', // sdditional classes to be added to the editor
		   'teeny' => true, // show minimal editor
		   'dfw' => false, // replace the default fullscreen with DFW
		   'tinymce' => array(
			   'height'=>500,
			   'toolbar1'=> 'bold,italic,underline,bullist,numlist,link,unlink,forecolor,undo,redo,',
		   ),
		   'quicktags' => array(
			   // Items for the Text Tab
			   'buttons' => 'strong,em,underline,ul,ol,li,link,code'
		   )
	   );
	   //$defaults = array('textarea_name' => 'template_pic[mail][galeryready]', 'editor_class' => 'textarea_template', 'textarea_rows' => 10, 'teeny' => true);
		wp_editor($template_galery_ready, 'admin-template-galery-ready', $settings);
	   $temp = ob_get_clean();
	   $html .= 	"<label for='admin-template-galery-ready'>Template Mail</label>";
	   $html .= $temp;
	   /** */
	   $html .= "</div>";

	   $galery_ready_send_mail_admin = (isset($config["mail"]["galeryready"])&&$config["mail"]["galeryready"])?true:false;
	   $html .= "<div class='bloc'>";
	   $html .= 	"<label for='admin-galery-ready-send-mail-admin'>".__( 'Send mail', 'pic_sell_plugin' )."</label>";
	   $html .= 	"<input type='checkbox' id='admin-galery-ready-send-mail-admin' " . ($galery_ready_send_mail_admin ? "checked":"") . " name='config_pic[mail][galeryready]' />";
	   $html .= 	"<p class='desc'>".__( 'sends an email to the administrator when a gallery is published.', 'pic_sell_plugin' )."<p/>";
	   $html .= "</div>";


	   $template_galery_interval_default = '<p>Bonjour,</p>
	   <p>Votre espace photo "{{title}}" créé le {{datecreate}} a une validité de {{datevalidite}}. Il expire dans {{dateleft}} à compter de cet email.</p> 
	   <p>Pour rappel, votre lien d\'accès est :</p>
	   <div style="background:#C9C9C9;padding:24px 12px;">
		   Lien : <a style="color: #0d6efd" href="{{permalink}}?utm_source=referral&utm_medium=email&utm_campaign=galleryIsReady&utm_content=link">{{permalink}}</a><br>
		   Votre mot de passe est : <b><i>{{password}}</i></b>
	   </div><br>
	   <p>Je reste à votre disposition pour des informations complémentaires,<br><br>Amicalement,<br><br>
	   <i>{{site_name}}</i>
	   </p>';

	   $template_galery_interval = isset($template["mail"]["galery_interval"]) && !empty($template["mail"]["galery_interval"])?$template["mail"]["galery_interval"]:$template_galery_interval_default;
	   $html .= "<div class='bloc'>";
	   $html .= 	"<h3>".__( 'Remaining time', 'pic_sell_plugin' )."</h3>";
	   /** */
	   ob_start();
	   $settings =   array(
		   'wpautop' => true, // enable auto paragraph?
		   'media_buttons' => false, // show media buttons?
		   'textarea_name' => "template_pic[mail][galery_interval]", // id of the target textarea
		   'textarea_rows' => get_option('default_post_edit_rows', 10), // This is equivalent to rows="" in HTML
		   'tabindex' => '',
		   'editor_css' => '', //  additional styles for Visual and Text editor,
		   'editor_class' => 'textarea_template', // sdditional classes to be added to the editor
		   'teeny' => true, // show minimal editor
		   'dfw' => false, // replace the default fullscreen with DFW
		   'tinymce' => array(
			   'height'=>500,
			   'toolbar1'=> 'bold,italic,underline,bullist,numlist,link,unlink,forecolor,undo,redo,',
		   ),
		   'quicktags' => array(
			   'buttons' => 'strong,em,underline,ul,ol,li,link,code'
		   )
	   );
	   //$defaults = array('textarea_name' => 'template_pic[mail][galeryready]', 'editor_class' => 'textarea_template', 'textarea_rows' => 10, 'teeny' => true);
		wp_editor($template_galery_interval, 'template_galeryinterval', $settings);
	   $temp = ob_get_clean();
	   $html .= 	"<label for='admin-template-galery-interval'>Template Mail</label>";
	   $html .= $temp;
	   /** */
	   $html .= "</div>";

	   $galery_interval_send_mail_admin = (isset($config["mail"]["galeryinterval"])&&$config["mail"]["galeryinterval"])?true:false;
	   $html .= "<div class='bloc'>";
	   $html .= 	"<label for='admin-galery-interval-send-mail-admin'>".__( 'Send mail', 'pic_sell_plugin' )."</label>";
	   $html .= 	"<input type='checkbox' id='admin-galery-interval-send-mail-admin' " . ($galery_interval_send_mail_admin ? "checked":"") . " name='config_pic[mail][galeryinterval]' />";
	   $html .= 	"<p class='desc'>".__( 'Send an email to the administrator when a gallery changes status.', 'pic_sell_plugin' )."<p/>";
	   $html .= "</div>";
	   $html .= "</div>";
	   

        $html .= '</div>';
		$html .= get_submit_button(); 
		$html .= '</form>';
        echo wp_kses_normalize_entities($html);    
	}

	public function picsell_menu_dashboard(){
		echo wp_kses_normalize_entities("<h2>Dashboard</h2>");   
	}

	public function picsell_page_commande(){

		$settings_fields = 'commands-pic';
        $tabs = array(
            0   => apply_filters('picsell/admin/menu/all_commands/h2', __( 'All commands', 'pic_sell_plugin' )), 
            1  => apply_filters('picsell/admin/menu/custommers/h2',__( 'Custommers', 'pic_sell_plugin' ))
        );
		$tabs = apply_filters('picsell/admin/menu/commands', $tabs);

        $html = '<h2 class="nav-tab-wrapper">';
        foreach( $tabs as $tab => $name ){
           // $class = ( $tab == $current_tab ) ? 'nav-tab-active' : '';
            $html .= '<a class="nav-tab nav-pic-'.$tab.' nav-tab-'.$tab.'" href="?page='.$this->menu_slug.'_page_commandes&tab=' . $tab . '">' . $name . '</a>';
        }
        $html .= '</h2>'; 

		$html .= '<form method="post" action="options.php" enctype="multipart/form-data">';

		$html .= "<input type='hidden' name='option_page' value='" . esc_attr( $settings_fields ) . "' />";
		$html .= '<input type="hidden" name="action" value="update" />';
		$html .= wp_nonce_field( "$settings_fields-options" ,'_wpnonce', true, false );

        $html .= '<div class="content">';
		// $html .= $tabs[$current_tab][1];
		$defaultOrders = array("orders"=>[]);
		$allOrders = get_option('allcommands_pic', serialize(json_encode($defaultOrders)));
		$allOrders = json_decode(unserialize($allOrders), true);
		$isOrders = false;
		if($allOrders == $defaultOrders ) {
			$paragraphe = "<p>".__( 'There are no orders at the moment.', 'pic_sell_plugin' )."</p>";
		} elseif ( $allOrders == false ) {
			$paragraphe = "<p>".__( 'There are no orders at the moment.', 'pic_sell_plugin' )."</p>";
			// Option's value is equal to false
		}else{
			$paragraphe = "<p>".__( 'Lists orders.', 'pic_sell_plugin' )."</p>";
			$isOrders = true;
		}


		$html .= "<div class='nav-pic-0'>";
		$html .= 	"<h1>".__( '<b>all orders</b>.', 'pic_sell_plugin' )."</h1>";
		$html .= 	$paragraphe;

		if($isOrders){
			$html .=    "<table>";
			$html .= 		"<thead>";
			$html .= 			"<tr>";
			$html .= 				"<th>".__( '<b>Order ID</b>', 'pic_sell_plugin' )."</th>";
			$html .= 				"<th>".__( '<b>Order number</b>', 'pic_sell_plugin' )."</th>";
			$html .= 				"<th>".__( '<b>Order date</b>', 'pic_sell_plugin' )."</th>";
			$html .= 			"</tr>";
			$html .= 		"</thead>";

			$html .= 		"<tbody>";
			
			foreach($allOrders["orders"] as $key => $order){
				$html .= "<tr>";
				$html .= "<td>$key</td>";
				foreach($order as $number_order => $card){
					$html .= "<td>$number_order</td>";
					$html .= "<td>$card[order_date]</td>";
				}	
				$html .= "</tr>";
			}		

			
			$html .= 		"</tbody>";		
			$html .=    "</table>";			
		}


		$html .= "</div>";
	


		$html .= "<div class='nav-pic-1'>";
		$html .= 	"<h1>".__( '<b>All customers</b>.', 'pic_sell_plugin' )."</h1>";
		$html .= "</div>";

        $html .= '</div>';
		$html .= get_submit_button(); 
		$html .= '</form>';

        echo wp_kses_normalize_entities($html);      
	}



	public function set_default_values($post_content, $post)
	{

		if ($post->post_type !== "espaceprive") {
			return;
		}

		$comb = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$shfl = str_shuffle($comb);
		$pwd = substr($shfl, 0, 8);

		$post->post_status = 'password';
		$post->post_password = $pwd;

		return $post_content;
	}

	public function add_htaccess()
	{
		$basedir = wp_upload_dir();
		$htacess = $basedir["basedir"] . '/pic_sell/.htaccess';
		if (!file_exists($htacess)) {
			$content = '#GENERATED BY PIC SELL PLUGIN' . "\n";
			$content .= '<FilesMatch "\.(?:jpg|JPG|JPEG|jpeg|png|PNG|mp4|MP4|mp3|avi)$">' . "\n";
			$content .= 'Order allow,deny' . "\n";
			$content .= 'Deny from all' . "\n";
			$content .= '</FilesMatch>' . "\n\n";

			file_put_contents($htacess, $content);
		}
	}

	private function get_cat_by_type_post($post_type, $taxonomy){
		$args = array(
			'type'                     => $post_type,
			'taxonomy'                 => $taxonomy,
			'hide_empty'               => 0
		);

		$cats = get_categories($args);
		return $cats;
	}

	public function add_post_meta_box()
	{
		/**
		 * ESPACE PRIVE
		 */
		$gallery_field = function ($i, $value) {

			$html = "";

			$bmedia = wp_upload_dir()["basedir"] . esc_html($value['media_dir'][$i]);


			$type = pathinfo($bmedia, PATHINFO_EXTENSION);

			$finfo = new finfo(FILEINFO_MIME); // Retourne le type mime
			/* Récupère le mime-type d'un fichier spécifique */
			$media_info = $finfo->file($bmedia);
			$genre_media = explode("/", $media_info)[0];

			if ($genre_media == "image") {

				list($width_orig, $height_orig) = getimagesize($bmedia);
				$data = file_get_contents($bmedia);

				/**
				 * REDIMMENSIONNEMENT
				 * Passage en parametre admin sur la prochaine version
				 */
				$theme_image_little = imagecreatefromstring($data);  
				$w = $width_orig / 3;
				$h = $height_orig / 3;
				$image_little = imagecreatetruecolor($w, $h);
				imagecopyresampled($image_little, $theme_image_little, 0, 0, 0, 0, $w, $h, $width_orig, $height_orig);
				ob_start();
				imagepng($image_little);
				$contents =  ob_get_contents();
				ob_end_clean();
				$theme_image_enc_little = base64_encode($contents);
				$base64 = 'data:' . $genre_media . '/' . $type . ';base64,' .$theme_image_enc_little;
			} 

			$html .= 	"<tr>";
			$html .= 		"<td class='ps_classement'>";
			$html .= 			"<span class='ps_classement_span'></span>";
			$html .= 			"<input type='hidden' class='ps_classement_input' name='gallery[classement][]' value='" . esc_html($value['classement'][$i]) . "'/>";
			$html .= 		"</td>";
			$html .= 		"<td class='ps_choice'>";
			$html .= 			"<select class='ps_choice_image_select' name='gallery[choice][]'>";
			$html .= 				"<option value='select'>" . __('Select media option', 'pic_sell_plugin') . "</option>";
			$html .= 				"<option value='image' ".(("image" == esc_html($value['choice'][$i])) ? 'selected':'').">" . __('Image', 'pic_sell_plugin') . "</option>";
			$html .= 				"<option value='video' ".(("video" == esc_html($value['choice'][$i])) ? 'selected':'').">" . __('Video', 'pic_sell_plugin') . "</option>";
			$html .= 			"</select>";
			$html .= 		"</td>";
			$html .= 		"<td class='ps_media'>";
			if ($genre_media == "image") {
				$html .= 		"<img src='" . $base64 . "' class='ps_display_image' style='width:auto;max-height:140px;display:block;' />";
			} else if ($genre_media == "video") {
				$html .= 		"<video  controls width='200' oncontextmenu='return false;' controlsList='nodownload' class='ps_display_video buffer' style='max-width:95%;display:block;'>
										<source data-url='" . $bmedia . "' src='".PIC_SELL_URL_INC."pic-sell-handlerStream.php?url=" . $bmedia . "' type='video/mp4'>
										Sorry, your browser doesn't support embedded videos.
									</video>";
				$html .= "<p class='ps-upload-progress'></p>";
			} else {
				$html .= "<p style='color:red;'>" . __('Error in type file', 'pic_sell_plugin') . "</p>";
			}
			$html .= 			"<input type='file' class='ps_upload_image_button button' style='display:none;' value='" . __('Add image', 'pic_sell_plugin') . "' />";
			$html .= 			"<input type='file' class='ps_upload_video_button button' style='display:none;' value='" . __('Add video', 'pic_sell_plugin') . "' />";
			$html .= 			"<div class='ps-upload-progress'><p class='uploading'>" . __('Uploading progress', 'pic_sell_plugin') . " <span></span></p><p class='finished'>" . __('Upload finished', 'pic_sell_plugin') . " <span></span></p></div>";
			$html .= 			"<input type='hidden' class='ps_media_dir' name='gallery[media_dir][]' value='" . esc_html($value['media_dir'][$i]) . "' />";
			$html .= 		"</td>";
			$html .= 		"<td class='ps_media_title'>";
			$html .= 			"<input type='text' class='ps_media_title' name='gallery[media_title][]' value='" . esc_html($value['media_title'][$i]) . "' />";
			$html .= 		"</td>";
			$html .= 		"<td class='ps_media_desc'>";
			$html .= 			"<textarea class='ps_media_desc' name='gallery[media_desc][]'>" . esc_html($value['media_desc'][$i]) . "</textarea>";
			$html .= 		"</td>";
			$html .= 		"<td class='param'>";
			$html .= 			"<a href='#' class='ps_remove_line_button button' style='display:inline-block;'>" . __('Remove line', 'pic_sell_plugin') . "</a>";
			$html .= 			"<a href='#' class='ps_remove_media_button button' style='display:inline-block;'>" . __('Remove media', 'pic_sell_plugin') . "</a>";
			$html .= 		"</td>";
			$html .= 	"</tr>";

			return $html;
		};
		$callback =  function ($post) use ($gallery_field) {

			wp_nonce_field('espaceprive_save_meta_box_data', 'espaceprive_meta_box_nonce');

			$gallery_data = get_post_meta($post->ID, '_gallery_data', true);
			$email_client = get_post_meta($post->ID, '_email_client', true);
			$panier_client = get_post_meta($post->ID, 'panier_client', true);
			$produit = get_post_meta($post->ID, 'produit_client', true);

			$date_left = get_post_meta($post->ID, '_date_left', true);

			$output = "";

			$output .= "<div class='dynamic_form'>";

			$output .= '<label for="field_wrap">';
			$output .= 		__('Gallery', 'pic_sell_plugin');
			$output .= '</label> ';


			$output .= "<table id='field_wrap'>";

			$output .= 		"<thead>";
			$output .= 			"<tr class='tr_head'>
									<th scope='col'>" . __('Classement', 'pic_sell_plugin') . "</th>
									<th scope='col'>" . __('Type media', 'pic_sell_plugin') . "</th>
									<th scope='col'>" . __('Media', 'pic_sell_plugin') . "</th>
									<th scope='col'>" . __('Title', 'pic_sell_plugin') . "</th>
									<th scope='col'>" . __('Description', 'pic_sell_plugin') . "</th>
									<th scope='col'>" . __('Actions', 'pic_sell_plugin') . "</th>
								</tr>";

			$output .= 		"</thead>";

			$output .= 		"<tbody>";
					if (isset($gallery_data['media_dir'])) {
						for ($i = 0; $i < count($gallery_data['media_dir']); $i++) {
							$output .= $gallery_field($i, $gallery_data);
						}
					}
			$output .= 		"</tbody>";

			$output .= "</table>";

			$output .= "<input class='button button-primary ps_add_tr' type='button' value='" . __('Add field', 'pic_sell_plugin') . "' onclick='add_field_row();' />";

			/**
			 * MODELE LIGNE TABLEAU
			 */
			$modele = 	"<div id='master-row' style='display:none;'>";
			$modele .= 		"<modele_tr>";
			$modele .= 			"<modele_td class='ps_classement'>";
			$modele .= 				"<span class='ps_classement_span'></span>";
			$modele .= 				"<input type='hidden' class='ps_classement_input' name='gallery[classement][]' value='' />";
			$modele .= 			"</modele_td>";
			$modele .= 			"<modele_td class='ps_choice'>";
			$modele .= 				"<select class='ps_choice_image_select' name='gallery[choice][]'>";
			$modele .= 					"<option value='select'>" . __('Select media option', 'pic_sell_plugin') . "</option>";
			$modele .= 					"<option value='image'>" . __('Image', 'pic_sell_plugin') . "</option>";
			$modele .= 					"<option value='video'>" . __('Video', 'pic_sell_plugin') . "</option>";
			$modele .= 				"</select>";
			$modele .= 			"</modele_td>";
			$modele .= 			"<modele_td class='ps_media'>";
			$modele .= 				"<input type='file' class='ps_upload_image_button button' style='display:none;' value='" . __('Add image', 'pic_sell_plugin') . "' />";
			$modele .= 				"<input type='file' class='ps_upload_video_button button' style='display:none;' value='" . __('Add video', 'pic_sell_plugin') . "' />";
			$modele .= 				"<img src='' class='ps_display_image' style='max-width:95%;display:none;' />";
			$modele .= 				"<video controls oncontextmenu='return false;' controlsList='nodownload' width='250' class='ps_display_video' style='max-width:95%;display:none;'>
											<source src='' type='video/mp4'>
											Sorry, your browser doesn't support embedded videos.
										</video>";
			$modele .= 				"<div class='ps-upload-progress'><p class='uploading'>" . __('Uploading progress', 'pic_sell_plugin') . " <span></span></p><p class='finished'>" . __('Upload finished', 'pic_sell_plugin') . " <span></span></p></div>";
			$modele .= 				"<input type='hidden' class='ps_media_dir' name='gallery[media_dir][]' value='' />";
			$modele .= 			"</modele_td>";
			$modele .= 			"<modele_td class='ps_media_title'>";
			$modele .= 				"<input type='text' class='ps_media_title' name='gallery[media_title][]' value='' />";
			$modele .= 			"</modele_td>";
			$modele .= 			"<modele_td class='ps_media_desc'>";
			$modele .= 				"<textarea class='ps_media_desc' name='gallery[media_desc][]'></textarea>";
			$modele .= 			"</modele_td>";
			$modele .= 			"<modele_td class='param'>";
			$modele .= 				"<a href='#' class='ps_remove_line_button button' style='display:inline-block;'>" . __('Remove line', 'pic_sell_plugin') . "</a>";
			$modele .= 				"<a href='#' class='ps_remove_media_button button' style='display:none;'>" . __('Remove media', 'pic_sell_plugin') . "</a>";
			$modele .= 			"</modele_td>";
			$modele .= 		"</modele_tr>";
			$modele .= 	"</div>";
			$output .= $modele;

			$output .= '<input type="hidden" name="espaceprive_date_left" id="espaceprive_date_left_rec" value="'.(empty($date_left)?date('Y-m-d', strtotime('+1 month +1 days')):$date_left).'" />';

			$output .= '<label for="espaceprive_email_client">';
			$output .=		__('Adress email', 'pic_sell_plugin');
			$output .= '</label>';

			$output .= "<div id='wrap-emails-clients'>";
			if (isset($email_client) && !empty($email_client)) {
				for ($i = 0; $i < count($email_client); $i++) {
					$output .= '<input type="text" name="espaceprive_email_client[]" value="' . esc_html($email_client[$i]) . '" />';
				}
			}
			$output .= "</div>";

			$output .= "<input class='button button-primary ps_add_email' type='button' value='" . __('Add adress mail', 'pic_sell_plugin') . "' onclick='add_field_email();' />";
			
			$output .= "<div id='wrap-produit-clients'>";

			$output .= 		'<label for="espaceprive_produit_client">';
			$output .=			__('Choice pack offers', 'pic_sell_plugin');
			$output .= 		'</label>';					
				
			$args = array(
					'post_type'        => 'offre',
					'post_status'     => 'publish'
			);

			$products = get_posts( $args );
			$output .= 		"<select name='espaceprive_produit_client' id='espaceprive_produit_client'>";
			$output .= 			"<option value='select'>" . __('Select pack offers', 'pic_sell_plugin') . "</option>";

			if(isset($products) && !empty($products)){
				foreach($products as $product){
					$id = $product->ID;
					$name = $product->post_title;
					$val_cat = $produit;
					$output .= 	"<option value='".$id."' " .(($id == $val_cat) ? 'selected':''). ">".$name."</option>";
				}
			}

			$output .= 		"</select>";

			$output .= "</div>";

			$output .= 		'<input type="hidden" name="espaceprive_panier_client" value="' . pic_esc_json($panier_client) . '" />';

			$output .= "</div>";	
			
			echo $output;	//echo wp_kses_post($output);	//echo wp_kses($output, _prefix_allowed_tags_all());	//NOT WORK  //SOLUTION?

		};
		new Pic_Sell_Custom_Fields("espaceprive", "section_space", __('Private space', 'pic_sell_plugin'), $callback);


		/**
		 * OFFRES
		 */
		$classement_base = 1; //classement de base si inexistant ou incorrect
		$offer_price_field = function ($i, $value) use($classement_base) {

			$html = "";

			$bmedia = wp_upload_dir()["basedir"] . esc_html($value['media'][$i]);

			$type = pathinfo($bmedia, PATHINFO_EXTENSION);

			$finfo = new finfo(FILEINFO_MIME); // Retourne le type mime
			/* Récupère le mime-type d'un fichier spécifique */
			$media_info = $finfo->file($bmedia);
			$genre_media = explode("/", $media_info)[0];

			if ($genre_media == "image") {

				$data = file_get_contents($bmedia);
				$base64 = 'data:' . $genre_media . '/' . $type . ';base64,' . base64_encode($data);
			
			} 

			$category = $this->get_cat_by_type_post("offre", "offre_category");
			$cats_modele = "";
			if(isset($category) && !empty($category)){
				foreach($category as $cat){
					$term_id = $cat->term_id;
					$term_name = $cat->name;
					$val_cat = esc_html($value['cat'][$i]);
					$cats_modele .= "<option value='".$term_id."' " .(($term_id == $val_cat) ? 'selected':''). ">".$term_name."</option>";
				}
			}
			$classement = intval( $value['classement'][$i] );
			if ( ! $classement ) {
				$classement = $classement_base;
				$classement_base++;
			}
			$quantity = intval( $value['quantity'][$i]);
			if ( ! $quantity ) {
				$quantity = 10;
			}
			$price = floatval( $value['price'][$i] );
			if ( ! $price ) {
				$price = 10;
			}

			$html .= 	"<tr>";
			$html .= 		"<td class='ps_classement'>";
			$html .= 			"<span class='ps_classement_span'></span>";
			$html .= 			"<input type='hidden' class='ps_classement_input' name='offer[classement][]' value='" . $classement . "' />";
			$html .= 		"</td>";
			$html .= 		"<td class='ps_media_title'>";
			$html .= 			"<input type='text' class='ps_media_title' name='offer[title][]' value='" . esc_html($value['title'][$i]) . "' />";
			$html .= 		"</td>";
			$html .= 		"<td class='ps_quantity'>";
			$html .= 			"<input type='number' class='ps_quantity' name='offer[quantity][]' step='1' value='" . $quantity . "' />";
			$html .= 		"</td>";
			$html .= 		"<td class='ps_price'>";
			$html .= 			"<input type='number' class='ps_price' name='offer[price][]' step='.01' value='" . $price . "' />";
			$html .= 		"</td>";
			$html .= 		"<td class='ps_choice'>";
			$html .= 			"<select class='ps_choice_image_select' name='offer[choice_media][]'>";
			$html .= 				"<option value='select'>" . __('Select media option', 'pic_sell_plugin') . "</option>";
			$html .= 				"<option value='image' " .(("image" == esc_html($value['choice_media'][$i])) ? 'selected':''). ">" . __('Image', 'pic_sell_plugin') . "</option>";
			$html .= 				"<option value='video' " .(("video" == esc_html($value['choice_media'][$i])) ? 'selected':''). ">" . __('Video', 'pic_sell_plugin') . "</option>";
			$html .= 			"</select>";
			$html .= 		"</td>";
			$html .= 		"<td class='ps_media'>";
			if ($genre_media == "image") {
				$html .= 		"<img src='" . $base64 . "' class='ps_display_image' style='max-width:100%;display:block;' />";
			}
			$html .= 			"<input type='file' class='ps_upload_image_button button' style='".($genre_media == "image" ? 'display:none;': 'display:block;' ). "' value='" . __('Add image', 'pic_sell_plugin') . "' />";
			$html .= 			"<div class='ps-upload-progress'><p class='uploading'>" . __('Uploading progress', 'pic_sell_plugin') . " <span></span></p><p class='finished'>" . __('Upload finished', 'pic_sell_plugin') . " <span></span></p></div>";
			$html .= 			"<input type='hidden' class='ps_media_dir' name='offer[media][]' value='" . esc_html($value['media'][$i]) . "' />";
			$html .= 		"</td>";
			$html .= 		"<td class='ps_media_desc'>";
			$html .= 			"<textarea class='ps_media_desc' name='offer[desc][]'>" . esc_html($value['desc'][$i]) . "</textarea>";
			$html .= 		"</td>";
			$html .= 		"<td class='ps_choice_cat'>";
			if(!empty($cats_modele)){
				$html .= 				"<select class='ps_choice_cat_select' name='offer[cat][]'>";
				$html .= 					"<option value='select'>" . __('Select cat', 'pic_sell_plugin') . "</option>";
				$html .= 						$cats_modele;
				$html .= 				"</select>";
			}else{
				$html .=              __('Create catégory before', 'pic_sell_plugin');
			}
			$html .= 		"</td>";
			$html .= 		"<td class='param'>";
			$html .= 			"<a href='#' class='ps_remove_line_button button' style='display:inline-block;'>" . __('Remove line', 'pic_sell_plugin') . "</a>";
			$html .= 			"<a href='#' class='ps_remove_media_button button' style='display:inline-block;'>" . __('Remove media', 'pic_sell_plugin') . "</a>";
			$html .= 		"</td>";
			$html .= 	"</tr>";

			return $html;
		};
		$callback_offer = function($post) use($offer_price_field){
			wp_nonce_field('offer_save_meta_box_data', 'offer_meta_box_nonce');

			$offer_price = get_post_meta($post->ID, '_offer_data', true);

			$output = "<div id='dynamic_form'>";

			$output .= '<label for="offers">';
			$output .=		__('Offers', 'pic_sell_plugin');
			$output .= '</label>';

			$output .= "<div>";

			$output .= "<table id='field_wrap'>";

			$output .= "	<col width=3% />
							<col width=12% />
							<col width=10% />
							<col width=10% />
							<col width=10% />
							<col width=15% />
							<col width=20% />
							<col width=10% />
							<col width=10% />";

			$output .= "<tr class='tr_head'>
								<th scope='col'>" . __('Classement', 'pic_sell_plugin') . "</th>
								<th scope='col'>" . __('Title', 'pic_sell_plugin') . "</th>								
								<th scope='col'>" . __('Quantity', 'pic_sell_plugin') . "</th>
								<th scope='col'>" . __('Price', 'pic_sell_plugin') . "</th>
								<th scope='col'>" . __('Type offre', 'pic_sell_plugin') . "</th>
								<th scope='col'>" . __('Media', 'pic_sell_plugin') . "</th>
								<th scope='col'>" . __('Description', 'pic_sell_plugin') . "</th>
								<th scope='col'>" . __('Catégorie', 'pic_sell_plugin') . "</th>
								<th scope='col'>" . __('Actions', 'pic_sell_plugin') . "</th>
						</tr>";

			if (isset($offer_price['classement'])) {
				for ($i = 0; $i < count($offer_price['classement']); $i++) {
					$output .= ($offer_price_field($i, $offer_price));
				}
			}

			$output .= "</table>";

			$output .= "<input class='button button-primary ps_add_tr' type='button' value='" . __('Add offer', 'pic_sell_plugin') . "' onclick='add_field_row_offer();' />";
			$output .= "</div>";

			$category = $this->get_cat_by_type_post("offre", "offre_category");
			$cats_modele = "";
			if(isset($category) && !empty($category)){
				foreach($category as $cat){
					$term_id = $cat->term_id;
					$term_name = $cat->name;
					$cats_modele .= "<option value='".$term_id."'>".$term_name."</option>";
				}
			}
			
			/**
			 * MODELE LIGNE TABLEAU
			 */
			$modele = 	"<div id='master-row' style='display:none;'>";
			$modele .= 		"<modele_tr>";
			$modele .= 			"<modele_td class='ps_classement'>";
			$modele .= 				"<span class='ps_classement_span'></span>";
			$modele .= 				"<input type='hidden' class='ps_classement_input' name='offer[classement][]' value='' />";
			$modele .= 			"</modele_td>";
			$modele .= 			"<modele_td class='ps_media_title'>";
			$modele .= 				"<input type='text' class='ps_media_title' name='offer[title][]' value='' />";
			$modele .= 			"</modele_td>";			
			$modele .= 			"<modele_td class='ps_quantity'>";
			$modele .= 				"<input type='number' class='ps_quantity' name='offer[quantity][]' step='1' value='' />";
			$modele .= 			"</modele_td>";
			$modele .= 			"<modele_td class='ps_price'>";
			$modele .= 				"<input type='number' class='ps_price' name='offer[price][]' step='.01' value='' />";
			$modele .= 			"</modele_td>";
			$modele .= 			"<modele_td class='ps_choice'>";
			$modele .= 				"<select class='ps_choice_image_select' name='offer[choice_media][]'>";
			$modele .= 					"<option value='select'>" . __('Select media option', 'pic_sell_plugin') . "</option>";
			$modele .= 					"<option value='image'>" . __('Image', 'pic_sell_plugin') . "</option>";
			$modele .= 					"<option value='video'>" . __('Video', 'pic_sell_plugin') . "</option>";
			$modele .= 				"</select>";
			$modele .= 			"</modele_td>";
			$modele .= 			"<modele_td class='ps_media'>";
			$modele .= 				"<input type='file' class='ps_upload_image_button button' style='display:block;' value='" . __('Add image', 'pic_sell_plugin') . "' />";
			$modele .= 				"<img src='' class='ps_display_image' style='max-width:100%;display:none;' />";
			$modele .= 				"<div class='ps-upload-progress'><p class='uploading'>" . __('Uploading progress', 'pic_sell_plugin') . " <span></span></p><p class='finished'>" . __('Upload finished', 'pic_sell_plugin') . " <span></span></p></div>";
			$modele .= 				"<input type='hidden' class='ps_media_dir' name='offer[media][]' value='' />";
			$modele .= 			"</modele_td>";
			$modele .= 			"<modele_td class='ps_media_desc'>";
			$modele .= 				"<textarea class='ps_media_desc' name='offer[desc][]'></textarea>";
			$modele .= 			"</modele_td>";
			$modele .= 			"<modele_td class='ps_choice_cat'>";
			if(!empty($cats_modele)){
				$modele .= 				"<select class='ps_choice_cat_select' name='offer[cat][]'>";
				$modele .= 					"<option value='select'>" . __('Select cat', 'pic_sell_plugin') . "</option>";
				$modele .= 						$cats_modele;
				$modele .= 				"</select>";
			}else{
				$modele .=              __('Create catégory before', 'pic_sell_plugin');
			}
			$modele .= 			"</modele_td>";		
			$modele .= 			"<modele_td class='param'>";
			$modele .= 				"<a href='#' class='ps_remove_line_button button' style='display:inline-block;'>" . __('Remove line', 'pic_sell_plugin') . "</a>";
			$modele .= 				"<a href='#' class='ps_remove_media_button button' style='display:none;'>" . __('Remove media', 'pic_sell_plugin') . "</a>";
			$modele .= 			"</modele_td>";
			$modele .= 		"</modele_tr>";
			$modele .= 	"</div>";
			$output .= $modele;

			$output .= "</div>"; //dynamic form

			echo $output;  //echo wp_kses($output, _prefix_allowed_tags_all());
			

		};
		new Pic_Sell_Custom_Fields("offre", "section_offre", __('The offer', 'pic_sell_plugin'), $callback_offer);
		
		$callback_offer_default = function($post){
			$pack_offer_default = get_post_meta($post->ID, '_pack_offer_default', true);
			$output = "";
			$output .= "<div id='wrap-pack-offer-default'>";
			$output .= 	'Default: <input type="checkbox" name="pack_offer_default" '.($pack_offer_default?"checked":"").' />';
			$output .= "</div>";

			echo wp_kses($output, _prefix_allowed_tags_all());
		};
		new Pic_Sell_Custom_Fields("offre", "section_offre_default", __('Default pack', 'pic_sell_plugin'), $callback_offer_default);

	}

	public function save_post_meta_box_offer($post_id)
	{
		if (!isset($_POST['offer_meta_box_nonce'])) {
			return;
		}
		if (!wp_verify_nonce($_POST['offer_meta_box_nonce'], 'offer_save_meta_box_data')) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		// Check the user's permissions.
		if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {

			if (!current_user_can('edit_page', $post_id)) {
				return;
			}
		} else {

			if (!current_user_can('edit_post', $post_id)) {
				return;
			}
		}

		if(isset($_POST['pack_offer_default'])){
			//supprimer le default de tout les posts
			$args = array( 'post_type' => 'offre');
			$loop = new WP_Query( $args );
			while ( $loop->have_posts() ) : $loop->the_post();
				delete_post_meta(get_the_ID(), '_pack_offer_default');
			endwhile;

			update_post_meta($post_id, '_pack_offer_default', true);
		}else {
			delete_post_meta($post_id, '_pack_offer_default');
		}

		if ($_POST['offer']) {
			// construction du tableau pour la sauvegarde des données
			$offer_data = array();

			for ($i = 0; $i < count($_POST['offer']['classement']); $i++) {
				if ('' != $_POST['offer']['classement'][$i]) {

					$classement = intval( $_POST['offer']['classement'][$i] );
					$quantity = intval( $_POST['offer']['quantity'][$i] );
					if ( ! $quantity ) {
						$quantity = 1;
					}

					$price = floatval( $_POST['offer']['price'][$i] );
					if ( ! $price ) {
						$price = 10;
					}

					$offer_data['classement'][]  = $classement;
					$offer_data['media'][]  = sanitize_text_field($_POST['offer']['media'][$i]);
					$offer_data['choice_media'][]  = sanitize_text_field($_POST['offer']['choice_media'][$i]); //image ou video
					$offer_data['title'][]  = sanitize_text_field($_POST['offer']['title'][$i]);
					$offer_data['desc'][] = sanitize_text_field($_POST['offer']['desc'][$i]);
					$offer_data['quantity'][] = $quantity;
					$offer_data['price'][] = $price;
					$offer_data['cat'][] = sanitize_text_field($_POST['offer']['cat'][$i]);
				}
			}

			if ($offer_data)
				update_post_meta($post_id, '_offer_data', $offer_data);
			else
				delete_post_meta($post_id, '_offer_data');
		}
		// si rien, supprimer les options
		else {
			delete_post_meta($post_id, '_offer_data');
		}

	}

	public function save_post_meta_box($post_id)
	{

		if (!isset($_POST['espaceprive_meta_box_nonce'])) {
			return;
		}

		if (!wp_verify_nonce($_POST['espaceprive_meta_box_nonce'], 'espaceprive_save_meta_box_data')) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		// Check the user's permissions.
		if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {

			if (!current_user_can('edit_page', $post_id)) {
				return;
			}
		} else {

			if (!current_user_can('edit_post', $post_id)) {
				return;
			}
		}

		if ($_POST['espaceprive_email_client']) {
			// construction du tableau pour la sauvegarde des données
			$email_data = array();
			for ($i = 0; $i < count($_POST['espaceprive_email_client']); $i++) {
				if ('' != $_POST['espaceprive_email_client'][$i]) {
					$email_data[]  = sanitize_text_field($_POST['espaceprive_email_client'][$i]);
				}
			}

			if ($email_data)
				update_post_meta($post_id, '_email_client', $email_data);
			else
				delete_post_meta($post_id, '_email_client');
		}
		// si rien, supprimer les options
		else {
			delete_post_meta($post_id, '_email_client');
		}	

		if ($_POST['gallery']) {
			// construction du tableau pour la sauvegarde des données
			$gallery_data = array();
			for ($i = 0; $i < count($_POST['gallery']['media_dir']); $i++) {
				if ('' != $_POST['gallery']['media_dir'][$i]) {

					$classement = intval( $_POST['gallery']['classement'][$i] );
					$gallery_data['classement'][] = $classement;
					$gallery_data['media_dir'][]  = sanitize_text_field($_POST['gallery']['media_dir'][$i]);
					$gallery_data['media_title'][]  = sanitize_text_field($_POST['gallery']['media_title'][$i]);
					$gallery_data['media_desc'][] = sanitize_text_field($_POST['gallery']['media_desc'][$i]);
					$gallery_data['choice'][] = sanitize_text_field($_POST['gallery']['choice'][$i]);
				}
			}

			if ($gallery_data)
				update_post_meta($post_id, '_gallery_data', $gallery_data);
			else
				delete_post_meta($post_id, '_gallery_data');
		}
		// si rien, supprimer les options
		else {
			delete_post_meta($post_id, '_gallery_data');
		}

		if ($_POST['espaceprive_produit_client']) {
			// construction du tableau pour la sauvegarde des données
			$produit_client  = sanitize_text_field($_POST['espaceprive_produit_client']);

			if ($produit_client && $produit_client != "select")
				update_post_meta($post_id, 'produit_client', $produit_client);
			else
				delete_post_meta($post_id, 'produit_client');
		}
		// si rien, supprimer les options
		else {
			delete_post_meta($post_id, 'produit_client');
		}



		if ($_POST['espaceprive_date_left']) {
			// construction du tableau pour la sauvegarde des données
			$date_left  = sanitize_text_field($_POST['espaceprive_date_left']);

			if ($date_left)
				update_post_meta($post_id, '_date_left', $date_left);
			else
				delete_post_meta($post_id, '_date_left');
		}
		// si rien, supprimer les options
		else {
			delete_post_meta($post_id, '_date_left');
		}

		update_post_meta($post_id, '_email_dateleft_sent', false); //on met le rappel par mail à false
		

	}

	
	/**
	 * AJAX
	 */
	public function pic_template_sent_gallery(){
		//global $post;

		$action = sanitize_text_field($_POST['act']);

		$post_id = intval($_POST['post_id']);
		if(!$post_id){
			exit();
		}

		if($action == "step_1"){
			$date_left = get_post_meta($post_id, '_date_left', true);
			if(empty($date_left)){ $date_left = date('Y-m-d', strtotime('+1 month +1 days'));}
			$dates_select = [];
			//days
			for($i=1;$i<=32;$i++){
				$dates_select[$i] = date('Y-m-d', strtotime('+'.$i.' days'));
			}
			$i2 = 2;
			//month
			for($i=33;$i<=34;$i++){
				$dates_select[$i] = date('Y-m-d', strtotime('+'.$i2.' month'));
				$i2++;
			}

			//sended
			$sended = get_post_meta($post_id, '_gallery_send', true);
			$date_sent = get_post_meta($post_id, '_gallery_send_date', true);

			if(empty($sended)){ $sended = false;}

			$html = "<div class='dynamic_form send-gallery'>";

			$html .=	"<div id='wrap-send-gallery'>";

			$html .= 		'<label for="espaceprive_date_left">';
			$html .=			__('Availability date', 'pic_sell_plugin');
			$html .=		'</label>';
			
				
			$html .= 		" <select name='espaceprive_date_left' id='espaceprive_date_left'>";
					if(isset($dates_select) && !empty($dates_select)){
						foreach($dates_select as $key => $date){
							$html .= "<option value='".$date."' " .(($date_left == $date) ? 'selected':''). ">".$date."</option>";
						}
					}
			$html .= 		"</select>";
			$html .= "<p>".__('End date: ', 'pic_sell_plugin').$date_left."</p>";

			$html .= 		'<label for="espaceprive_date_sended">';
			$html .=			__('Gallery Sent', 'pic_sell_plugin');
			$html .= 		'</label>';
			$opt = "";
					if($sended){
						$text = __('Re-send', 'pic_sell_plugin');
						$class = "resend";
						$opt = "<p>".__('Last mail sent: ', 'pic_sell_plugin').$date_sent."</p>";
					}else{
						$text = __('Send', 'pic_sell_plugin');
						$class = "send";
					}
			$html .= 		"<input class='button button-primary ps_send_gallery ".$class."' type='button' value='" . $text . "' />".$opt;

			$html .= 	"</div>";
				
			$html .= "</div>";
			echo $html;
			exit();			
		}
		else if($action = "step_2"){

			$emails = sanitize_text_field($_POST["emails"]);
			//$emails .= ", test-3qtezve9f@srv1.mail-tester.com";
			$sent = sanitize_text_field($_POST["sent"]);
			$date_left = sanitize_text_field($_POST["date_left"]);
			$post_password =  sanitize_text_field($_POST['post_password']);
			$post = get_post($post_id);

			$urlparts = parse_url(home_url());
			$domain = $urlparts['host'];
			$site_name = get_bloginfo("name");

			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'From:'.$site_name.' <contact@'.$domain.'>' . "\r\n" .
				'Reply-To: contact@'.$domain."\r\n" .
				'Content-Type: text/html; charset=UTF-8'."\r\n" .
				'Content-Disposition: inline'. "\r\n" .
				'Content-Transfer-Encoding: 7bit'." \r\n" .
				'X-Mailer:PHP/'.phpversion();

			require (PIC_SELL_TEMPLATE_DIR . "templateOrders.php");
			$template = new PIC_Template_Mail();

			$html = $template->templateGalleryReady();
			$message = $html;

			$DateNow = new DateTime();
			$date_left_en = new DateTime($date_left);
			$TempsRestant = $DateNow->diff($date_left_en);

			$m = ($TempsRestant->m > 0) ? $TempsRestant->m . " mois" : "";
			$d = ($TempsRestant->d > 0) ? $TempsRestant->d . " jours" : "";
			$a = (!empty($m) && !empty($d)) ? " et ":"";
 
			$dateleft = $m.$a.$d;

			$message = str_replace('{{site_name}}', $site_name, $message);
			$message = str_replace('{{dateleft}}', $dateleft, $message);
			$message = str_replace('{{title}}', $post->post_title, $message);
			$message = str_replace('{{permalink}}', get_permalink($post_id), $message);
			$message = str_replace('{{password}}', $post_password, $message);
			$message = str_replace('{{img}}', get_the_post_thumbnail_url($post_id), $message);

			foreach(explode(",",$emails) as $email){
				wp_mail($email, '['.$site_name.'] Votre galerie est disponible! ', $message, $headers );
			}

			$config = get_option('config_pic'); 
			$galery_ready_send_mail_admin = (isset($config["mail"]["galeryready"])&&$config["mail"]["galeryready"])?true:false;
			$admin_address_mail = isset($config["config"]["adresse"])?$config["config"]["adresse"]:"";
			if($galery_ready_send_mail_admin && !empty($admin_address_mail)){
				wp_mail($admin_address_mail, '[ADMIN/'.$site_name.'] Une galerie est disponible! ', $message, $headers );
			}

			update_post_meta($post_id, '_gallery_send', true);

			update_post_meta($post_id, '_gallery_send_date', date("d M Y"));

			echo "<p>".__("Email Send Succefully","pic_sell_plugin")."</p>";
			exit();		

		}


	}

	public function fiu_upload_file_video()
	{

		$post_id = intval($_POST['post_id']);
		$filename = sanitize_text_field($_POST['filename']);

		if(!$post_id){
			exit();
		}

		/* Location */
		$basedir = wp_upload_dir();
		$location = $basedir["basedir"] . '/pic_sell/';
		if (!is_dir($location) && !mkdir($location)) {
			die("Error creating folder $location");
		}
		$location = $location . $post_id . '/';
		if (!is_dir($location) && !mkdir($location)) {
			die("Error creating folder $location");
		}

		$location_dir = '/pic_sell/' . $post_id . '/';

		$file_data     = $this->decode_chunk($_POST['video']);

		if (false === $file_data) {
			$response[] = "err1"; //no valid base64 POST
			echo json_encode($response);
			exit();
		}

		$imageFileType = pathinfo($filename, PATHINFO_EXTENSION);

		/* Valid extensions */
		$valid_extensions = array("mp4");

		$response[] = "err2"; //error extension
		/* Check file extension */
		if (in_array(strtolower($imageFileType), $valid_extensions)) {

			file_put_contents($location . $filename, $file_data, FILE_APPEND);
			$response =  [
				'bdir' => $location_dir . $filename
			];
		}

		echo json_encode($response);
		exit();
	}

	public function decode_chunk($data)
	{
		$data = explode(';base64,', $data);

		if (!is_array($data) || !isset($data[1])) {
			return false;
		}

		$data = base64_decode($data[1]);
		if (!$data) {
			return false;
		}

		return $data;
	}

	public function fiu_upload_file()
	{

		if (isset($_FILES['file']['name'])) {

			/* Getting file name */
			$filename = ($_FILES['file']['name']);
			$post_id = intval($_POST['post_id']);

			if(!$post_id){
				exit();
			}

			/* Location */
			$basedir = wp_upload_dir();
			$location = $basedir["basedir"] . '/pic_sell/';
			if (!is_dir($location) && !mkdir($location)) {
				die("Error creating folder $location");
			}
			$location = $location . $post_id . '/';
			if (!is_dir($location) && !mkdir($location)) {
				die("Error creating folder $location");
			}

			$location_dir = '/pic_sell/' . $post_id . '/';

			$imageFileType = pathinfo($filename, PATHINFO_EXTENSION);

			/* Valid extensions */
			$valid_extensions = array("jpg", "jpeg", "png");

			$response = 0;

			/* Check file extension */
			if (in_array(strtolower($imageFileType), $valid_extensions)) {

				/* Upload file */
				if (move_uploaded_file($_FILES['file']['tmp_name'], $location . $filename)) {
					$response =  [
						'bdir' => $location_dir . $filename
					];
				}
			}
			echo json_encode($response);
			exit();
		}
		exit();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		global $post;


		wp_enqueue_style( 'pic-google-fonts', 'https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,600;1,100;1,200;1,300&display=swap', false );
	


		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/pic-sell-admin.css', array(), $this->version, 'all');

		global $wp_scripts; 

		wp_enqueue_style("wp-jquery-ui-dialog");

		/**CUSTOM TYPE espaceprive only */
		if (isset($post) && 'espaceprive' == $post->post_type) {
			wp_enqueue_style($this->plugin_name . "-espaceprive", plugin_dir_url(__FILE__) . 'css/pic-sell-espaceprive.css', array(), $this->version, 'all');
		}

		/**CUSTOM TYPE offre only */
		if (isset($post) && 'offre' == $post->post_type) {
			wp_enqueue_style($this->plugin_name . "-offre", plugin_dir_url(__FILE__) . 'css/pic-sell-offre.css', array(), $this->version, 'all');
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		global $post;

		wp_enqueue_media();

		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-dialog');

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/pic-sell-admin.js', array('jquery','jquery-ui-dialog'), $this->version, false);


		/**CUSTOM TYPE espaceprive only */
		if (isset($post) && 'espaceprive' == $post->post_type) {
			$vars = array(
				'post' => $post,
				'url_include' => PIC_SELL_URL_INC
			);
			wp_enqueue_script('psvars', plugin_dir_url(__FILE__) . 'js/pic-sell-espaceprive.js', array('jquery', 'wp-i18n'), false, true);
			wp_localize_script('psvars', 'PicSellVars', $vars);
			wp_set_script_translations( 'psvars', 'pic_sell_plugin', PIC_SELL_PATH . '/languages' );

	

		}

		/**CUSTOM TYPE offre only */
		if (isset($post) && 'offre' == $post->post_type) {
			$vars = array(
				'post' => $post,
				'url_include' => PIC_SELL_URL_INC
			);
			wp_enqueue_script('psvars', plugin_dir_url(__FILE__) . 'js/pic-sell-offre.js', array('jquery'), $this->version, false);
			wp_localize_script('psvars', 'PicSellVars', $vars);
		}



	}
}
