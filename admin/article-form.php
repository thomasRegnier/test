<?php

require_once '../tools/common.php';

if(!isset($_SESSION['user']) OR $_SESSION['user']['is_admin'] == 0){
	header('location:../index.php');
	exit;
}


$messages = [];


//Si $_POST['save'] existe, cela signifie que c'est un ajout d'article
if(isset($_POST['save'])){


    if (empty($_POST['title'])) {
        $messages['title'] = 'le titre est obligatoire';
    }
    if (empty($_POST['published_at'])) {
        $messages['date'] = 'la date est obligatoire';
    }

    if (empty($_POST['category_id'])) {
        $messages['categories'] = 'la catégorie est obligatoire';
    }

        if (isset($_FILES['image'])){

            if ($_FILES['image']['error'] === 0 ) {

                $allowed_extensions = array('jpg', 'jpeg','png','gif');

                $my_file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);


                if (in_array($my_file_extension, $allowed_extensions)) {

                    do {
                        $new_file_name = time() . rand();

                        $nameImg = $new_file_name . '.' . $my_file_extension;

                        $destination = '../img/article/' . $new_file_name . '.' . $my_file_extension;

                    } while (file_exists($destination));

                }
                else {
                    $messages['error'] = "Fichiers non autorisé";
                }

            }


        }



    if(empty($messages)) {

    $query = $db->prepare('INSERT INTO article (title, content, summary, is_published, published_at,img) VALUES (?, ?, ?, ?, ?,?)');
    $newArticle = $query->execute([
        $_POST['title'],
        $_POST['content'],
        $_POST['summary'],
        $_POST['is_published'],
        $_POST['published_at'],
        $nameImg
    ]);

        move_uploaded_file($_FILES['image']['tmp_name'], $destination);


    $query = $db->prepare('INSERT INTO articles_categories(article_id, category_id) VALUES (?,?)');
    $last_insert = $db->lastInsertId();
    foreach ($_POST['category_id'] as $category) {
        $query->execute([
            $last_insert,
            $category
        ]);
    }

    //redirection après enregistrement
    //si $newArticle alors l'enregistrement a fonctionné
    if ($newArticle) {
        //redirection après enregistrement
        $_SESSION['message'] = 'Article ajouté !';
        header('location:article-list.php');
        exit;
    } else { //si pas $newArticle => enregistrement échoué => générer un message pour l'administrateur à afficher plus bas
        $message = "Impossible d'enregistrer le nouvel article...";
    }

    }
}




//si on modifie un article, on doit séléctionner l'article en question (id envoyé dans URL) pour pré-remplir le formulaire plus bas
if(isset($_GET['article_id']) && isset($_GET['action']) && $_GET['action'] == 'edit'){

    $query = $db->prepare('SELECT * FROM article WHERE id = ?');
    $query->execute(array($_GET['article_id']));
    //$article contiendra les informations de l'article dont l'id a été envoyé en paramètre d'URL
    $article = $query->fetch();

}






