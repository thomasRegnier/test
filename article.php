<?php 
require_once 'tools/common.php'; 

//si j'ai reçu article_id ET que c'est un nombre
if(isset($_GET['article_id']) AND ctype_digit($_GET['article_id'])){

	//$query = $db->prepare('SELECT a.* , c.name as category_name
	//	FROM article a JOIN category c
	//	ON a.category_id = c.id
	//	WHERE published_at <= NOW() AND is_published = 1 AND a.id = ?');

	$query = $db->prepare('SELECT title, GROUP_CONCAT(name) as name, published_at, summary, content, article.id,img
 FROM article INNER JOIN articles_categories
 ON article.id = articles_categories.article_id
 
 INNER JOIN category
 ON articles_categories.category_id = category.id
 WHERE article.id = ? AND published_at <= NOW() AND is_published = 1
     GROUP BY article.id
	ORDER BY published_at DESC');

	$query->execute( array( $_GET['article_id'] ) );
	$article = $query->fetch();


//	echo '<pre>';
//	var_dump($article);
 //   echo '</pre>';

	//si aucun article n'a été trouvé je redirige
	if($article == false){
		header('location:index.php');
		exit;
	}
}
else{ //sinon je redirige
	header('location:index.php');
	exit;
}
?>

<!DOCTYPE html>
<html>
 <head>
	<title><?= $article['title']; ?> - Mon premier blog !</title>
	<?php require 'partials/head_assets.php'; ?>
 </head>
 <body class="article-body">
	<div class="container-fluid">
		<?php require 'partials/header.php'; ?>
		<div class="row my-3 article-content">
			<?php require 'partials/nav.php'; ?>
			<main class="col-9">
				<article>
                    <?php if (!empty($article['img'])) : ?>
                        <img class="pb-4 img-fluid" src="img/article/<?php echo $article['img']?>"></br>
                    <?php endif;?>
					<h1><?= $article['title']; ?></h1>
					<strong style="color: red">[<?= $article['name']; ?>]</strong>
					<span class="article-date">
						<!-- affichage de la date de l'article selon le format %A %e %B %Y -->
						<?= strftime("%A %e %B %Y", strtotime($article['published_at'])); ?>
					</span>
					<div class="article-content">
						<?= $article['content']; ?>
					</div>
				</article>
			</main>
		</div>
		<?php require 'partials/footer.php'; ?>
	</div>
 </body>
</html>
