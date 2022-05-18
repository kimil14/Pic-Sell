<?php 

class PIC_Panier{

	private function protectUserCart(){
		$the_post = get_post($_POST['cartId']);

		if ( $the_post->post_password != $_POST['password'] ){
			exit;
		}
		
	}

	private function object_to_array($data){

	    if (is_array($data) || is_object($data))
	    {
	        $result = array();
	        foreach ($data as $key => $value)
	        {
	            $result[$key] = $this->object_to_array($value);
	        }
	        return $result;
	    }
	    return $data;
	}



	public function updateCart(){
	
		$this->protectUserCart();

        if(!empty($_POST['cart'])){
          update_post_meta( $_POST['cartId'], 'panier_client', $_POST['cart']);  
        }

        $a["result"] = get_post_meta($_POST['cartId'], 'panier_client');
        echo json_encode($a);	
		
	}

	public function paypalCheckOut($cart, $query){

	//	global $post;

		//$cart = get_post_meta($_POST['cartId'], 'panier_client', true);

			//Prepare GET data
		 //   $query = array();
		    $user = array();
		    $order = array();

			$base = get_bloginfo('wpurl');
			$query['notify_url'] = $base . '/espace-prive/?validate_ipn=ipn';
			//$query['notify_url'] = "https://f368-2a01-e0a-98-b760-ec40-5cda-2211-20b5.eu.ngrok.io/wpdev/espace-prive/";
		    $query['return'] = $base . '/espace-prive/?validate_commande=thankyou';

		    $query['cmd'] = '_cart';
		    $query['upload'] = '1';
			$paypal = get_option("paypal_pic");
			$paypal_address_mail = isset($paypal["paypal"]["adresse"])?$paypal["paypal"]["adresse"]:"";
		    $query['business'] = $paypal_address_mail;
		    $query['address_override'] = 0;
		    $query['currency_code'] = 'EUR';
		    $query['shipping_1'] = 0;
		    $query['address_country_code'] = "FR";

		    $user = array();
		    $user['first_name'] = $query['first_name'];
		    $user['last_name'] = $query['last_name'];
		    $user['email'] = $query['email'];
		    $user['telephone'] = $query['telephone'];
		    $user['address1'] = $query['address1'];
		    $user['address2'] = $query['address2'];
		    $user['city'] = $query['city'];
		    $user['country'] = $query['country'];
		    $user['state'] = $query['state'];
		    $user['zip'] = $query['zip'];
		    $user['cartId'] = $query['cartId'];

			$query['custom'] = json_encode($user);

		   // $_SESSION['user'] = $user;

    	//Prepare query string
    	$query_string = http_build_query($query);
		return $query_string;
  		 // header('Location: https://www.paypal.com/cgi-bin/webscr?' . $query_string);
		
	}

	public function get_amount($cart, $fdp=0){

		$total = 0;
		$products_title = array();

        foreach ($cart as $value) {

            //print_r($value);
            /*Si la limite est égale à 1, c'est que ce n'est pas un coffret*/
            if ($value['produit']['limite'] == 1) {

                if (!in_array($value['produit']['titre'], $products_title)) {
                    $products_title[] = $value['produit']['titre'];
                }

            } else {
                $total = $total + ($value['produit']['prix'] * $value['qtt']);
            }
        }


        foreach ($products_title as $key => $title) {


            foreach ($cart as $value) {

                if ($value['produit']['titre'] == $title) {

                    foreach ($value['array_media'] as $key => $photo) {

                        $total = $total + ($value['produit']['prix'] * $value['qtt']);

                    }
                }
            }
        }

        $totalTTC = $total + $fdp;

        return $totalTTC;
	}