//Si $_POST['update'] existe, cela signifie que c'est une mise à jour d'article
if(isset($_POST['update'])){


    if (isset($_FILES['image'])){

        if ($_FILES['image']['error'] === 0 ) {

            $allowed_extensions = array('jpg', 'jpeg','png','gif');

            $my_file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);


            if (in_array($my_file_extension, $allowed_extensions)) {

                do {
                    $new_file_name = time() . rand();

                    $nameImg = $new_file_name . '.' . $my_file_extension;

                    $destination = '../img/article/' . $new_file_name . '.' . $my_file_extension;

                } while (file_exists($destination));

            }
            else {
                $messages['error'] = "Fichiers non autorisé";
            }

        }


    }

	$query = $db->prepare('UPDATE article SET
		title = :title,
		content = :content,
		summary = :summary,
		is_published = :is_published,
		published_at = :published_at,
		img = :img
		WHERE id = :id'
	);

	//mise à jour avec les données du formulaire
	$resultArticle = $query->execute([
		'title' => $_POST['title'],
		'content' => $_POST['content'],
		'summary' => $_POST['summary'],
		'is_published' => $_POST['is_published'],
		'published_at' => $_POST['published_at'],
		'id' => $_POST['id'],
        'img' => $nameImg,
	]);

    $pathForImg = '../img/article/';


    unlink($pathForImg. $article['img']);

    move_uploaded_file($_FILES['image']['tmp_name'], $destination);



    $categoryDelete = $db->prepare('DELETE FROM articles_categories WHERE article_id = ?');
	$deleted = $categoryDelete->execute(array($_POST['id']));

	foreach ($_POST['category_id'] as $category){
	    $updateCategory =$db->prepare('INSERT INTO articles_categories(article_id, category_id) VALUES (?,?)');
	    $resultInsert = $updateCategory->execute(
	            [
	                    $_POST['id'],
	                    $category

                ]
        );
    }

	//si enregistrement ok
	if($resultArticle){
		$_SESSION['message'] = 'Article mis à jour !';
    header('location:article-list.php');
    exit;
  }
	else{
		$message = 'Erreur.';
	}
}


        $queryCategories = $db ->query('SELECT * FROM category');
        $categories = $queryCategories->fetchAll();

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Administration des articles - Mon premier blog !</title>
		<?php require 'partials/head_assets.php'; ?>
	</head>
	<body class="index-body">
		<div class="container-fluid">
			<?php require 'partials/header.php'; ?>
			<div class="row my-3 index-content">
				<?php require 'partials/nav.php'; ?>
				<section class="col-9">
					<header class="pb-3">
						<!-- Si $article existe, on affiche "Modifier" SINON on affiche "Ajouter" -->
						<h4><?php if(isset($article)): ?>Modifier<?php else: ?>Ajouter<?php endif; ?> un article</h4>
					</header>
					<?php if(isset($message)): //si un message a été généré plus haut, l'afficher ?>
					<div class="bg-danger text-white">
						<?= $message; ?>
					</div>
					<?php endif; ?>

                    <?php if (!empty($messages)) :?>
                        <?php foreach ($messages as $msg) :?>
                            <?php echo $msg;?></br>
                        <?php endforeach;?>
                    <?php endif ;?>

					<!-- Si $article existe, chaque champ du formulaire sera pré-remplit avec les informations de l'article -->
					<form action="article-form.php" method="post" enctype="multipart/form-data">

						<div class="form-group">
							<label for="title">Titre :</label>
							<input class="form-control" value="<?= isset($article) ? htmlentities($article['title']) : '';?>" type="text" placeholder="Titre" name="title" id="title" />
						</div>
						<div class="form-group">
							<label for="summary">Résumé :</label>
							<input class="form-control" value="<?= isset($article) ? htmlentities($article['summary']) : '';?>" type="text" placeholder="Résumé" name="summary" id="summary" />
						</div>
						<div class="form-group">
							<label for="content">Contenu :</label>
							<textarea class="form-control" name="content" id="content" placeholder="Contenu"><?= isset($article) ? htmlentities($article['content']) : '';?></textarea>
						</div>

                        <div class="form-group">
                            <label for="image">Image :</label>
                            <input class="form-control" type="file" name="image" id="image"/>
                        </div>

						<div class="form-group">
							<label for="category_id">Catégorie :</label>
							<select multiple class="form-control" name="category_id[]" id="category_id">
                                        <?php foreach($categories as $key => $category) : ?>

                                        <?php

                                          $query = $db->prepare('SELECT * from articles_categories WHERE article_id = ? AND category_id = ?');
                                          $query->execute(array($_GET['article_id'], $category['id']));
                                          $articleCat = $query->fetch();?>

                                <option value="<?= $category['id']; ?>" <?= isset($_GET['article_id']) && $articleCat  ? 'selected' : '';?>>
										<?= $category['name']; ?>
									        </option>


                                        <?php endforeach; ?>




							</select>
						</div>

						<div class="form-group">
							<label for="published_at">Date de publication :</label>
							<input class="form-control" value="<?= isset($article) ? htmlentities($article['published_at']) : '';?>" type="date" placeholder="Résumé" name="published_at" id="published_at" />
						</div>

						<div class="form-group">
							<label for="is_published">Publié ?</label>
							<select class="form-control" name="is_published" id="is_published">
								<option value="0" <?= isset($article) && $article['is_published'] == 0 ? 'selected' : '';?>>Non</option>
								<option value="1" <?= isset($article) && $article['is_published'] == 1 ? 'selected' : '';?>>Oui</option>
							</select>
						</div>

						<div class="text-right">
						<!-- Si $article existe, on affiche un lien de mise à jour -->
						<?php if(isset($article)): ?>
							<input class="btn btn-success" type="submit" name="update" value="Mettre à jour" />
						<!-- Sinon on afficher un lien d'enregistrement d'un nouvel article -->
						<?php else: ?>
							<input class="btn btn-success" type="submit" name="save" value="Enregistrer" />
						<?php endif; ?>
						</div>

						<!-- Si $article existe, on ajoute un champ caché contenant l'id de l'article à modifier pour la requête UPDATE -->
						<?php if(isset($article)): ?>
						<input type="hidden" name="id" value="<?= $article['id']; ?>" />
						<?php endif; ?>

					</form>

				</section>
			</div>
		</div>


  </body>
</html>
