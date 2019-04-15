<?php

require_once '../tools/common.php';

if(!isset($_SESSION['user']) OR $_SESSION['user']['is_admin'] == 0){
	header('location:../index.php');
	exit;
}

//supprimer l'article dont l'ID est envoyé en paramètre URL
if(isset($_GET['article_id']) && isset($_GET['action']) && $_GET['action'] == 'delete'){


    $query = $db->prepare('SELECT img FROM article WHERE id = ?');
    $result = $query->execute([
        $_GET['article_id']
    ]);


    $imgSelect=$query->fetch();

    $pathForImg = '../img/article/';


    unlink($pathForImg. $imgSelect['img']);


    $query = $db->prepare('DELETE FROM articles_categories WHERE article_id = ?');
    $result = $query->execute([
        $_GET['article_id']
    ]);




    $query = $db->prepare('DELETE FROM article WHERE id = ?');
	$result = $query->execute([
		$_GET['article_id']
	]);


	//générer un message à afficher pour l'administrateur
	if($result){
		$message = "Suppression efféctuée.";
	}
	else{
		$message = "Impossible de supprimer la séléction.";
	}
}

//séléctionner tous les articles pour affichage de la liste
$query = $db->query('SELECT * FROM article ORDER BY id DESC');
$articles = $query->fetchall();
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
					<header class="pb-4 d-flex justify-content-between">
						<h4>Liste des articles</h4>
						<a class="btn btn-primary" href="article-form.php">Ajouter un article</a>
					</header>
					<?php if(isset($message)): //si un message a été généré plus haut, l'afficher ?>
					<div class="bg-success text-white p-2 mb-4">
						<?= $message; ?>
					</div>
					<?php endif; ?>
					<?php if(isset($_SESSION['message'])): //si un message a été généré plus haut, l'afficher ?>
					<div class="bg-success text-white p-2 mb-4">
						<?= $_SESSION['message']; ?>
						<?php unset($_SESSION['message']); ?>
					</div>
					<?php endif; ?>
					<table class="table table-striped">
						<thead>
							<tr>
								<th>#</th>
								<th>Titre</th>
								<th>Publié</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php if($articles): ?>
							<?php foreach($articles as $article): ?>
							<tr>
								<!-- htmlentities sert à écrire les balises html sans les interpréter -->
								<th><?= htmlentities($article['id']); ?></th>
								<td><?= htmlentities($article['title']); ?></td>
								<td>
									<?= $article['is_published'] == 1 ? 'Oui' : 'Non' ?>
								</td>
								<td>
									<a href="article-form.php?article_id=<?= $article['id']; ?>&action=edit" class="btn btn-warning">Modifier</a>
									<a onclick="return confirm('Are you sure?')" href="article-list.php?article_id=<?= $article['id']; ?>&action=delete" class="btn btn-danger">Supprimer</a>
								</td>
							</tr>
							<?php endforeach; ?>
							<?php else: ?>
								Aucun article enregistré.
							<?php endif; ?>
						</tbody>
					</table>
				</section>
			</div>
		</div>
	</body>
</html>
