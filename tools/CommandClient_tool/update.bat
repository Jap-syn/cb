@echo off

echo "�R�}���h�N���C�A���g�A�b�v�f�[�g���s"

rem JAVA�����Z�b�g
set JAVA_HOME="C:\Program Files\Java\jre1.8.0_121"

rem ���[�g�ؖ������Z�b�g
set JAVA_OPT_TRUSTSTR=-Djavax.net.ssl.trustStore=%JAVA_HOME%\lib\security\cacerts -Djavax.net.ssl.trustStorePassword=changeit

rem ���s�t�@�C���ihccommand.jar�j�A�b�v�f�[�g
java %JAVA_OPT_TRUSTSTR% -jar hcupdate.jar -config config.xml

pause

exit
