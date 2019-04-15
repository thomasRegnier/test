
<?php

require_once 'tools/common.php';


$query = $db->prepare('SELECT lastname, firstname, email, bio FROM user WHERE id = ?');
$query->execute(array($_SESSION['user']['id']));
//$article contiendra les informations de l'article dont l'id a été envoyé en paramètre d'URL
$user = $query->fetch();



$messages = [];

if(isset($_POST['update'])) {

    if (empty($_POST['firstname'])) {
        $messages['title'] = 'le prénom est obligatoire';
    }
    if (empty($_POST['lastname'])) {
        $messages['date'] = 'le nom est obligatoire';
    }


//début de la chaîne de caractères de la requête de mise à jour
    $queryString = 'UPDATE user SET firstname = :firstname, lastname = :lastname, email = :email, bio = :bio ';
    //début du tableau de paramètres de la requête de mise à jour
    $queryParameters = [
        'firstname' => $_POST['firstname'],
        'lastname' => $_POST['lastname'],
        'email' => $_POST['email'],
        'bio' => $_POST['bio'],
        'id' => $_SESSION['user']['id']
    ];

    //uniquement si l'admin souhaite modifier le mot de passe
    if( !empty($_POST['password'])) {
        if ($_POST['password'] == $_POST['password_confirm']){

            $queryString .= ', password = :password ';
            //ajout du paramètre password à mettre à jour
            $queryParameters['password'] = hash('md5', $_POST['password']);
        }

        else{
            $messages['password'] = 'Les mots de passe sont différents';
        }

    }

    //fin de la chaîne de caractères de la requête de mise à jour
    $queryString .= 'WHERE id = :id';

    //préparation et execution de la requête avec la chaîne de caractères et le tableau de données

if(empty($messages)) {

    $query = $db->prepare($queryString);
    $result = $query->execute($queryParameters);

    if($result){

        $_SESSION['user']['firstname'] = $_POST['firstname'];
        $_SESSION['message'] = "Update effectué avec succés";
        header('location:index.php');
        exit;
      //  $messages['success'] = "Update effectué avec succés";

    }
    else{
        $message['error'] = 'Une erreur est survenue.';
    }

}

}

?>
<!DOCTYPE html>
<html>
<head>

    <title>Login - Mon premier blog !</title>

    <?php require 'partials/head_assets.php'; ?>

</head>
<body class="article-body">
<div class="container-fluid">

    <?php require 'partials/header.php'; ?>

    <div class="row my-3 article-content">

        <?php require 'partials/nav.php'; ?>

        <main class="col-9">

            <form action="update-profil.php" method="post" class="p-4 row flex-column">

                <h4 class="pb-4 col-sm-8 offset-sm-2">Mise à jour des informations utilisateur</h4>


           <?php foreach ($messages as $message){
                echo $message;
                }
                ;?>


                <div class="form-group col-sm-8 offset-sm-2">
                    <label for="firstname">Prénom <b class="text-danger">*</b></label>
                    <input class="form-control" value="<?php echo $user['firstname'];?>" type="text" placeholder="Prénom" name="firstname" id="firstname" />
                </div>
                <div class="form-group col-sm-8 offset-sm-2">
                    <label for="lastname">Nom de famille</label>
                    <input class="form-control" value="<?php echo $user['lastname'];?>" type="text" placeholder="Nom de famille" name="lastname" id="lastname" />
                </div>
                <div class="form-group col-sm-8 offset-sm-2">
                    <label for="email">Email <b class="text-danger">*</b></label>
                    <input class="form-control" value="<?php echo $user['email'];?>" type="email" placeholder="Email" name="email" id="email" />
                </div>
                <div class="form-group col-sm-8 offset-sm-2">
                    <label for="password">Mot de passe (uniquement si vous souhaitez modifier votre mot de passe actuel)</label>
                    <input class="form-control" value="" type="password" placeholder="Mot de passe" name="password" id="password" />
                </div>
                <div class="form-group col-sm-8 offset-sm-2">
                    <label for="password_confirm">Confirmation du mot de passe (uniquement si vous souhaitez modifier votre mot de passe actuel)</label>
                    <input class="form-control" value="" type="password" placeholder="Confirmation du mot de passe" name="password_confirm" id="password_confirm" />
                </div>
                <div class="form-group col-sm-8 offset-sm-2">
                    <label for="bio">Biographie</label>
                    <textarea class="form-control" name="bio" id="bio" placeholder="Ta vie Ton oeuvre..."><?php echo $user['bio'];?></textarea>
                </div>

                <div class="text-right col-sm-8 offset-sm-2">
                    <p class="text-danger">* champs requis</p>
                    <input class="btn btn-success" type="submit" name="update" value="Valider" />
                </div>

            </form>
        </main>
    </div>

    <footer class="row mt-3">
        <div class="col py-2 text-right">
            <b>Footer du site</b>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.3.1/jquery.fancybox.min.js"></script>

    <script src="js/main.js"></script>

</div>
</body>
</html>




<?php
print_r($_SESSION);

//print_r($user);

//echo $user['firstname'];

?>
