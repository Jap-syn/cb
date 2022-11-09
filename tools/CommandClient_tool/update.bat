@echo off

echo "コマンドクライアントアップデート実行"

rem JAVA環境情報セット
set JAVA_HOME="C:\Program Files\Java\jre1.8.0_121"

rem ルート証明書情報セット
set JAVA_OPT_TRUSTSTR=-Djavax.net.ssl.trustStore=%JAVA_HOME%\lib\security\cacerts -Djavax.net.ssl.trustStorePassword=changeit

rem 実行ファイル（hccommand.jar）アップデート
java %JAVA_OPT_TRUSTSTR% -jar hcupdate.jar -config config.xml

pause

exit