	public function emailOrder($txn, $custom){

		//$output = "";
		//parse_str($custom, $output);
		$session = urldecode($custom);
		$session = stripslashes($session);
		$session = json_decode($session, true);

		$order[$txn] = array(
			'order_id' => uniqid(),
			'order_date' => date('d/m/Y'),
			'user' => $session,
			'cart' => $this->object_to_array(json_decode(get_post_meta($session['cartId'], 'panier_client', true)))
		);
		//mail("benjamin@cestre.fr", "IPN: check 4 ok", "USER SESSION custom: ".$session, "From: noreply@test.fr");
		/*On vérifie que le panier n'est pas vide*/
		if (isset($order[$txn]['cart'])){
			
			$orders='';
			
			$fdp=0;
			if ( $session['state'] != 'France') $fdp=15;
			$total=0;
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
			$template = new Template_Mail();

			//ob_start();
			$html = $template->templateOrder($order[$txn], $fdp, $txn);
			//require '../templates_email/oneWeekLeftEmailTemplate.php';
			//$html = ob_get_clean();

			$message = $html;
			//$message = "email order...";
			//require '../templates_email/templateOrders.php';

			mail($order[$txn]['user']['email'], 'Commande '.$site_name, $message, $headers );


			$config = get_option('config_pic'); 
			$admin_address_mail = isset($config["config"]["adresse"])?$config["config"]["adresse"]:"";
			if(!empty($admin_address_mail)){
				mail($admin_address_mail, '[ADMIN/'.$site_name.'] Nouvelle commande! ', $message, $headers );
			}

			$defaultOrders = array("orders"=>[]);
			$allOrders = get_option('allcommands_pic', serialize(json_encode($defaultOrders)));
			$allOrders = json_decode(unserialize($allOrders), true);

			if($allOrders == $defaultOrders || $allOrders == false ) {
				$allOrders["orders"][] = $order;
				update_option('allcommands_pic', serialize(json_encode($allOrders)));
				update_post_meta($session['cartId'], 'panier_client', '');
			}else{				
				$allOrders["orders"][] = $order;
				update_option('allcommands_pic', serialize(json_encode($allOrders)));
				update_post_meta($session['cartId'], 'panier_client', '');					
					
			}

			//header('Location: '.get_page_link($session['cartId']).'?merci');

		}else{
			//header('Location: '.get_home_url());
		}		
	}

	public function getOrders($orderID,$getBy=false){
	
		$message="";
		if(!$orderID && !$getBy) exit('Merci de renseigner un numéro de commande...');
		if($getBy){
			$getBy = explode(':', $getBy);
		}

		$defaultOrders = array("orders"=>[]);
		//$defaultOrders = serialize(json_encode("[{'orders':[]}]"));
		$allOrders = get_option('allcommands_pic', serialize(json_encode($defaultOrders)));
		$allOrders = json_decode(unserialize($allOrders), true);

		foreach ($allOrders as $key) {
			foreach ($key as $value) {
				//print_r($value);
				if($orderID){
					if(array_key_exists($orderID, $value)){
						$order['cart'] = $value[$orderID]['cart'];
						$order['user'] = $value[$orderID]['user'];
						$order['order_id'] = $value[$orderID]['order_id'];
						$order['order_date'] = $value[$orderID]['order_date'];
						$fdp=0;
						if ( $order['user']['country'] != 'FR') $fdp=15;
						require (PIC_SELL_TEMPLATE_DIR . "templateOrders.php");
						$template = new Template_Mail();

						$message = $template->templateOrder($order, $fdp);
						// exit;
						//require '../templates_email/templateOrders.php';	
					}
				}
			}

			

			 			 
		/*	 if($getBy){
			 	if ( $value['user'][$getBy[0]] == $getBy[1]){
					$order['cart'] = $value['cart'];
					$order['user'] = $value['user'];
					$order['order_id'] = $value['order_id'];
					$order['order_date'] = $value['order_date'];
					$fdp=0;
					if ( $order['user']['country'] != 'FR') $fdp=15;
					// print_r($value);
					// exit;
					require '../templates_email/templateOrders.php';	
				}
			 }*/
			
		}

		if(!$message) $message = "Votre numéro de commande est faux...";
		echo $message;
	}

}



?>
