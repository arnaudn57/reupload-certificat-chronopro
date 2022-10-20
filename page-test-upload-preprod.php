<!-- CHARGE le fichier header.php -->
<?php get_header(); ?>

<?php

global $wpdb;

echo "<a href='https://chronopro.net/preprod-upload/mon-compte/orders' style='color: blue; font-weight: bold'> < Retour</a>";

//Customer ID
$customer_id = get_current_user_id();

//ITEMS_ID
$order_id = intval($_GET['order']); // Récupère l'id de la commande en integer
$order = wc_get_order($order_id); // Recupère la commande
$items = $order->get_items();
$items_id = array_keys($items)[0]; // Récupère order_item_id


// echo "<pre>";
// var_dump($items_id);
// echo "</pre>";

//Sécurisation
if ($customer_id == $order->data['customer_id']) {
  $commandes = $wpdb->get_results("SELECT `order_item_id` FROM `chro_woocommerce_order_items` WHERE order_id = \"$order_id\""); //Requête SQL pour récupérer la commande
  $order_item_id = $commandes[0]->order_item_id;
  // echo "<pre>";
  // var_dump(intval($order_item_id));
  // echo "</pre>";

  $verif_nombre_modif = $wpdb->get_results("SELECT `meta_value` FROM `chro_woocommerce_order_itemmeta` WHERE order_item_id = $order_item_id AND meta_key = '_tax_class'");
  $verif_nombre_modif = $verif_nombre_modif[0]->meta_value;
  // echo "<pre>";
  // var_dump($verif_nombre_modif);
  // echo "</pre>";

  if($verif_nombre_modif == "1"){
    echo "it's not good";
  } else {
    echo "it's good";
    foreach ($commandes as $key => $value) {

      $order_item_id = $value->order_item_id; //Récupère l'order_item_id dans la table chro_woocommerce_order_itemmeta
      $commande = $wpdb->get_results("SELECT * FROM `chro_woocommerce_order_itemmeta`  WHERE order_item_id = \"$order_item_id\"");
      echo "<pre>";
      var_dump($order_item_id);
      echo "</pre>";

      $discipline_value = $commande[15]->meta_value; // Nom de la course
      $distance_value = $commande[16]->meta_value; //Distance de la course
      $quantity_participant = intval($commande[2]->meta_value); // Nombre de participants
      echo "<pre>";
      echo "<h2>Discipline: $discipline_value</h2>";
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
      $test_query_count = 0; // Compteur pour le nombre de certificat
      $btn_upload = 1; // Compteru pour le form
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
            if ($meta_key == "Certificat médical ou licence FFA, FCD, FSPN, ASPTT, UOLEP" || $meta_key == "Certificat médical ou licence FFC ou UFOLEP" || $meta_key == "Certificat médical ou licence FFA, FCD, FSPN, ASPTT, UFOLEP" || $meta_key == "Certificat médical ou licence FFTriathlon" || $meta_key == "Certificat médical ou licence FFA") {
              $meta_value = str_replace('<a href=', "", $meta_value);
              $meta_value = str_replace('"', "", $meta_value);
              $meta_value = explode(">", $meta_value);
              $meta_value = $meta_value[0];
              $meta_value = trim($meta_value, "'");
              $meta_value_path = trim($meta_value, "'"); //force le navigateur à ne pas utiliser son cache pour afficher le nouveau certificat
              $ext = explode(".", $meta_value_path)[2]; //Extension de l'ancien certificat

              if ($ext == "pdf") {
                echo "<iframe src='$meta_value_path' frameborder='1' height='250px' width='250px'></iframe>'"; //Affiche un iframe si l'extension du certificat actuel est un pdf
              } else {
                echo "<img src='$meta_value_path' width='70px' height='70px'>"; //Affiche l'image si l'extension du certificat est un png, jpeg ou jpg
              }
            }
            echo "$meta_key: ";
            echo $meta_value;
            echo "</pre>";
          }

          //Affiche le certificat si la meta_key contient un certificat ou une licence
          if ($meta_key == "Certificat médical ou licence FFC ou UFOLEP" || $meta_key == "Certificat médical ou licence FFA, FCD, FSPN, ASPTT, UFOLEP" || $meta_key == "Certificat médical ou licence FFTriathlon" || $meta_key == "Certificat médical ou licence FFA") {

            // Récupère l'url de l'ancien certificat
            $test_query = $wpdb->get_results("SELECT * from chro_woocommerce_order_itemmeta WHERE order_item_id = \"$order_item_id\" AND (meta_key = 'Certificat médical ou licence FFA, FCD, FSPN, ASPTT, UFOLEP' OR meta_key= 'Certificat médical ou licence FFC ou UFOLEP' OR meta_key = 'Certificat médical ou licence FFTriathlon' OR meta_key = 'Certificat médical ou licence FFA')");
            $certificat = $meta_value_path;
            echo "<pre>";
            var_dump($test_query[$test_query_count]->meta_id);
            $certificat_meta_id = $test_query[$test_query_count]->meta_id;
            echo "</pre>";


            $certificat_path = trim($certificat, "'"); //force le navigateur à ne pas utiliser son cache pour afficher le nouveau certificat
            $ext = explode(".", $certificat_path)[2]; //Extension de l'ancien certificat

            // if($ext == "pdf"){
            //   echo "<iframe src='$certificat_path' frameborder='1' height='250px' width='250px'></iframe>'";//Affiche un iframe si l'extension du certificat actuel est un pdf
            // } else {
            //   echo "<img src='$certificat_path' width='70px' height='70px'>"; //Affiche l'image si l'extension du certificat est un png, jpeg ou jpg
            // }

            echo "<pre>";
            var_dump($test_query_count);
            echo "</pre>";
            // Affichage Formulaire d'Upload
            echo "<form method='post' name='upload_certificat_$btn_upload' enctype='multipart/form-data'>
          <p>Choisissez votre certificat ou licence:</p>
          <input type='file' name='certificat' accept='image/png, image/jpeg, application/pdf'>
          <input type='submit' name='upload_certificat_$btn_upload' value='Upload un nouveau certificat' />
          </form>";
            $test_query_count += 1; //Incrémente

            $btn_upload_path = "upload_certificat_$btn_upload";
            if (isset($_POST[$btn_upload_path])) {
              //Upload New Certificat
              $ancien_path_certificat = strval(explode("/", $certificat)[7]); //Unique ID GravityForms

              $year = strval(explode('/', $certificat)[8]); //Année dans uploads/gravity/forms/Année
              $month = strval(explode('/', $certificat)[9]); //Mois dans uploads/gravity/forms/Année/Mois
              $ancien_nom_certificat = explode('/', $certificat)[10]; //Récupere le nom + extension de l'ancien certificat
              $ancien_nom_certificat = explode('"', $ancien_nom_certificat)[0];
              $extension_ancien_certificat = explode('.', $ancien_nom_certificat)[1];


              if ($extension_ancien_certificat == "pdf" || $extension_ancien_certificat == "PDF" || $extension_ancien_certificat == "png" || $extension_ancien_certificat == "jpeg" || $extension_ancien_certificat == "PNG" || $extension_ancien_certificat == "JPEG") {

                echo "Certificat PDF";
                $old_path_pdf = $_SERVER['DOCUMENT_ROOT'] . "/preprod-upload/wp-content/uploads/gravity_forms/" . explode("/", $certificat)[7] . "/" . explode("/", $certificat)[8] . "/" . explode("/", $certificat)[9] . "/" . $ancien_nom_certificat;
                $old_name_pdf = explode("/", $certificat)[10];
                $old_name_pdf = explode(".", $old_name_pdf);
                $old_extension_pdf = $old_name_pdf[1];

                $random_id_name_certificat = rand(); //Génèration d'un id unique
                $new_path_pdf = explode("/", $certificat)[7] . "/" . explode("/", $certificat)[8] . "/" . explode("/", $certificat)[9] . "/";
                $new_name_pdf = $_FILES['certificat']['name'];
                $new_name_pdf = explode(".", $new_name_pdf)[0] . $random_id_name_certificat . "." . explode(".", $new_name_pdf)[1]; //Nouveau nom du certificat qui va être upload
                $new_extension_pdf = explode("/", $_FILES['certificat']['type'])[1]; // Extension du nouveau certificat
                $tmp_name_pdf = $_FILES['certificat']['tmp_name'];
                $upload_new_path_pdf = $_SERVER['DOCUMENT_ROOT'] . "/preprod-upload/wp-content/uploads/gravity_forms/" . explode("/", $certificat)[7] . "/" . explode("/", $certificat)[8] . "/" . explode("/", $certificat)[9] . "/";
                $new_url_pdf = "<a href='https://chronopro.net/preprod-upload/wp-content/uploads/gravity_forms/" . strval($new_path_pdf) . strval($new_name_pdf) . "'>Visionner</a>"; //Balise a avec url du nouveau certificat

                if (file_exists($old_path_pdf)) {

                  echo "le fichier existe déja ";
                  //Supression de l'ancien fichier PDF
                  $delete_pdf = unlink($old_path_pdf);
                  if ($delete_pdf) {

                    echo "Suppression de l'ancien fichier réussi";
                    $upload = move_uploaded_file($tmp_name_pdf, $upload_new_path_pdf . $new_name_pdf); //Fonction d'upload

                    if ($upload) {
                      echo "Upload réussi";
                      //requete qui modifie l'url dans la bdd
                      $tablename = 'chro_woocommerce_order_itemmeta';
                      $new_meta_value = $new_url_pdf;
                      $request_order_item_id = intval($order_item_id);
                      $new_meta_key = "Certificat médical ou licence FFTriathlon";
                      $request = $wpdb->query($wpdb->prepare("UPDATE $tablename SET meta_value = '%s' WHERE order_item_id = '%d' AND meta_id = $certificat_meta_id", array($new_meta_value, $request_order_item_id)));
                      if ($request) {
                        echo "Query OK";

                        //Query Ajout pour commande modifié
                        $tablename = 'chro_woocommerce_order_itemmeta';
                        $new_meta_value = true;
                        $request_order_item_id = intval($order_item_id);
                        $new_meta_key = "Certificat médical ou licence FFTriathlon";
                        $request = $wpdb->query($wpdb->prepare("UPDATE $tablename SET meta_value = '%s' WHERE order_item_id = '%d' AND meta_key = '_tax_class'", array($new_meta_value, $request_order_item_id)));
                        echo "Changement status de modification changé";
                        echo "<pre>";
                        $redirect_url = "https://chronopro.net" . $_SERVER['REQUEST_URI'];
                        echo "</pre>";
                        header('Location: ' . $redirect_url);
                        die;
                      } else {
                        echo "<pre>";
                        echo "Query not OK";
                        echo "</pre>";
                      }
                    } else {
                      echo "<pre>";
                      echo "Upload FAIL";
                      echo "</pre>";
                    }
                  } else {
                    echo "<pre>";
                    echo "Suppression de l'ancien fichier FAIL";
                    echo "</pre>";
                  }
                } else {
                  echo "<pre>";
                  echo "Le fichier voulu n'existe pas";
                  echo "</pre>";
                }
              } else {
                echo "<pre>";
                echo "Abandon du processus, un probleme est apparu";
                echo "</pre>";
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
  }



} else {

  echo "Impossible d'accéder à cette commande";

}

?>

<?php
// CHARGE le fichier footer.php
get_footer(); ?>

