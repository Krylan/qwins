<?php require_once 'connect.php';
session_start();
if(isset($_GET['pass']) && $_GET['pass'] == 'admin'){ $_SESSION['admin'] = true; }
if($_SESSION['admin'] != true){ header("Location: index.php"); }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8" />
	<title>Qwins! Manager</title>

	<link rel="stylesheet" href="css/style_manager.css" />
	<link rel="stylesheet" href="css/bootstrap.min.css" />
	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

	<meta name="viewport" content="width=device-width, initial-scale=1">

</head>
<body>
	<div id="menu-bar" class="container-fluid">
		<div class="title">
			Qwins!<br />
			<span>Manager</span>
		</div>
	</div>
	<div class="container padding-vertical-2x">
		<div class="row">
			<div class="col-md-9">
			<?php 
			$c_id = isset($_GET['c_id']) ? (int)$_GET['c_id'] : 0; // CATEGORY ID
			$q_id = isset($_GET['q_id']) ? (int)$_GET['q_id'] : 0; // QUESTION ID
			$view = 'category'; // Nazwa widoku

			// Tworzymy ścieżkę, po której można nawigować
			function make_breadcrumbs(){
				global $breadcrumbs;
				global $mysql, $c_id, $q_id;
				global $view;
				$breadcrumbs = '<div id="breadcrumbs" data-cid="'.$c_id.'" data-qid="'.$q_id.'"><a href="manage_quiz.php">Home</a>';
				if($c_id > 0){
					$view = 'question';
					$get_category_name = $mysql->query("SELECT id, name FROM qwins_category WHERE id=$c_id");
					$show_category_name = $get_category_name->fetch_object();
					$breadcrumbs .= '<i class="material-icons">chevron_right</i><a href="manage_quiz.php?c_id='.$show_category_name->id.'" data-label="Kategoria">'.$show_category_name->name.'</a>';
				}
				if($q_id > 0){
					$view = 'answer';
					$get_q_content = $mysql->query("SELECT content FROM qwins_question WHERE id = $q_id");
					$show_q_content = $get_q_content->fetch_object();
					$breadcrumbs .= '<i class="material-icons">chevron_right</i><a href="#" data-label="Pytanie">'.$show_q_content->content.'</a>';
				}
				$breadcrumbs .= '</div>';
			}
			make_breadcrumbs();

			// Jeśli nie wybraliśmy kategorii ani pytania, to znajdujemy się w widoku kategorii
			if($c_id <= 0 && $q_id <= 0):
			?>
			<div class="col-md-12">
				<?=$breadcrumbs?>
				<div id="add-category" class="box add-new background-icon">Dodaj nową kategorię<i class="material-icons">add_circle</i></div>
			</div>
			<div class="col-md-12">
			<?php
			$get_categories = $mysql->query("SELECT qwins_category.id, qwins_category.name, qwins_category.difficulty, COUNT(qwins_question.id) AS question_counter 
			FROM qwins_category LEFT JOIN qwins_question 
			ON qwins_category.id = qwins_question.category_id
			GROUP BY qwins_category.id");
			if($get_categories->num_rows > 0){
				while($show_categories = $get_categories->fetch_object()){
					$difficulty = '';
					if($show_categories->difficulty == 1){ $difficulty = '<span data-difficulty="1">Łatwy</span>'; }
					if($show_categories->difficulty == 2){ $difficulty = '<span data-difficulty="2">Średnio zaawansowany</span>'; }
					if($show_categories->difficulty == 3){ $difficulty = '<span data-difficulty="3">Trudny</span>'; }
					echo '<a href="manage_quiz.php?c_id='.$show_categories->id.'"><div class="box box-quiz">
						<h4>'.$show_categories->name.'</h4>
						'.$difficulty.'
						<div class="question-counter">'.$show_categories->question_counter.'<i class="material-icons">help</i></div>
					</div></a>';
				}
			}
			?>
			</div>
			<?php endif;
			// Jeśli wybraliśmy kategorię, to znajdujemy się w widoku pytań
			if($c_id > 0 && $q_id <= 0): ?>
			<div class="col-md-12">
				<?=$breadcrumbs?>
				<div id="add-question" class="box add-new background-icon">Dodaj nowe pytanie<i class="material-icons">add_circle</i></div>
			</div>
			<div class="col-md-12">
			<?php
			$get_questions = $mysql->query("SELECT qwins_question.id, qwins_question.content, SUM(qwins_answer.correct) AS correct, COUNT(qwins_answer.id) AS answer_counter FROM qwins_question LEFT JOIN qwins_answer ON qwins_question.id = qwins_answer.question_id WHERE qwins_question.category_id = $c_id 
			GROUP BY qwins_question.id");
			if($get_questions->num_rows > 0){
				while($show_questions = $get_questions->fetch_object()){
					if($show_questions->correct == 0){
						$color = 'color:#F00;';
					}else{ $color = ''; }
					echo '<span class="copy-question" data-qid="'.$show_questions->id.'"><i class="material-icons" title="Kopiuj">content_copy</i></span>';
					echo '<a href="manage_quiz.php?c_id='.$c_id.'&q_id='.$show_questions->id.'"><div class="box box-quiz">
						<h4>'.$show_questions->content.'</h4>
						<div class="question-counter" style="'.$color.'">'.$show_questions->answer_counter.'<i class="material-icons">feedback</i></div>
					</div></a>';
				}
			}else{
				//header("Location: manage_quiz.php");
			}
			?>
			</div>
			<?php endif;
			// Jeśli wybraliśmy pytanie, to znajdujemy się w widoku odpowiedzi
			if($c_id > 0 && $q_id > 0): ?>
			<div class="col-md-12">
				<?=$breadcrumbs?>
			</div>
			<div class="col-md-12">
			<?php
			$get_question = $mysql->query("SELECT id, content FROM qwins_question WHERE id = $q_id AND category_id = $c_id");
			$get_answers = $mysql->query("SELECT id, content, correct FROM qwins_answer WHERE question_id = $q_id ORDER BY correct DESC, id ASC");
			if($get_question->num_rows > 0){
				$show_question = $get_question->fetch_object();
				echo '<div class="box box-quiz">
					<input id="input-question" class="input-question" data-id="'.$show_question->id.'" value="'.$show_question->content.'" />';
				echo '<ul id="list-answer" class="box-list">';
				if($get_answers->num_rows > 0){
					while($show_answers = $get_answers->fetch_object()){
						echo '<li>
							<input class="input-answer" data-id="'.$show_answers->id.'" value="'.$show_answers->content.'" />';
							if($show_answers->correct == 1){
								$checked = 'checked';
							}else{ $checked = ''; }
							echo '<span>
								<input type="radio" id="answer_'.$show_answers->id.'" class="correct_answer" name="correct_answer" value="'.$show_answers->id.'" '.$checked.' />
								<label for="answer_'.$show_answers->id.'"></label>
								</span>';
						echo '</li>';
					}
				}
				echo '</ul>
				<div id="add-answer" class="flat add-new background-icon" data-id="'.$show_question->id.'">Dodaj nową odpowiedź<i class="material-icons">add_circle</i></div>
				</div>';
			}else{
				//header("Location: manage_quiz.php");
			}
			?>
			</div>
			<?php endif; ?>
			</div>
		</div>
	</div>
	
<script>
const view = '<?=$view?>';
</script>
<script>
// Funkcja dla wszystkich żądań AJAX
function ajaxRequest(action, content, c_id=0, q_id=0, id=0){
  return new Promise((resolve, reject) => {
    const req = new XMLHttpRequest();
    req.open('GET', 'ajax/manager.php?action='+action+'&content='+content+'&c_id='+c_id+'&q_id='+q_id+'&id='+id);
    req.onload = () => req.status === 200 ? resolve(req.response) : reject(Error(req.statusText));
    req.onerror = (e) => reject(Error(`Network Error: ${e}`));
    req.send();
  });
}

/* W widoku pytań możemy:
	- Edytować pytanie
	- Dodać odpowiedź
	- Edytować odpowiedź
	- Zaznaczyć odpowiedź jako poprawną
*/
if(view == 'answer'){
	document.querySelector('#add-answer').onclick = function(){
		let content = '';
		let id = this.dataset.id;
		ajaxRequest('addAnswer', content, 0, 0, id).then((data) => {
			data = JSON.parse(data);
			if(data.success == 1){
				document.querySelector('#list-answer').insertAdjacentHTML('beforeend', '<li><input class="input-answer" data-id="'+data.thisId+'" value="" /><span><input type="radio" id="answer_'+data.thisId+'" class="correct_answer" name="correct_answer" value="'+data.thisId+'" /><label for="answer_'+data.thisId+'"></label></span></li>');
				let element = document.querySelector('[data-id="'+data.thisId+'"]');
				let element_radio = document.querySelector('[value="'+data.thisId+'"]');
				answerEditEvent(element);
				answerEditCorrectEvent(element_radio);
			}
		});
	}

	document.querySelector('.input-question').onchange = function(){
		let content = this.value;
		let id = this.dataset.id;
		ajaxRequest('editQuestion', content, 0, 0, id).then((data) => {
		
		});
	}

	document.querySelectorAll('.input-answer').forEach(
		function(element){ answerEditEvent(element); }
	);

	document.querySelectorAll('[name="correct_answer"]').forEach(
		function(element){ answerEditCorrectEvent(element); }
	);

	function answerEditCorrectEvent(element){
		element.onchange = function(){
			let id = this.value;
			let q_id = document.querySelector('#input-question').dataset.id;
			ajaxRequest('editCorrectAnswer', id, 0, q_id, id).then((data) => {
			
			});
		}
	}

	function answerEditEvent(element){
		element.onchange = function(){
			let content = this.value;
			let id = this.dataset.id;
			ajaxRequest('editAnswer', content, 0, 0, id).then((data) => {
			
			});
		}
	}
}

/* W widoku kategorii możemy:
	- Dodać kategorię
*/
if(view == 'category'){
	document.querySelector('#add-category').onclick = function(){ 
		let content = prompt("Wpisz nazwę kategorii", "");
		
		if (content != null) {
			ajaxRequest('addCategory', content, 0, 0, 0).then((data) => {
				window.location.reload(true);
			});
		} 
	};
}

/* W widoku pytań możemy:
	- Dodać pytanie
	– Skopiować pytanie
*/
if(view == 'question'){
	document.querySelector('#add-question').onclick = function(){ 
		let content = prompt("Wpisz treść pytania", "");
		let c_id = document.querySelector('#breadcrumbs').dataset.cid;
		
		if (content != null) {
			ajaxRequest('addQuestion', content, c_id, 0, 0).then((data) => {
				window.location.reload(true);
			});
		} 
	};

	let copyLink = document.querySelectorAll('.copy-question');
	for (let i = 0; i < copyLink.length; i++) {
		copyLink[i].addEventListener('click', function(event) {
			this.onclick = function(){
			let content = prompt("Wpisz treść pytania", "");
			let c_id = document.querySelector('#breadcrumbs').dataset.cid;
			let q_id = this.dataset.qid;

			if (content != null) {
				ajaxRequest('copyQuestion', content, c_id, q_id, 0).then((data) => {
					window.location.reload(true);
				});
			}
		};
		});
	}
}
</script>
</body>
</html>