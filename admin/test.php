<?php require_once '../tools/common.php';?>

<?php

echo '<pre>';
var_dump($_FILES);
echo '</pre>';


if (isset($_FILES['image'])){
    if ($_FILES['image']['error'] === 0 ) {

        //  $allowed_extensions = array('jpg', 'jpeg', 'gif', 'png');

        $allowed_extensions = array('jpg', 'jpeg','png','gif');

        // $maxSize = 20000000;
        $maxSize = 200000;

        $my_file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        $dimension = getimagesize($_FILES['image']['tmp_name']);

        // print_r($dimension);

        //  echo $dimension[0];

        // echo $my_file_extension;

        $message = [];

        if (in_array($my_file_extension, $allowed_extensions)) {


            if ($_FILES['image']['size'] <= $maxSize AND $dimension[0] <= 600 AND $dimension[1] <= 1200) {

            } else {
                $message['error'] = "largeur ou hauteur trop importante";

            }

        } else {
            $message['error'] = "Fichiers non autorisé";
        }


        if (empty($message)) {
            do {
                $new_file_name = time() . rand();
                $destination = './file/' . $new_file_name . '.' . $my_file_extension;
            } while (file_exists($destination));

            $result = move_uploaded_file($_FILES['image']['tmp_name'], $destination);
            echo "Fichiers enrégistré avec succés";


        }
    }

    else{
        echo "met une image enculé";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Administration - Mon premier blog !</title>
    <?php require 'partials/head_assets.php'; ?>
</head>
<body class="index-body">
<div class="container-fluid">
    <?php require 'partials/header.php'; ?>
    <div class="row my-3 index-content">
        <?php require 'partials/nav.php'; ?>
        <main class="col-9">


            <?php if (!empty($message)) :?>
            <?php foreach ($message as $msg) :?>
            <?php echo $msg;?>
            <?php endforeach;?>
            <?php endif ;?>

            <form action="test.php" method="post" enctype="multipart/form-data">

                <label for="image">Image :</label>
                 <input class="form-control" type="file" name="image" id="image"/>
                <input class="btn btn-success" type="submit" name="save" value="Enregistrer" />

            </form>
        </main>
    </div>
</div>
</body>
</html>

