<?php
require_once '../connect.php';
session_start();

$action = $_GET['action'];
if($_SESSION['admin'] !== true){ http_response_code(304);exit(); }

// Dodawanie kategorii
if($action == 'addCategory'){
	$content = htmlspecialchars($_GET['content']);
	
	$add_category = $mysql->query("INSERT INTO qwins_category (name) VALUES ('$content')");
}

// Dodawanie pytania
if($action == 'addQuestion'){
	$c_id = (int)$_GET['c_id'];
	$content = htmlspecialchars($_GET['content']);
	
	$add_question = $mysql->query("INSERT INTO qwins_question (category_id, content) VALUES ($c_id, '$content')");
}

// Kopiowanie pytania
if($action == 'copyQuestion'){
	$c_id = (int)$_GET['c_id'];
	$q_id = (int)$_GET['q_id'];
	$content = htmlspecialchars($_GET['content']);

	$add_question = $mysql->query("INSERT INTO qwins_question (category_id, content) VALUES ($c_id, '$content')");
	$new_q_id = $mysql->insert_id;
	$add_answers = $mysql->query("INSERT INTO qwins_answer (question_id, content, correct) SELECT $new_q_id, content, 0 FROM qwins_answer WHERE question_id=$q_id");
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

	$add_answer = $mysql->query("INSERT INTO qwins_answer (question_id, content, correct) VALUES ($q_id, '', 0)");
	$new_id = $mysql->insert_id;

	if($add_answer){
		$result = array('success' => 1, 'thisId' => $new_id);
	}

	echo json_encode($result);
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