# qwins

Prosta gra w quiz pozwalająca na rozgrywkę za pomocą smartphone'ów. Zawiera tryb dla pojedynczego gracza, jak i dla wielu osób.

## Instalacja

Aby uruchomić grę na swoim serwerze, potrzebujemy serwera z zainstalowanym PHP oraz bazą danych MySQL. Importujemy plik qwins.sql, a następnie tworzymy w katalogu głównym plik connect.php, w którym znajdować się będzie połączenie z bazą. Pobieramy także bibliotekę datachannel.js (https://github.com/muaz-khan/DataChannel) i umieszczamy ją w katalogu głównym. Jeśli korzystamy z Firebase jako serwera sygnalizującego, tworzymy plik initialize_firebase.js, w którym wklejamy kawałek kodu z panelu zarządzania Firebase. Po tym wszystkim otwieramy w przeglądarce plik manage_quiz.php, w którym możemy zarządzać quizami i dodawać swoje kategorie/pytania/odpowiedzi. Kiedy już wszystko będzie gotowe, możemy otworzyć index.php i rozpocząć rozgrywkę.

## Demo

Oficjalną wersję możecie znaleźć pod tym adresem: https://krylan.ovh/qwins/

## Pomoc

W razie pytań, błędów czy sugestii, zapraszam do kontaktu ze mną poprzez formularz: https://krylan.ovh/portfolio/contact/ lub poprzez tę stronę GitHub.

Życzę udanej rozgrywki i świetnej zabawy!