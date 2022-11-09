@echo off

echo "アップロードコマンド実行"

rem JAVA環境情報セット
set JAVA_HOME="C:\Program Files\Java\jre1.8.0_121"

rem ルート証明書情報セット
set JAVA_OPT_TRUSTSTR=-Djavax.net.ssl.trustStore=%JAVA_HOME%\lib\security\cacerts -Djavax.net.ssl.trustStorePassword=changeit

rem アップロード実行
java %JAVA_OPT_TRUSTSTR% -jar hccommand.jar -cmd upload -config config.xml -parent_object_path /test -upload_file D:\CommandClient_tool\upload\upload_data.txt

echo %errorlevel%

pause

exit
