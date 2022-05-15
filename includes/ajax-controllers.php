
<?php 

//Gestion des paramètres dans l'url (ex : ?clé=valeur&autreClé=valeur...)
/*if (strpos($_SERVER["REQUEST_URI"], '?') !== false) {

    if( strpos($_SERVER["REQUEST_URI"], '&') !== false ){

        $arr = explode('&', parse_url($_SERVER["REQUEST_URI"])['query']);
        $queries = [];
        foreach ($arr as $query) {
            $line=[];
            $key = explode('=', $query)[0];
            $value = explode('=', $query)[1];
            $queries[$key] = $value;
              
        }

        $request = explode('?', $_SERVER["REQUEST_URI"])[0];

    }else{
        $queries = [];
        $key = explode('=', parse_url($_SERVER["REQUEST_URI"])['query'])[0];
        $value = explode('=', parse_url($_SERVER["REQUEST_URI"])['query'])[1];
        $queries[$key] = $value;
        $request = explode('?', $_SERVER["REQUEST_URI"])[0];

    }

    
    
}else{
    $request = $_SERVER["REQUEST_URI"];
}*/
$request = $_POST["action"];

//exit();

require(PIC_SELL_PATH_INC . "app/panier.php");

switch ($request) {

    case "/post/update/":

        $cart = new Panier();
        $cart->updateCart();
        //$a["panier"] = "update"; 
        //echo json_encode($a);
        break;


    case "/post/checkout/":
        if(session_id() == '') {
            session_start();
        }

      //  $cart = new Panier();
        $cart->paypalCheckOut();
       
        break;


     case "/post/testMail/":

        if(session_id() == '') {
            session_start();
        }

       // $cart = new Panier();
        $cart->emailOrder();
       
        break;

        case "/post/checkPrivateGallery/":

       // $automation = new MarketingAutomation();
        $automation->checkPrivateGallery();
        
       
        break;

        case "/post/checkOrders/":
        
       // $cart = new Panier();
        $cart->getOrders($queries['commande'],$queries['getBy']);
        
       
        break;

    default:
       header('Location: /404/');
}
