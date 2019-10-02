# Remote CMD
System służący do sterowania hostami
## Założenia
System składający się z aplikacji działającej w tle na maszynie hosta, serwera php i websocket oraz aplikacji webowej.
Celem systemu jest udostępnienie interfejsu do zarządzania wszystkimi hostami z uruchomionymi aplikacjami klienckimi w tle.

To repozytorium (remote-cmd) zawiera kod aplikacji webowej, php oraz skryptów php backendu. 
## Serwer websocket
Lokalizacja: `/servers/ws_server.php`
Serwer można włączyć ręcznie poprzez `php ws_server.php start` lub z pozycji konsoli po zalogowaniu
