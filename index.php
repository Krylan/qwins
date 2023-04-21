<?php require 'connect.php'; ?>
<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8" />
	<title>Qwins!</title>

	<?php
	if(isset($_GET['game'])){
		echo '<meta property="og:title" content="Zaproszenie do gry w Qwins!" />';
		echo '<meta property="og:description" content="Zobaczmy, kto będzie lepszy w quizowym wyzwaniu!" />';

		$game_id = (int)$_GET['game'];
	}else{
		$game_id = null;
	}
	?>

	<link rel="stylesheet" href="css/style.css" />
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

	<link rel='shortcut icon' href='img/icon-512.png' type='image/x-icon' />

	<meta name="viewport" content="width=device-width, initial-scale=1">

	<meta name="theme-color" content="#03a9f4">
	<link rel="manifest" href="manifest.json" />
</head>
<body style="background-color:#03A9F4;">
	<div class="triangles"></div>
	<div class="triangles2"></div>
	<div class="container-fluid">
		<div class="container">
			<div class="row padding-vertical">
				<div class="title">
					Qwins!
				</div>
			</div>
		</div>
		<div class="container main-menu">
			<div class="row padding-vertical-2x">
				<div class="box col-md-5" style="background-color:#FFF;"><b>Rozpocznij grę</b><br /><br />Utwórz grę, do której będą mogli dołączyć znajomi!<br /><br />Po jej utworzeniu możesz ustawić kategorie pytań oraz inne opcje rozgrywki. Podany numer rozgrywki pozostali gracze wpisują w pole "ID gry" w bloku obok.<br /><br />
				<form method="post" action="lobby.php">
					<div class="form_input"><input type="text" name="nick" maxlength="15" value="host-<?=rand(1000, 9999)?>" required /><label>Nick</label></div>
					<input type="submit" class="button" name="host" value="Utwórz grę" />
				</form>
				</div>
				<div class="box col-md-5" style="background-color:#FFF;"><b>Dołącz do istniejącej gry</b><br /><br />Jeśli ktoś już utworzył grę, wpisz jej numer poniżej i kliknij połącz, aby dołączyć do gry.<br /><br />
				<form method="post" action="lobby.php">
					<div class="form_input"><input type="text" name="nick" maxlength="15" value="player-<?=rand(1000, 9999)?>" required /><label>Nick</label></div>
					<div class="form_input"><input type="number" name="game_id" min="1000" max="9999" step="1" value="<?=$game_id?>" required /><label>ID gry</label></div>
					<input type="submit" class="button" name="connect" value="Połącz" />
				</form>
				</div>

				<div class="report_bug col-md-4">
				<a href="https://krylan.ovh/portfolio/contact/" target="_blank"><i class="material-icons">bug_report</i> Zgłoś błąd</a>
				</div>
			</div>
		</div>
	</div>
</body>
</html>