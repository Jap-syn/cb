@echo off

echo "�A�b�v���[�h�R�}���h���s"

rem JAVA�����Z�b�g
set JAVA_HOME="C:\Program Files\Java\jre1.8.0_121"

rem ���[�g�ؖ������Z�b�g
set JAVA_OPT_TRUSTSTR=-Djavax.net.ssl.trustStore=%JAVA_HOME%\lib\security\cacerts -Djavax.net.ssl.trustStorePassword=changeit

rem �A�b�v���[�h���s
java %JAVA_OPT_TRUSTSTR% -jar hccommand.jar -cmd upload -config config.xml -parent_object_path /test -upload_file D:\CommandClient_tool\upload\upload_data.txt

echo %errorlevel%

pause

exit
