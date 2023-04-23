<?php
require_once '../connect.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"));
$action = $data->action;

$category_string = implode(',', $data->category_array);
$data->shown_questions[] = 0;
$questions_string = implode(',', $data->shown_questions);

if($action == 'requestQuestion'){
	$get_question = $mysql->query("SELECT qwins_question.id, qwins_question.content, qwins_category.name  
	FROM qwins_question 
	INNER JOIN qwins_answer ON qwins_question.id=qwins_answer.question_id 
	INNER JOIN qwins_category ON qwins_question.category_id=qwins_category.id 
	WHERE qwins_question.category_id IN ($category_string) 
	AND qwins_question.id NOT IN ($questions_string) 
	ORDER BY RAND() LIMIT 1");
	$response = array();
	if($get_question->num_rows > 0){
		$show_question = $get_question->fetch_object();
		$question_id = $show_question->id;
		$get_wrong_answer = $mysql->query("SELECT * FROM qwins_answer WHERE question_id=$question_id AND correct=0 ORDER BY RAND() LIMIT 3");
		$get_correct_answer = $mysql->query("SELECT * FROM qwins_answer WHERE question_id=$question_id AND correct=1 LIMIT 1");
		if($get_wrong_answer->num_rows > 0 && $get_correct_answer->num_rows > 0){
			$response = array('question_id' => $show_question->id, 'question_content' => $show_question->content, 'category_name' => $show_question->name);
			while($show_correct_answer = $get_correct_answer->fetch_object()){
				$correct_answer = array('id' => $show_correct_answer->id, 'content' => $show_correct_answer->content, 'correct' => '1');
				$answer_place = floor(rand(0,3));
			}
			$i = 0;
			while($show_wrong_answer = $get_wrong_answer->fetch_object()){
				if($i == $answer_place && $i < 3){
					array_push($response, $correct_answer);
				}
				$answer = array('id' => $show_wrong_answer->id, 'content' => $show_wrong_answer->content, 'correct' => '0');
				array_push($response, $answer);
				$i++;
			}
			if($i == 3){
				array_push($response, $correct_answer);
			}
		}
		
	}
	echo json_encode($response);
}