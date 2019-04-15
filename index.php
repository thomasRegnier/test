<?php

require_once 'tools/common.php';

//si un utilisateur est connécté et que l'on reçoit le paramètre "lougout" via URL, on le déconnecte
if(isset($_GET['logout']) && isset($_SESSION['user'])){
	//la fonction unset() détruit une variable ou une partie de tableau. ici on détruit la session user
	unset($_SESSION["user"]);
}

//selection des 3 derniers articles PUBLIés ET dont la publish_date est inférieure ou égale à la date du jour
/*$query = $db->query('SELECT a.*, c.name as category_name
	FROM article a JOIN category c
	ON a.category_id = c.id
	WHERE a.published_at <= NOW() AND a.is_published = 1
	ORDER BY a.published_at DESC
	LIMIT 3');
$homeArticles=$query->fetchAll();
*/


$query = $db->query('SELECT title, GROUP_CONCAT(name) as name, published_at, summary, article.id,img
 FROM article INNER JOIN articles_categories
 ON article.id = articles_categories.article_id
 
 INNER JOIN category
 ON articles_categories.category_id = category.id
 WHERE published_at <= NOW() AND is_published = 1
     GROUP BY article.id DESC
	ORDER BY published_at DESC LIMIT 3');
$homeArticles=$query->fetchAll();

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Homepage - Mon premier blog !</title>
		<?php require 'partials/head_assets.php'; ?>
	</head>
	<body class="index-body">
		<div class="container-fluid">

			<?php require 'partials/header.php'; ?>

			<div class="row my-3 index-content">

				<?php require 'partials/nav.php'; ?>

				<main class="col-9">
					<section class="latest_articles">
						<header class="mb-4"><h1>Les 3 derniers articles :</h1></header>

                        <div style="background-color: #1abd54; color: white">
                            <?php if (isset($_SESSION['message'])) : ?>
                                <?php echo  $_SESSION['message'] ; ?>
                                <?php unset($_SESSION['message']) ;?>
                            <?php endif; ?>
                        </div>


                        <!-- les trois derniers articles -->

						<?php foreach($homeArticles as $key => $article): ?>
						<article class="mb-4">
							<h2><?php echo $article['title']; ?></h2>
							<strong style="color: red">[<?= $article['name']; ?>]</strong>
							<span class="article-date">
								<!-- affichage de la date de l'article selon le format %A %e %B %Y -->
								<?php echo strftime("%A %e %B %Y", strtotime($article['published_at'])); ?>
							</span>
							<div class="article-content">
								<?php echo $article['summary']; ?>
							</div>
                            <div class="col-12 col-md-4 col-lg-3">

                            <?php if (!empty($article['img'])) : ?>
                                <img class="img-fluid" src="img/article/<?php echo $article['img']?>">
                            <?php endif;?>

                            </div>
							<a href="article.php?article_id=<?php echo $article['id']; ?>">> Lire l'article</a>
						</article>
						<?php endforeach; ?>

					</section>
					<div class="text-right">
						<a href="article_list.php">> Tous les articles</a>
					</div>
				</main>
			</div>

			<?php require 'partials/footer.php'; ?>

		</div>
	</body>
</html>
