<!-- CHARGE le fichier header.php -->
<?php get_header(); ?>

<?php

global $wpdb;

echo "<a href='https://chronopro.net/preprod-upload/mon-compte/orders' style='color: blue; font-weight: bold'> < Retour</a>";

//Customer ID
$customer_id = get_current_user_id();
// var_dump($customer_id);

//ITEMS_ID
$order_id = intval($_GET['order']); // Récupère l'id de la commande en integer
$order = wc_get_order($order_id); // Recupère la commande
$items = $order->get_items();
$items_id = array_keys($items)[0]; // Récupère order_item_id


//Sécurisation
if ($customer_id == $order->data['customer_id']) {
  $commandes = $wpdb->get_results("SELECT `order_item_id` FROM `chro_woocommerce_order_items` WHERE order_id = \"$order_id\""); //Requête SQL pour récupérer la commande

  foreach ($commandes as $key => $value) {
    // print_r($key);
    // print_r($value->order_item_id);

    $order_item_id = $value->order_item_id;
    $commande = $wpdb->get_results("SELECT * FROM `chro_woocommerce_order_itemmeta`  WHERE order_item_id = \"$order_item_id\"");
    // echo "<pre>";
    // var_dump($commande);
    // echo "</pre>";

    $discipline_value = $commande[15]->meta_value; // Nom de la course
    $distance_value = $commande[16]->meta_value; //Distance de la course
    $quantity_participant = intval($commande[2]->meta_value); // Nombre de participants
    echo "<pre>";
    echo "<h2>Discipine: $discipline_value</h2>";
    echo "</pre>";
    echo "<pre>";
    echo "<h3>Distance: $distance_value</h3>";
    echo "</pre>";


    $first_numero = 19; //Premier tableau Participant débute toujours a 19
    $first_meta = $commande[$first_numero];
    $last_numero = count($commande); //nombre tous les tableaux de la commande
    $last_meta = $commande[$last_numero]; //numéro de du dernier tableau de la commande

    $nombre_participant = intval($commande[2]->meta_value); // Récupère nombre de participant
    $nombre_infos_participant = $last_numero - $first_numero; //Nombre de tableau qui contiennent les infos des participants en tout
    $nombre_array_par_participant = intdiv($nombre_infos_participant, $nombre_participant); //Divise le nombre de tous les tableaux par le nombre de participant pour trouver le nombre de tableau par participant

    $compteur = 0; //Initialisation du compteur
    $depart = 19; //Premier tableau contenant les infos des participants
    $btn_upload = 1;
    //Si un seul participant
    if ($nombre_participant == 1) {
      $positions = range(19, (19 + $nombre_array_par_participant) - 1); //Position des tableaux contenant les infos du participant si 1 participant
    } else {
      $positions = range(19, (19 + $nombre_array_par_participant) - 2); //Position des tableaux contenant les infos du participant si plusieurs participants
    }
    while ($compteur < $nombre_participant) {
      echo "<pre>";
      echo "Participant " . strval($compteur + 1) . ":"; //Affiche le numéro du participant
      echo "</pre>";
      foreach ($positions as $position) {
        $meta_key = $commande[$position]->meta_key; //Récupère la meta_key
        $meta_value = $commande[$position]->meta_value; //Récupère la meta_value

        //Affiche les infos de chaque participant
        if ($meta_key == "NOM" || $meta_key == "Prénom" || $meta_key == "Mail" || $meta_key == "Date de naissance" || $meta_key == "Certificat médical ou licence FFA, FCD, FSPN, ASPTT, UOLEP" || $meta_key == "Certificat médical ou licence FFC ou UFOLEP" || $meta_key == "Certificat médical ou licence FFA, FCD, FSPN, ASPTT, UFOLEP" || $meta_key == "Certificat médical ou licence FFTriathlon" || $meta_key == "Certificat médical ou licence FFA") {
          echo "<pre>";
          echo "$meta_key: ";
          echo $meta_value;
          echo "</pre>";
        }

        //Affiche le certificat si la meta_key contient un certificat ou une licence
        if ($meta_key == "Certificat médical ou licence FFC ou UFOLEP" || $meta_key == "Certificat médical ou licence FFA, FCD, FSPN, ASPTT, UFOLEP" || $meta_key == "Certificat médical ou licence FFTriathlon" || $meta_key == "Certificat médical ou licence FFA") {

          $certificat = explode('"', $meta_value)[1]; //URL du certificat actuel
          $certificat_path = $certificat."?t=".rand();//empêche la navigateur à utiliser son cache

          echo "<img src='$certificat_path' width='70px' height='70px'>";//Affichage du certificat

          // Afiichage Formulaire d'Upload
          echo "<form method='post' name='upload_certificat_$btn_upload' enctype='multipart/form-data'>
          <p>Choisissez votre certificat ou licence:</p>
          <input type='file' name='certificat' accept='image/png, image/jpeg'>
          <input type='submit' name='upload_certificat_$btn_upload' value='Upload un nouveau certificat' />
          </form>";

          $btn_upload_path = "upload_certificat_$btn_upload";
          if (isset($_POST[$btn_upload_path])) {

            $ancien_path_certificat = strval(explode("/", $certificat)[7]); //Unique ID GravityForms
            $year = strval(explode('/', $certificat)[8]); //Année dans uploads/gravity/forms/Année
            $month = strval(explode('/', $certificat)[9]); //Mois dans uploads/gravity/forms/Année/Mois
            $ancien_nom_certificat = explode('/', $certificat)[10]; //Récupere le nom + extension de l'ancien certificat
            $nouveau_path_certificat = "$ancien_path_certificat/$year/$month/$ancien_nom_certificat"; //Chemin d'accès du nouveau certificat
            $trt = $_SERVER['DOCUMENT_ROOT'] . "/preprod-upload/wp-content/uploads/gravity_forms/$nouveau_path_certificat";
            $tmp_name = $_FILES['certificat']['tmp_name'];
            $upload = move_uploaded_file($tmp_name, $trt);
            // echo "<pre>";
            // var_dump($trt);
            // echo "</pre>";
            // echo "<pre>";
            // var_dump($tmp_name);
            // echo "</pre>";

            if ($upload) {
              echo "Upload OK";
            } else {
              echo "Upload Not Ok";
              echo $_FILES['certificat']['error'];
            }
          }
        }
      }
      $btn_upload += 1;

      $depart += $nombre_array_par_participant; //Premier tableau contenant les infos du premier participant
      $positions = range($depart, ($depart + $nombre_array_par_participant) - 1); //Rajoute le nombre de tableau par participant au nombre de depart
      $compteur++; //incrémente le compteur
      echo "<pre>";
      echo "---------------------";
      echo "</pre>";
    }
  }

} else {
  echo "Impossible d'accéder à cette commande";
}

?>

<?php
// CHARGE le fichier footer.php
get_footer(); ?>
