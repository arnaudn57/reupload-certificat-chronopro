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



$form_count = 0;
//Sécurisation
if ($customer_id == $order->data['customer_id']) {
  $commandes = $wpdb->get_results("SELECT `order_item_id` FROM `chro_woocommerce_order_items` WHERE order_id = \"$order_id\""); //Requête SQL pour récupérer la commande
  $order_item_id = $commandes[0]->order_item_id;
  // echo "<pre>";
  // var_dump(intval($order_item_id));
  // echo "</pre>";



  $verif_nombre_modif = $wpdb->get_results("SELECT `meta_value` FROM `chro_woocommerce_order_itemmeta` WHERE order_item_id = $order_item_id AND meta_key = '_tax_class'");
  $verif_nombre_modif = $verif_nombre_modif[0]->meta_value;



  // if ($verif_nombre_modif == "1") {
  //   echo "Impossible d'accèder à la modification";
  // } else {

  foreach ($commandes as $key => $value) {
    $opl = 1;
    $order_item_id = $value->order_item_id; //Récupère l'order_item_id dans la table chro_woocommerce_order_itemmeta
    $commande = $wpdb->get_results("SELECT * FROM `chro_woocommerce_order_itemmeta`  WHERE order_item_id = \"$order_item_id\""); //Récupère tous les items de chaque achat

?>
    <hr size="8" width="90%" color="blue">

    <?php
    // echo "<pre>";
    // var_dump($order_item_id);
    // echo "</pre>";

    ?>

    <?php
    $discipline_value = $commande[15]->meta_value; // Nom de la course
    $distance_value = $commande[16]->meta_value; //Distance de la course
    $quantity_participant = intval($commande[2]->meta_value); // Nombre de participants

    ?>
    <div class="infos-course text-center">
      <h3>Discipline : <?php echo $discipline_value ?></h3>
      <h3>Distance: <?php echo $distance_value ?></h3>
    </div>

    <div class="container">
      <?php


      $first_numero = 19; //Premier tableau Participant débute toujours a 19
      $first_meta = $commande[$first_numero];
      $last_numero = count($commande) - 1; //nombre tous les tableaux de la commande
      $last_meta = $commande[$last_numero]; //numéro de du dernier tableau de la commande

      $nombre_participant = intval($commande[2]->meta_value); // Récupère nombre de participant
      $nombre_infos_participant = $last_numero - $first_numero; //Nombre de tableau qui contiennent les infos des participants en tout
      $nombre_array_par_participant = intdiv($nombre_infos_participant, $nombre_participant); //Divise le nombre de tous les tableaux par le nombre de participant pour trouver le nombre de tableau par participant

      $compteur = 0; //Initialisation du compteur
      $depart = 19; //Premier tableau contenant les infos des participants
      $test_query_count = 0; // Compteur pour le nombre de certificat

      //Si un seul participant

      if ($nombre_participant == 1) {
        $positions = range(19, (19 + $nombre_array_par_participant) - 1); //Position des tableaux contenant les infos du participant si 1 participant
      } else {
        $positions = range(19, (19 + $nombre_array_par_participant) - 2); //Position des tableaux contenant les infos du participant si plusieurs participants
      }

      while ($compteur < $nombre_participant) {

      ?>
        <h4 class="text-decoration-underline"><?php echo "Participant " . strval($compteur + 1) . ":" ?></h4>

        <?php

        foreach ($positions as $position) {
          $meta_key = $commande[$position]->meta_key; //Récupère la meta_key
          $meta_value = $commande[$position]->meta_value; //Récupère la meta_value

          //Affiche les infos de chaque participant
          if ($meta_key == "NOM" || $meta_key == "Prénom" || $meta_key == "Mail" || $meta_key == "Date de naissance" || $meta_key == "Certificat médical ou licence FFA, FCD, FSPN, ASPTT, UOLEP" || $meta_key == "Certificat médical ou licence FFC ou UFOLEP" || $meta_key == "Certificat médical ou licence FFA, FCD, FSPN, ASPTT, UFOLEP" || $meta_key == "Certificat médical ou licence FFTriathlon" || $meta_key == "Certificat médical ou licence FFA") {
            // var_dump($meta_key);
            if ($meta_key == "Certificat médical ou licence FFA, FCD, FSPN, ASPTT, UOLEP" || $meta_key == "Certificat médical ou licence FFC ou UFOLEP" || $meta_key == "Certificat médical ou licence FFA, FCD, FSPN, ASPTT, UFOLEP" || $meta_key == "Certificat médical ou licence FFTriathlon" || $meta_key == "Certificat médical ou licence FFA") {
              //Système pour nettoyer string balise a inscrite dans la bdd
              $meta_value = str_replace('<a href=', "", $meta_value);
              $meta_value = str_replace('"', "", $meta_value);
              $meta_value = explode(">", $meta_value);
              $meta_value = $meta_value[0];
              $meta_value = trim($meta_value, "'");
              $meta_value_path = trim($meta_value, "'"); //force le navigateur à ne pas utiliser son cache pour afficher le nouveau certificat
              $ext = explode(".", $meta_value_path)[2]; //Extension de l'ancien certificat
              // var_dump($form_count);
              $form_count++;


        ?>
              <?php if ($ext == "pdf") {
                echo "<strong>Certificat ou licence :</strong>
                <iframe src='$meta_value_path' frameborder='2' height='250px' width='250px'></iframe>"; //Affiche un iframe si l'extension du certificat actuel est un pdf
              } else {
                echo "<strong>Certificat ou licence :</strong><a href='$meta_value' target='_blank'><img src='$meta_value_path' class='border' width='250px' height='250px'></a>"; //Affiche l'image si l'extension du certificat est un png, jpeg ou jpg
              } ?>


            <?php
            }
            ?>
            <?php if ($meta_key != "Certificat médical ou licence FFA, FCD, FSPN, ASPTT, UFOLEP") { //Cache le chemain d'accès du certificat
            ?>
              <p><strong><?php echo $meta_key . ": " ?></strong> <?php echo $meta_value ?></p>
            <?php } ?>

        <?php
          }


          //Affiche le certificat si la meta_key contient un certificat ou une licence
          if ($meta_key == "Certificat médical ou licence FFC ou UFOLEP" || $meta_key == "Certificat médical ou licence FFA, FCD, FSPN, ASPTT, UFOLEP" || $meta_key == "Certificat médical ou licence FFTriathlon" || $meta_key == "Certificat médical ou licence FFA") {

            // Récupère l'url de l'ancien certificat
            $test_query = $wpdb->get_results("SELECT * from chro_woocommerce_order_itemmeta WHERE order_item_id = \"$order_item_id\" AND (meta_key = 'Certificat médical ou licence FFA, FCD, FSPN, ASPTT, UFOLEP' OR meta_key= 'Certificat médical ou licence FFC ou UFOLEP' OR meta_key = 'Certificat médical ou licence FFTriathlon' OR meta_key = 'Certificat médical ou licence FFA')");
            $certificat = $meta_value_path;
            $certificat_meta_id = $test_query[$test_query_count]->meta_id; //Meta ID de l'ancien certificat
            $certificat_path = trim($certificat, "'"); //force le navigateur à ne pas utiliser son cache pour afficher le nouveau certificat
            $ext = explode(".", $certificat_path)[2]; //Extension de l'ancien certificat

            $test_query_count += 1; //Incrémente
            // echo "$form_count";

            //Récupére la valeur de la meta_key: verif_modif_certificat
            $query_meta_key_verif = "verif_modif_certificat_" . strval($opl);
            $query_verif_certificat = $wpdb->get_results("SELECT meta_value FROM `chro_woocommerce_order_itemmeta` WHERE order_item_id = \"$order_item_id\" AND meta_key = '$query_meta_key_verif' ");

            //Condition qui verifie le statut de la ligne verif_modif_certificat existe
            //Certificat déja modifié
            if (intval($query_verif_certificat[0]->meta_value) == 1) {
              echo "Le changement n'est plus autorisé";
            } else {
              //Le certificat peut être modifié
              echo "Le changement est autorisé";
              echo "<form method='post' name='upload_certificat_$form_count' enctype='multipart/form-data'>
                      <p>Choisissez votre certificat ou licence:</p>
                      <input type='file' name='certificat' accept='image/png, image/jpeg, application/pdf'>
                      <input type='submit' class='btn btn-primary' name='upload_certificat_$form_count' value='Remplacer le certificat' />
                      </form>";

              //Si la meta_key verif_nombre_modif n'existe pas, insertion pour chaque formulaire
              if (is_null($query_verif_certificat[0]->meta_value)) {
                echo "true";
                $verif_meta_key_request = "verif_modif_certificat_" . strval($opl);
                $verif_request_form = $wpdb->query($wpdb->prepare("INSERT INTO chro_woocommerce_order_itemmeta (order_item_id, meta_key, meta_value) VALUES ('$order_item_id', '$verif_meta_key_request', 0)"));
              }
            }

            $btn_upload_path = "upload_certificat_$form_count";
            if (isset($_POST[$btn_upload_path])) {
              //Upload New Certificat
              $ancien_path_certificat = strval(explode("/", $certificat)[7]); //Unique ID GravityForms
              // var_dump($opl);

              $year = strval(explode('/', $certificat)[8]); //Année dans uploads/gravity/forms/Année
              $month = strval(explode('/', $certificat)[9]); //Mois dans uploads/gravity/forms/Année/Mois
              $ancien_nom_certificat = explode('/', $certificat)[10]; //Récupere le nom + extension de l'ancien certificat
              $ancien_nom_certificat = explode('"', $ancien_nom_certificat)[0]; //Ancien nom certificat
              $extension_ancien_certificat = substr($ancien_nom_certificat, -3); //Extension ancien certificat

              $extension_verif = substr($ancien_nom_certificat, -4);

              //Permet de savoir si l'extension contient 3 ou 4 lettres (pour récuperer l'ancien nom complet de l'ancien certificat)
              if ($extension_verif == "png" || $extension_verif == "PNG" || $extension_verif == "jpg" || $extension_verif == "JPG" || $extension_verif == "pdf" || $extension_verif == "PDF") {
                $extension_ancien_certificat = substr($ancien_nom_certificat, -3);
              } elseif ($extension_verif == "JPEG" || $extension_verif == "jpeg") {
                $extension_ancien_certificat = substr($ancien_nom_certificat, -4);
              }

              if ($extension_ancien_certificat == "pdf" || $extension_ancien_certificat == "PDF" || $extension_ancien_certificat == "png" || $extension_ancien_certificat == "jpeg" || $extension_ancien_certificat == "PNG" || $extension_ancien_certificat == "JPEG" || $extension_ancien_certificat == "JPG") {

                echo "Certificat PDF";
                $old_path_pdf = $_SERVER['DOCUMENT_ROOT'] . "/preprod-upload/wp-content/uploads/gravity_forms/" . explode("/", $certificat)[7] . "/" . explode("/", $certificat)[8] . "/" . explode("/", $certificat)[9] . "/" . $ancien_nom_certificat; //Path de l'ancien certificat
                $old_name_pdf = explode("/", $certificat)[10];
                $old_name_pdf = explode(".", $old_name_pdf); //nom de l'ancien certificat
                $old_extension_pdf = $old_name_pdf[1]; //extension de l'ancien certificat

                $random_id_name_certificat = rand(); //Génèration d'un id unique
                $new_path_pdf = explode("/", $certificat)[7] . "/" . explode("/", $certificat)[8] . "/" . explode("/", $certificat)[9] . "/";
                $new_name_pdf = $_FILES['certificat']['name'];
                $new_extension_pdf = explode("/", $_FILES['certificat']['type'])[1]; //Extension du nouveau certificat
                $new_name_pdf = explode(".", $new_name_pdf)[0] . $random_id_name_certificat . "." . $new_extension_pdf; //Nouveau nom du certificat qui va être upload
                $tmp_name_pdf = $_FILES['certificat']['tmp_name'];
                $upload_new_path_pdf = $_SERVER['DOCUMENT_ROOT'] . "/preprod-upload/wp-content/uploads/gravity_forms/" . explode("/", $certificat)[7] . "/" . explode("/", $certificat)[8] . "/" . explode("/", $certificat)[9] . "/";
                $new_url_pdf = "<a href='https://chronopro.net/preprod-upload/wp-content/uploads/gravity_forms/" . strval($new_path_pdf) . strval($new_name_pdf) . "'>Visionner</a>"; //Balise a avec url du nouveau certificat

                //Verifie si le fichier existe
                if (file_exists($old_path_pdf)) {
                  echo "le fichier existe déja";
                  echo "Suppression de l'ancien fichier réussi";
                  $upload = move_uploaded_file($tmp_name_pdf, $upload_new_path_pdf . $new_name_pdf); //Fonction d'upload
                  //Supression de l'ancien fichier PDF
                  $delete_pdf = unlink($old_path_pdf);
                  if ($upload) {

                    echo "Upload réussi";

                    if ($request) {
                      echo "Query OK";

                      //Query qui modifie l'url dans la bdd
                      $tablename = 'chro_woocommerce_order_itemmeta';
                      $new_meta_value = $new_url_pdf;
                      $request_order_item_id = intval($order_item_id);
                      $new_meta_key = "Certificat médical ou licence FFTriathlon";
                      $request = $wpdb->query($wpdb->prepare("UPDATE $tablename SET meta_value = '%s' WHERE order_item_id = '%d' AND meta_id = $certificat_meta_id", array($new_meta_value, $request_order_item_id)));


                      //Query qui modifie le statut modification certificat
                      $tablename = 'chro_woocommerce_order_itemmeta';
                      $new_meta_value_verif = 1;
                      $request_order_item_id = intval($order_item_id);
                      $meta_key_verif = "verif_modif_certificat_" . strval($opl);
                      $request_verif = $wpdb->query($wpdb->prepare("UPDATE $tablename SET meta_value = '%s' WHERE order_item_id = '%d' AND meta_key = '$meta_key_verif'", array($new_meta_value_verif, $request_order_item_id)));
                      echo "Changement status de modification changé";
                      $redirect_url = "https://chronopro.net" . $_SERVER['REQUEST_URI'];
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
        $opl++; //Incrémente le nombre de formulaire par achat
        $form_count_verif++; //Incrémente le nombre de formulaire présent au total sur la page
        $depart += $nombre_array_par_participant; //Premier tableau contenant les infos du premier participant
        $positions = range($depart, ($depart + $nombre_array_par_participant) - 1); //Rajoute le nombre de tableau par participant au nombre de depart
        $compteur++; //incrémente le compteur
        ?>
        <hr size="8" width="90%" color="red">
  <?php
      }
    }
  }
  // } else {
  //   echo "Impossible d'accéder à cette commande, la modification des certificats à déja était fait";
  // }

  ?>

    </div>


    <?php
    // CHARGE le fichier footer.php
    get_footer(); ?>
