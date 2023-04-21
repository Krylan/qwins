var channel = new DataChannel();

function runTime(targetTime, callback, color = '#6fdb6f'){
	var initialOffset = 0;
	var time = targetTime;

	document.querySelector('.circle_animation').setAttribute('stroke', color);
	
	var interval = setInterval(function(){
		document.querySelector('#question_timer').dataset.time = Math.ceil(time);
		document.querySelector('.circle_animation').style.strokeDashoffset = 125-(125*(time/targetTime));
		if (time <= 0){
			document.querySelector('.circle_animation').style.strokeDashoffset = 125;
			clearInterval(interval);
			if (typeof callback === "function") {
				callback();
			}
			return;
		}
		time -= 0.1;
	}, 100);
}

function blockAnswering(){
	document.querySelectorAll('.answer').forEach(function(element){
		element.classList.add('disabled');
	});
}

channel.onopen = function(userid){
	if(role === 'host'){
		var newPlayer = document.createElement("li");
		newPlayer.setAttribute('data-player', userid);
		newPlayer.innerHTML = '<i class="material-icons" data-uid="'+userid+'">smartphone</i> '+userid+'<i class="material-icons kick" style="float:right;" onclick="kick(\''+userid+'\');">block</i>';
		document.getElementById("player-list").appendChild(newPlayer);
		check_start();
	}
	if(role === 'player'){
		channel.send({ dataType: 'changeDevice', playerId: channel.userid, content: device });
	}
};

channel.onmessage = function(data, userid){
	if(role === 'player'){
		if(data.dataType == 'requestQuestion'){
			document.body.classList.add('overflow');
			var question = JSON.parse(data.content);
			document.querySelector("#question_counter").dataset.count = data.questionCount;
			insertQuestion(question);
			runTime(10, blockAnswering);
		}
		if(data.dataType == 'kickPlayer'){
			if(data.content == channel.userid){
				window.location.replace('index.php');
			}
		}
		if(data.dataType == 'showCorrect'){
			blockAnswering();
			document.querySelectorAll('.answer:not(.correct)').forEach(function(element){
				element.classList.add('hide');
			});
			runTime(5, function(){}, '#03A9F4');
		}
		if(data.dataType == 'sendScoreTable'){
			document.querySelector('#player-score-list').innerHTML = data.content;
			showScoreTable();
		}
		if(data.dataType == 'sendScoreTableEnd'){
			endGame();
		}
	}
	if(role == 'host'){
		if(data.dataType == 'selectAnswer'){
			if(document.querySelector('.answer.correct').dataset.answer == data.content){
				var timeLeft = document.querySelector('#question_timer').dataset.time;
				playerList[data.playerId] += timeLeft*10;
				document.querySelectorAll("#player-score-list li").forEach(function(element){
					if(element.dataset.player == data.playerId){
						element.querySelector('span').innerHTML = playerList[data.playerId];
					}
				});
			}
		}
		if(data.dataType == 'changeDevice'){
			document.querySelector('i[data-uid='+data.playerId+']').innerHTML = data.content;
		}
	}
};

channel.onleave = function(userid){
	if(role === 'host'){
		document.querySelector('[data-player="'+userid+'"]').remove();
		channel.eject(userid);
		check_start();
	}
	if(role === 'player' && userid.includes('host-')){
		window.location.replace('index.php');
	}
};

channel.onclose = function(event) {
};

function htmlDecode(input){
  var e = document.createElement('div');
  e.innerHTML = input;
  return e.childNodes[0].nodeValue;
}

function insertQuestion(data){
	document.querySelectorAll("#question_box .answer").forEach(function(element){
		element.classList.remove('correct');
		element.classList.remove('hide');
	});
	document.querySelector('#question_text').innerHTML = htmlDecode(data.question_content);
	document.querySelector('#question_text').dataset.category = data.category_name;
	if(data[0].correct == 1){ document.querySelector('#answer1_text').classList.add('correct'); }
	if(data[1].correct == 1){ document.querySelector('#answer2_text').classList.add('correct'); }
	if(data[2].correct == 1){ document.querySelector('#answer3_text').classList.add('correct'); }
	if(data[3].correct == 1){ document.querySelector('#answer4_text').classList.add('correct'); }
	document.querySelector('#answer1_text div').innerHTML = htmlDecode(data[0].content);
	document.querySelector('#answer2_text div').innerHTML = htmlDecode(data[1].content);
	document.querySelector('#answer3_text div').innerHTML = htmlDecode(data[2].content);
	document.querySelector('#answer4_text div').innerHTML = htmlDecode(data[3].content);
	document.querySelector('#quiz_game_container').classList.remove('hidden');
	document.querySelectorAll('.answer').forEach(function(element){
		element.classList.remove('disabled');
		element.classList.remove('selected');
		element.addEventListener('click', function(){
			if(!this.classList.contains('disabled') && role == 'host'){
				//document.querySelector('#audio-select').play();
				this.classList.add('selected');
				blockAnswering();
				if(document.querySelector('.answer.correct').dataset.answer == this.dataset.answer){
					var timeLeft = document.querySelector('#question_timer').dataset.time;
					playerList[channel.userid] += timeLeft*10;
					document.querySelectorAll("#player-score-list li").forEach(function(element){
						if(element.dataset.player == channel.userid){
							element.querySelector('span').innerHTML = playerList[channel.userid];
						}
					});
				}
			}
			if(!this.classList.contains('disabled') && role == 'player'){
				this.classList.add('selected');
				blockAnswering();
				channel.send({ dataType: 'selectAnswer', playerId: channel.userid, content: this.dataset.answer });
			}
		});
	});
}

