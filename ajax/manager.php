<?php
require '../connect.php';

$action = $_GET['action'];

// Dodawanie kategorii
if($action == 'addCategory'){
	$content = htmlspecialchars($_GET['content']);
	
	$add_category = $mysql->query("INSERT INTO qwins_category (id, name) VALUES ('', '$content')");
}

// Dodawanie pytania
if($action == 'addQuestion'){
	$c_id = (int)$_GET['c_id'];
	$content = htmlspecialchars($_GET['content']);
	
	$check_category = $mysql->query("SELECT id qwins_category WHERE id=$c_id");
	if($check_category->num_rows > 0){
		$add_question = $mysql->query("INSERT INTO qwins_question (id, category_id, content, difficulty) VALUES ('', $c_id, '$content', 0)");
	}
}
// Edycja pytania
if($action == 'editQuestion'){
	$id = (int)$_GET['id'];
	$content = htmlspecialchars($_GET['content']);
	
	$update_question = $mysql->query("UPDATE qwins_question SET content='$content' WHERE id=$id");
}
// Dodawanie odpowiedzi
if($action == 'addAnswer'){
	$q_id = (int)$_GET['id'];
	
	$check_question = $mysql->query("SELECT id qwins_question WHERE id=$q_id");
	if($check_category->num_rows > 0){
		$add_answer = $mysql->query("INSERT INTO qwins_answer (id, question_id, content, correct) VALUES ('', $q_id, '', 0)");
		$new_id = $mysql->insert_id;
		
		if($add_answer){
			$result = array('success' => 1, 'thisId' => $new_id);
		}
		
		echo json_encode($result);
	}
}
// Edycja odpowiedzi
if($action == 'editAnswer'){
	$id = (int)$_GET['id'];
	$content = htmlspecialchars($_GET['content']);
	
	$update_answer = $mysql->query("UPDATE qwins_answer SET content='$content' WHERE id=$id");
}
// Oznaczanie poprawnej odpowiedzi
if($action == 'editCorrectAnswer'){
	$id = (int)$_GET['id'];
	$q_id = (int)$_GET['q_id'];
	
	$clear_answers = $mysql->query("UPDATE qwins_answer SET correct=0 WHERE question_id=$q_id");
	$update_correct_answer = $mysql->query("UPDATE qwins_answer SET correct=1 WHERE question_id=$q_id AND id=$id");
}
?>