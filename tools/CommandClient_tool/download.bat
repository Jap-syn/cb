@echo off

echo "�_�E�����[�h�R�}���h���s"

rem JAVA�����Z�b�g
set JAVA_HOME="C:\Program Files\Java\jre1.8.0_121"

rem ���[�g�ؖ������Z�b�g
set JAVA_OPT_TRUSTSTR=-Djavax.net.ssl.trustStore=%JAVA_HOME%\lib\security\cacerts -Djavax.net.ssl.trustStorePassword=changeit

rem �_�E�����[�h���s
java %JAVA_OPT_TRUSTSTR% -jar hccommand.jar -cmd download -config -config.xml -parent_object_path /test -registered_file_name upload_data.txt -local_directory D:\CommandClient_tool\download
echo %errorlevel%

pause

exit