function kick(userid){
	if(role === 'host'){
		channel.eject(userid);
		channel.send({ dataType: 'kickPlayer', content: userid });
		check_start();
	}
}

if(role === 'host'){
	channel.userid = nick;
	channel.open(game_id);
	var playerList = [];
	var question_count = 10;
	var solo_mode = false;
	
	var newPlayer = document.createElement("li");
	newPlayer.setAttribute('data-player', channel.userid);
	newPlayer.innerHTML = '<i class="material-icons">'+device+'</i> '+channel.userid;
	document.getElementById("player-list").appendChild(newPlayer);
	
	document.querySelector('#setting-solo-mode').addEventListener('change', function(){
		if(this.checked){ solo_mode = true; }
		else{ solo_mode = false; }
		check_start();
	});
}
if(role === 'player'){
	channel.userid = nick;
	
	firebase.database().ref(game_id).once('value', function (data) {
		var isChannelPresent = data.val() != null;
		if (!isChannelPresent) {
			window.location.replace('index.php');
		} else {
			channel.connect(game_id);
		}
	});
}

function check_start(){
	var player_count = document.getElementById("player-list").getElementsByTagName("li").length;
	var setting_question_cnt = parseInt(document.querySelector('#setting-question-cnt').value);
	if(((player_count >= 2 && solo_mode == false) || (player_count == 1 && solo_mode == true)) && selected_quiz.length > 0 && setting_question_cnt >= 3 && setting_question_cnt <= 25){
		document.querySelector('.start-game').classList.remove('disabled');
		return true;
	}else{
		document.querySelector('.start-game').classList.add('disabled');
		return false;
	}
}

function select_quiz(id){
	if(selected_quiz.indexOf(id) != -1){
		selected_quiz.splice(selected_quiz.indexOf(id), 1);	
	}else{
		selected_quiz.push(id);
	}
	check_start();
}

function start_game(){
	if(check_start() == true){
		question_count = parseInt(document.querySelector('#setting-question-cnt').value);
		document.querySelectorAll("#player-list li").forEach(function(element){
			playerList[element.dataset.player] = 0;
			var node = document.createElement("LI");
			node.innerHTML = element.dataset.player+'<span>'+0+'</span>';
			node.dataset.player = element.dataset.player;
			document.querySelector('#player-score-list').appendChild(node);
		});
		document.querySelector('#quiz_game_container').classList.remove('hidden');
		ajaxRequest();
	}
}

function endQuestion(){
	blockAnswering();
	document.querySelectorAll('.answer:not(.correct)').forEach(function(element){
		element.classList.add('hide');
	});
	channel.send({ dataType: 'showCorrect', content: '' });
	runTime(5, showScoreTable, '#03A9F4');
}

function endGame(){
	document.querySelector('#score-table').classList.add('show');
	document.querySelector('#score-table').classList.remove('hidden');
	document.querySelector('#question_timer').dataset.time = 'Koniec gry';
	document.querySelector('#question_timer').classList.add('score-table');
}

function showScoreTable(){
	document.querySelector('#question_timer').classList.add('score-table');
	document.querySelector('#score-table').classList.remove('hidden');
	if(role === 'host'){
		var paraArr = [].slice.call(document.querySelectorAll("#player-score-list li")).sort(function(elementA, elementB){
			if(parseInt(elementA.querySelector('span').innerHTML) > parseInt(elementB.querySelector('span').innerHTML)){
				return -1;
			}
			if(parseInt(elementA.querySelector('span').innerHTML) < parseInt(elementB.querySelector('span').innerHTML)){
				return 1;
			}
			return 0;
		});
		paraArr.forEach(function (p) {
			document.querySelector("#player-score-list").appendChild(p);
		});
		var score_table = document.querySelector('#player-score-list').innerHTML;
		if(shown_questions.length == question_count){
			endGame();
			channel.send({ dataType: 'sendScoreTableEnd', content: score_table });
		}else{
			runTime(5, ajaxRequest, '#FFF');
			channel.send({ dataType: 'sendScoreTable', content: score_table });
		}
	}
	if(role === 'player'){
		runTime(5, function(){
			document.querySelector('#question_timer').classList.remove('score-table');
			document.querySelector('#score-table').classList.add('hidden');
		}, '#FFF');
	}
}

function ajaxRequest(){
  return new Promise((resolve, reject) => {
    const req = new XMLHttpRequest();
    req.open('POST', 'ajax/game.php');
	req.setRequestHeader("Content-type", "application/json");
    req.onload = () => req.status === 200 ? resolve(req.response) : reject(Error(req.statusText));
    req.onerror = (e) => reject(Error(`Network Error: ${e}`));
    var data = JSON.stringify({"action":"requestQuestion","category_array":selected_quiz,"shown_questions": shown_questions});
	req.send(data);
	req.addEventListener("readystatechange", function () {
	  if(this.readyState === 4){
		document.querySelector('#question_timer').classList.remove('score-table');
		document.querySelector('#score-table').classList.add('hidden');
		var data = JSON.parse(this.responseText);
		shown_questions.push(parseInt(data.question_id));
		document.querySelector("#question_counter").dataset.count = shown_questions.length;
		insertQuestion(data);
		channel.send({ dataType: 'requestQuestion', content: this.responseText, questionCount: shown_questions.length });
		runTime(10, endQuestion);
	  }
	});
  });
}