<?php
require 'connect.php';
$game_id = rand(1000, 9999);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8" />
	<title>Qwins!</title>

	<link rel="stylesheet" href="css/style.css?time=<?=time()?>" />
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

	<link rel='shortcut icon' href='img/icon-512.png' type='image/x-icon' />

	<meta name="viewport" content="width=device-width, initial-scale=1">

	<meta name="theme-color" content="#03A9F4">
	<link rel="manifest" href="manifest.json" />
<script>
function share(){
	if (navigator.share) {
	  navigator.share({
		  title: 'Zaproszenie do gry w Qwins!',
		  text: 'Zobaczmy, kto będzie lepszy w quizowym wyzwaniu!',
		  url: '<?='https://'.$_SERVER['HTTP_HOST'].str_replace('lobby.php', '', $_SERVER['REQUEST_URI']).'?game='.$game_id;?>',
	  })
		.then(() => console.log('Successful share'))
		.catch((error) => console.log('Error sharing', error));
	}
}
</script>
</head>
<body>
	<?php
	if(!isset($_POST['connect']) && !isset($_POST['host'])){
		header("Location: index.php");
	}
	?>
	<?php if(isset($_POST['host'])): ?>
	<div id="menu-bar" class="container-fluid">
		<div class="container">
			<div class="title">
				Qwins!
			</div>
			<div class="start-game box disabled" onclick="start_game()">
				Rozpocznij grę
			</div>
			<?php
			$vertical_padding = 'class="padding-vertical-2x"';
			echo '<div id="game-id">ID gry:<br /><b>'.$game_id.'</b></div>';
			?>
		</div>
	</div>
	<div class="container padding-vertical-2x">
		<div>
			<div class="col-md-9" style="float:left;">
				<h3>Kategorie quizów</h3>
				<?php
				$get_categories = $mysql->query("SELECT qwins_category.id, qwins_category.name, qwins_category.difficulty, COUNT(qwins_question.id) AS question_counter, qwins_category.language 
				FROM qwins_category INNER JOIN qwins_question 
				ON qwins_category.id = qwins_question.category_id 
				GROUP BY qwins_category.id");
				if($get_categories->num_rows > 0){
					while($show_categories = $get_categories->fetch_object()){
						if($show_categories->question_counter >= 20){
							$difficulty = '';
							if($show_categories->difficulty == 1){ $difficulty = '<span data-difficulty="1">Łatwy</span>'; }
							if($show_categories->difficulty == 2){ $difficulty = '<span data-difficulty="2">Średnio zaawansowany</span>'; }
							if($show_categories->difficulty == 3){ $difficulty = '<span data-difficulty="3">Trudny</span>'; }

							if($show_categories->language == 0){ $language = '<span data-type="lang">PL</span>'; }
							if($show_categories->language == 1){ $language = '<span data-type="lang">EN</span>'; }

							echo '<label for="category_'.$show_categories->id.'" class="box box-quiz">
								<h4>'.$show_categories->name.'</h4>
								'.$language.$difficulty.'
								<div class="question-counter">'.$show_categories->question_counter.'<i class="material-icons">help</i></div>
								<input type="checkbox" id="category_'.$show_categories->id.'" class="select_category" name="select_category_'.$show_categories->id.'" value="'.$show_categories->id.'" onchange="select_quiz('.$show_categories->id.')" /><label for="category_'.$show_categories->id.'"></label>
							</label>';
						}
					}
				}
				?>
			</div>
			<div class="col-md-3" style="float:left;">
				<h3>Lista graczy</h3>
				<ul id="player-list">
				</ul>
				<h3>Ustawienia gry</h3><br />
				<div class="form_input"><input id="setting-question-cnt" type="number" min="3" max="20" step="1" value="10" /><label>Ilość pytań</label></div>
				<input id="setting-solo-mode" class="material_checkbox" type="checkbox" /><label for="setting-solo-mode">Tryb solo</label>
				<br /><br />
				<span class="button-share" onclick="share()">Udostępnij</span>
				<br style="clear:both;" /><br />
				<br /><br />
			</div>
		</div>
	</div>
	<?php endif; 
	if(isset($_POST['connect'])):
	?>
	<div id="menu-bar" class="container-fluid">
		<div class="container">
			<div class="row">
				<div class="title">
					Qwins!
				</div>
			</div>
		</div>
	</div>
	<div class="container-fluid">
		<div class="container main-menu">
			<div class="row padding-vertical-2x" style="text-align:center;">
				Poczekaj, aż host wybierze kategorie i rozpocznie grę.
			</div>
		</div>
	<?php endif; ?>

	<div id="quiz_game_container" class="shadow-overlay hidden">
		<div <?=$vertical_padding?>>
			<div id="question_box" class="box col-md-9">
				<div class="row">
					<div id="question_counter" data-count="1"></div>
					<div id="question_timer" data-time="10">
						<svg width="160" height="160" xmlns="http://www.w3.org/2000/svg">
						  <circle id="circle" class="circle_animation" r="20" cy="80" cx="80" stroke-width="10" stroke="#6fdb6f" fill="none" />
						</svg>
					</div>
					<div id="question_text" class="col-md-12 col-sm-12"></div>
					<div id="answer1_text" class="col-md-6 col-sm-12 answer" data-answer="1"><div></div></div>
					<div id="answer2_text" class="col-md-6 col-sm-12 answer" data-answer="2"><div></div></div>
					<div id="answer3_text" class="col-md-6 col-sm-12 answer" data-answer="3"><div></div></div>
					<div id="answer4_text" class="col-md-6 col-sm-12 answer" data-answer="4"><div></div></div>
				</div>
			</div>
			<div id="score-table" class="hidden">
				<h1>Tabela wyników</h1>
				<p>Gracz<span>Wynik</span></p>
				<ul id="player-score-list">
				</ul>
			</div>
		</div>
	</div>

	<?php
	if(isset($_POST['connect'])){ echo '</div>'; }
	if(isset($_POST['host'])){
		$role = 'host';
		$nick = htmlspecialchars($_POST['nick']);
	}
	if(isset($_POST['connect'])){
		$role = 'player';
		$game_id = (int)$_POST['game_id'];
		$nick = htmlspecialchars($_POST['nick']);
	}
	?>
	<script>
		const game_id = 'game-<?=$game_id?>';
		const role = '<?=$role?>';
		const nick = '<?=$nick?>';

		let selected_quiz = [];
		let shown_questions = [];
		let device = 'desktop_windows';

		if(typeof window.orientation !== 'undefined'){ device = 'smartphone'; }
	</script>
	<script src="https://www.gstatic.com/firebasejs/3.9.0/firebase.js" integrity="sha384-ljiddGbiDjiJwWcj0TZcD73bb9+8croWbrAjdLZ3KZxWLk+6sflC9wbmjXx/uWyR" crossorigin="anonymous"></script>
	<script src="js/initialize_firebase.js"></script>
	<script src="js/datachannel.js"></script>
	<script src="js/connect.js?time=<?=time()?>"></script>
</body>
</html>