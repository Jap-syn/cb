INSERT INTO M_CodeManagement VALUES(204, 'SMBCヴァーチャル口座自動取込エラーメッセージ', NULL, 'SMBCヴァーチャル口座自動取込エラーメッセージ', 1, NULL, 0, NULL, 0, NULL, NOW(), 9, NOW(), 9, 1);
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12000, '内容）認証エラーです。
      ログイン画面よりログインしてください。
対処）再ログインが必要です。
      ログイン画面よりログインし直してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12001, '内容）権限情報の取得ができません。
対処）権限グループが存在しているかご確認ください。
      または、DB と正常に接続できることをご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12002, '内容）既にデータが存在します。
対処）新規作成の場合、共通リクエストインタフェースの更新フラグを上書き更新に設定して API を呼び出してください。
      更新の場合、既存のオブジェクトと重複しないオブジェクト名を指定してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12003, '内容）アルゴリズムが存在しません。
対処）Java 実行環境 (JDK) の JCE(Java Cryptography Extension) 設定が正しく行われているかご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12005, '内容）データが存在しません。
対処）該当データが存在するかをご確認ください。
      また、HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12006, '内容）セッションの要求が不正です。
対処）リクエストパラメータの値をご確認ください。
      または正しい画面遷移となるよう、操作をやり直してください。
      エラーが改善されない場合は、ログイン画面よりログインし直してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12007, '内容）アプリケーションの試用期限が過ぎています。
      管理者へ連絡してください。
対処）試用期限後に本製品を使用する場合は、製品版へのアップデートを行ってください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12008, '内容）サーバがセットアップされていません。
      管理者に連絡してください。
対処）導入マニュアルを参照の上、サーバをセットアップしてください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12009, '内容）ユーザの登録数が上限となっているためユーザ情報を登録できません。
      接続ライセンスを追加設定してください。
対処）導入マニュアルを参照の上、サーバをセットアップしてください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12010, '内容）アップロード権限がありません。
対処）この操作を行うには、ログインユーザにアップロード権限が必要です。
      ログインユーザにアップロード権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12011, '内容）ダウンロード権限がありません。
対処）この操作を行うには、ログインユーザにダウンロード権限が必要です。
      ログインユーザにダウンロード権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12012, '内容）ツールダウンロード権限がありません。
対処）この操作を行うには、ログインユーザにツールダウンロード権限が必要です。
      ログインユーザにツールダウンロード権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12013, '内容）フォルダ作成権限がありません。
対処）この操作を行うには、ログインユーザにフォルダ作成権限が必要です。
      ログインユーザにフォルダの作成権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12014, '内容）フォルダ削除権限がありません。
対処）この操作を行うには、ログインユーザにフォルダ削除権限が必要です。
      ログインユーザにフォルダの削除権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12015, '内容）履歴参照権限がありません。
対処）この操作を行うには、ログインユーザに履歴参照権限が必要です。
      ログインユーザに履歴の参照権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12016, '内容）履歴削除権限がありません。
対処）この操作を行うには、ログインユーザに履歴削除権限が必要です。
      ログインユーザに履歴の削除権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12017, '内容）システム管理参照権限がありません。
対処）この操作を行うには、ログインユーザにシステム管理参照権限が必要です。
      ログインユーザにシステム管理の参照権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12018, '内容）システム管理更新権限がありません。
対処）この操作を行うには、ログインユーザにシステム管理更新権限が必要です。
      ログインユーザにシステム管理の更新権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12019, '内容）システム管理削除権限がありません。
対処）この操作を行うには、ログインユーザにシステム管理削除権限が必要です。
      ログインユーザにシステム管理の削除権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12020, '内容）システム管理の参照権限、更新権限、および削除権限がありません。
対処）この操作を行うには、ログインユーザにシステム管理の参照権限、更新権限、および削除権限が必要です。
      ログインユーザにシステム管理の参照権限、更新権限、および削除権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12050, '内容）グループ管理権限では当該操作を行うことができません。
対処）この操作を行うには、ログインユーザにシステム管理権限が必要です。
      ログインユーザにシステム管理権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12100, '内容）ログインエラーです。
対処）グループ ID 及びユーザ ID、パスワードを確認し、ログイン画面よりログインし直してください。
      また、HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12101, '内容）二重ログインです。
対処）既にログインしているユーザが正当なユーザではない場合、強制ログアウトを行ってください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12102, '内容）該当のユーザは、現在ロックアウト中です。
対処）ロックアウト期間の設定がない場合は、手動でロックアウトを解除してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12111, '内容）ユーザ ID が有効期限切れです。
対処）ユーザ情報を更新してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12112, '内容）パスワードが有効期限切れです。
対処）パスワードを更新してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12113, '内容）制限付ログインの為、このリクエストは拒否されました。
対処）パスワードの有効期限が切れています。
      パスワードを更新してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12114, '内容）再ログインに失敗しました。
対処）ログイン済みのセッションをログアウトするか、再ログインチェックフラグを無効にしてログインしてください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12200, '内容）グループ情報の参照権限がありません。
対処）この操作を行うには、ログインユーザにシステム管理参照権限が必要です。
      ログインユーザにシステム管理の参照権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12201, '内容）グループ情報の削除権限がありません。
対処）この操作を行うには、ログインユーザにシステム管理削除権限が必要です。
      ログインユーザにシステム管理の削除権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12202, '内容）グループ情報の更新権限がありません。
対処）この操作を行うには、ログインユーザにシステム管理更新権限が必要です。
      ログインユーザにシステム管理の更新権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12211, '内容）登録可能なユーザ数より、既に登録されているユーザ数が多いため、グループ情報を登録できません。
対処）登録可能なユーザ数には、既に登録されているユーザ数以上の値を指定してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12212, '内容）ログイン方法がクライアント証明書の場合は、認証サーバを利用できません。
対処）ログイン方法を変更するか、ID ／パスワード認証方法では HULFT-WebFileTransfer の認証機能を利用してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12300, '内容）ユーザ情報の参照権限がありません。
対処）この操作を行うには、ログインユーザにシステム管理参照権限が必要です。
      ログインユーザにシステム管理の参照権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12301, '内容）ユーザ情報の削除権限がありません。
対処）この操作を行うには、ログインユーザにシステム管理削除権限が必要です。
      ログインユーザにシステム管理の削除権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12302, '内容）ユーザ情報の更新権限がありません。
対処）この操作を行うには、ログインユーザにシステム管理更新権限が必要です。
      ログインユーザにシステム管理の更新権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12303, '内容）管理対象外となるグループ ID が指定されているため当該操作を行うことができません。
対処）この操作を行うには、ログインユーザにシステム管理権限が必要です。
      ログインユーザにシステム管理権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12304, '内容）グループ管理権限ではログインユーザの権限を超える権限グループは指定できません。
対処）ログインユーザの権限と設定対象のユーザの権限をご確認ください。
      ログインユーザに付与されていない権限を持つ権限グループは指定できません。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12305, '内容）管理対象外となる権限グループが指定されているため当該操作を行うことができません。
対処）この操作を行うには、ログインユーザにシステム管理権限が必要です。
      ログインユーザにシステム管理権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12306, '内容）グループ管理権限ではログインユーザの権限を超えるユーザ情報を削除できません。
対処）ログインユーザの権限と削除対象のユーザの権限をご確認ください。
      ログインユーザに付与されていない権限を持つユーザ情報を削除できません。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12307, '内容）ログイン方法がクライアント証明書となっているため、パスワードは設定できません。
対処）ログイン方法にクライアント証明書を設定する場合は、ユーザ情報にパスワードを含めないでください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12311, '内容）数字 (0-9) だけのユーザ ID のため、登録できません。
対処）ユーザ ID に数字以外の文字を含めてください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12312, '内容）英字 (a-z,A-Z) だけのユーザ ID のため、登録できません。
対処）ユーザ ID に英字以外の文字を含めてください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12313, '内容）数字 (0-9) だけのパスワードのため、登録できません。
対処）パスワードに数字以外の文字を含めてください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12314, '内容）英字 (a-z,A-Z) だけのパスワードのため、登録できません。
対処）パスワードに英字以外の文字を含めてください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12315, '内容）ユーザ ID とパスワードが同一のため、登録できません。
対処）ユーザ ID とパスワードは異なる文字列を指定してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12316, '内容）パスワードにユーザ ID が含まれているため、登録できません。
対処）パスワードには、ユーザ ID を含まない文字列を指定してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12317, '内容）パスワードの文字数が不足しているため、登録できません。
対処）パスワードに指定する文字数を増やしてください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12318, '内容）パスワードが未入力です。
対処）パスワードを入力してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12319, '内容）認証サーバを使用する場合、パスワードは設定できません。
対処）認証サーバを使用して認証を行う場合は、ユーザ情報にパスワードを含めないでください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12320, '内容）パスワード変更禁止期間中のため、パスワードは変更できません。
対処）パスワードの変更禁止期間後に、改めてパスワードの変更を実施してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12321, '内容）以前に登録したパスワードのため、パスワードの更新はできません。
対処）以前に登録したパスワードと違うパスワードを設定してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12322, '内容）グループに登録可能なユーザ数の上限値を超過するため、ユーザ情報を登録できません。
対処）グループ情報をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12400, '内容）権限グループ情報の参照権限がありません。
対処）この操作を行うには、ログインユーザにシステム管理参照権限が必要です。
      ログインユーザにシステム管理の参照権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12401, '内容）権限グループ情報の削除権限がありません。
対処）この操作を行うには、ログインユーザにシステム管理削除権限が必要です。
      ログインユーザにシステム管理の削除権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12402, '内容）権限グループ情報の更新権限がありません。
対処）この操作を行うには、ログインユーザにシステム管理更新権限が必要です。
      ログインユーザにシステム管理の更新権限を持つ権限グループを付与してください', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12403, '内容）管理対象のグループの登録数が上限値を超過しているため、権限グループ情報を登録できません。
対処）プロパティファイル（env.properties）のグループ管理の最大数（auth.managed.groupid.max.count）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12500, '内容）オブジェクト権限情報の参照権限がありません。
対処）この操作を行うには、ログインユーザにシステム管理参照権限が必要です。
      ログインユーザにシステム管理の参照権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12501, '内容）オブジェクト権限情報の削除権限がありません。
対処）この操作を行うには、ログインユーザにシステム管理削除権限が必要です。
      ログインユーザにシステム管理の削除権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12502, '内容）フォルダの作成権限がありません。
対処）この操作を行うには、ログインユーザにフォルダ作成権限が必要です。
      ログインユーザにフォルダの作成権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12503, '内容）アクセス権限がありません。
対処）この操作を行うには、ログインユーザに該当オブジェクトのアクセス権限が必要です。
      ログインユーザに該当オブジェクトのアクセス権限を付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12504, '内容）オブジェクト権限情報の更新権限がありません。
対処）この操作を行うには、ログインユーザにシステム管理更新権限が必要です。
      ログインユーザにシステム管理の更新権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12505, '内容）フォルダの削除権限がありません。
対処）この操作を行うには、ログインユーザにフォルダ削除権限が必要です。
      ログインユーザにフォルダの削除権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12600, '内容）フォルダイベントの登録数が上限を超えています。
対処）プロパティファイル（env.properties）のフォルダイベント登録数上限（folderevent.max.count）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12601, '内容）フォルダイベントの実行時にエラーが発生しました。
対処）フォルダイベント履歴及び HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。
      また、フォルダイベントで実行したアプリケーションの履歴をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12602, '内容）フォルダイベントの実行中にタイムアウトが発生しました。
対処）フォルダイベントのタイムアウト値を変更する場合は、システム動作環境設定のイベントタイムアウトの値を更新してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12603, '内容）フォルダイベントのデータが存在しません。
対処）該当データが存在するかをご確認ください。
      また、HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12604, '内容）ターゲットファイルが存在しないため、フォルダイベントを実行することができませんでした。
対処）対象となるフォルダイベントのフォルダ直下に指定したターゲットファイルと同名のファイルが存在するかご確認ください。
      標準で提供するブラウザインタフェースを使用する場合、ターゲットファイルは「イベントで使用するファイル」で指定したファイルです。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12605, '内容）フォルダイベントは未実行です。
対処）フォルダイベントの設定不備によりフォルダイベントを実行しませんでした。
      フォルダイベントの設定内容をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12606, '内容）グループ管理者には HULFT 連携、アプリ連携を含むフォルダイベントの更新権限はありません。
対処）この操作を行うには、ログインユーザにシステム管理の更新権限が必要です。
      ログインユーザにシステム管理の更新権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12607, '内容）アップロード時の転送前フォルダイベントおよびダウンロード時の転送前フォルダイベントは複数登録することができません。
対処）登録済みのフォルダイベント情報を見直してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12608, '内容）前提イベントである HULFT 連携が失敗したため、対象のフォルダイベントは未実行です。
対処）前提イベントのフォルダイベント履歴及び HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12609, '内容）前提イベントであるアプリ連携が失敗したため、対象のフォルダイベントは未実行です。
対処）前提イベントのフォルダイベント履歴及び HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12610, '内容）前提イベントであるメール連携が失敗したため、対象のフォルダイベントは未実行です。
対処）前提イベントのフォルダイベント履歴及び HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 12611, '内容）前提イベントであるフォルダ移動が失敗したため、対象のフォルダイベントは未実行です。
対処）前提イベントのフォルダイベント履歴及び HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13000, '内容）該当の HULFT ID は存在しません。
対処）フォルダイベント情報設定 API の呼び出しが正しく行われているかご確認ください。
      また、HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13001, '内容）HULFT 連携機能の実行に失敗しました。
対処）フォルダイベント履歴及び HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。
      また、プロパティファイル（env.properties）の HULFT 設定をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13002, '内容）HULFT 連携機能の実行時にエラーが発生しました。
対処）フォルダイベント履歴及び HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。
      また、HULFT が正常に動作していることをご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13003, '内容）既にデータが存在するため HULFT 連携機能 ( 集信 ) を実行できませんでした。
対処）ターゲットファイルと同名のファイルがすでに存在しないかご確認ください。
      標準で提供するブラウザインタフェースを使用する場合、ターゲットファイルは「イベントで使用するファイル」で指定したファイルです。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13004, '内容）HULFT 連携機能（集信）で集信したデータが見つかりません。
対処）HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。
      また、HULFT のシステム動作環境設定で「メッセージ動的パラメータ指定」が有効になっていること、HULFT の集信管理情報で「ファイル名」が“$MSG1$MSG2$MSG3$MSG4$MSG5”になっていること、「世代管理」が " しない " になっていることをご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13005, '内容）既に同名のフォルダが登録されています。
対処）ターゲットファイルと同名のフォルダがすでに存在しないかご確認ください。
      標準で提供するブラウザインタフェースを使用する場合、ターゲットファイルは「イベントで使用するファイル」で指定したファイルです。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13006, '内容）集信したファイル名がファイル名規則に一致しません。
対処）集信ファイルが対象フォルダのファイル名規則に一致していることをご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13200, '内容）該当の移動 ID は存在しません。
対処）移動先のフォルダが存在するかご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13201, '内容）移動対象のオブジェクトが存在しません。
対処）移動対象のオブジェクトが存在するかご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13202, '内容）既にデータが存在します。
対処）移動先フォルダに移動対象と同名のファイルがすでに存在しないかご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13203, '内容）既に同名のフォルダが登録されています。
対処）移動先フォルダに移動対象と同名のフォルダがすでに存在しないかご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13204, '内容）移動対象のファイル名が、移動先フォルダのファイル名規則に一致しません。
対処）移動対象のファイル名が移動先フォルダのファイル名規則に一致していることをご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13401, '内容）アクセス受付時間外です。
対処）アクセス受付時間は管理者にお問い合わせください。
      また、アクセス受付時間を変更する場合は、システム動作環境設定のアクセス制限を更新してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13501, '内容）トランザクション処理が失敗したため、ファイル転送を中止します。
対処）トランザクション API の呼び出しが正しく行われているかご確認ください。
      また、HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13502, '内容）該当ファイルはロック中です。
対処）一定時間経過後、再度ファイル転送を実行してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13503, '内容）ファイル転送が異常終了したため、ロールバック処理を行います。
対処）HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。
      また、ネットワーク環境等が正常であることをご確認ください。
      なお、このエラーコードは、ユーザ操作でアップロードまたはダウンロード処理を途中でキャンセルした場合も出力されます。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13504, '内容）トランザクション要求のファイルは存在しません。
対処）指定したファイルが存在するかご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13505, '内容）トランザクションの要求が不正です。
対処）トランザクション API の呼び出しが正しく行われているかご確認ください。
      また、HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13506, '内容）オブジェクト ID にフォルダオブジェクトが指定されています。
      オブジェクト ID にはファイルオブジェクトを指定してください。
対処）オブジェクト ID にはファイルオブジェクトを指定してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13510, '内容）転送前フォルダイベントの実行時にエラーが発生しました。
対処）フォルダイベント履歴及び HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。
      また、フォルダイベントで実行したアプリケーションの履歴をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13511, '内容）トランザクション要求のファイルは存在しません。
      転送前フォルダイベントによってファイルが削除された可能性があります。
対処）転送前フォルダイベントによって対象ファイルが移動または削除されている可能性があります。
      フォルダイベントに設定したコマンドを確認してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13515, '内容）ファイルサイズが不正です。
      指定されたファイルサイズと転送されたファイルサイズが異なります。
対処）データが転送中に欠損した可能性があります。
      OS またはネットワーク環境等を確認し、データを再転送してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13516, '内容）ファイルデータが不正です。
      指定されたファイルのハッシュ値と転送されたファイルのハッシュ値が異なります。
対処）データが転送中に欠損した可能性があります。
      OS またはネットワーク環境等を確認し、データを再転送してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13546, '内容）ファイル転送が未完了です。
対処）該当ファイル転送のトランザクション終了要求が正しく実行されませんでした。
      HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。 
      なお、このエラーコードは、ダウンロード処理が実行中の場合にも出力されます。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13541, '内容）ファイル情報の復号化に失敗しました。
      管理者に連絡してください。
対処）HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13542, '内容）ダウンロード権限がありません。
対処）この操作を行うには、ログインユーザにダウンロード権限が必要です。
      ログインユーザにファイルのダウンロード権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13543, '内容）ファイルのダウンロード回数が有効ではありません。
対処）再度ファイルをアップロードしてください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13544, '内容）ファイルの保存期限切れです。
対処）再度ファイルをアップロードしてください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13545, '内容）DB からファイルを取得できません。
      ファイルサイズが大き過ぎる可能性があります。
対処）ファイルサイズをご確認ください。
      また、HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13547, '内容）ダウンロード対象のファイルが存在しません。
対処）指定内容をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13548, '内容）ダウンロードファイルの保存先フォルダが取得できません。
対処）プロパティファイル（env.properties）の保存先フォルダ（file.storage.path）が存在することをご確認ください。
      また、該当フォルダが AP サーバから書き込み可能であることをご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13549, '内容）保存先フォルダからダウンロードファイルを取得できません。
対処）プロパティファイル（env.properties）の保存先フォルダ（file.storage.path）が存在することをご確認ください。
      また、該当フォルダが AP サーバから書き込み可能であることをご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13601, '内容）フォルダに対してデータ検証は行えません。
対処）保存時のデータ検証を行う場合は、ファイルを指定してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13602, '内容）アップロード時にデータ検証を実行していないので、データ検証を行うことができません。
対処）アップロード時にデータ検証を実行してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13603, '内容）オブジェクト情報の削除権限がありません。
対処）この操作を行うには、ログインユーザにフォルダ削除権限が必要です。
      ログインユーザにフォルダの削除権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13604, '内容）親オブジェクト ID にファイルオブジェクトが指定されています。
      親オブジェクト IDにはフォルダオブジェクトを指定してください。
対処）親オブジェクト ID にはフォルダオブジェクトを指定してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13605, '内容）オブジェクト ID にファイルオブジェクトが指定されています。
      オブジェクト ID にはフォルダオブジェクトを指定してください。
対処）オブジェクト ID にはフォルダオブジェクトを指定してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13606, '内容）フォルダを移動することはできません。
対処）移動対象にはファイルを指定してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13607, '内容）移動先にファイルを指定することはできません。
対処）移動先にはフォルダを指定してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13608, '内容）システム管理削除権限がないため、ルートフォルダを削除できません。
対処）この操作を行うには、ログインユーザにシステム管理削除権限が必要です。
      ログインユーザにシステム管理の削除権限を持つ権限グループを付与してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13609, '内容）既に登録済みのデータです。
対処）別の名前を指定してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13610, '内容）フォルダ内にファイル名規則に一致しないファイルがあるため、登録できません。
対処）フォルダ内のファイル名を確認し、ファイル名規則に一致しないファイルを削除するか、ファイル名規則に一致するようにしてください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13611, '内容）ファイル名がファイル名規則に一致しません。
対処）ファイル名規則に一致する名前を指定してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13612, '内容）イベントで使用するファイル名がファイル名規則に一致しません。
対処）ファイル名規則に一致する名前を指定してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13900, '内容）管理情報の取得に失敗しました。
対処）HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。
      また、HULFT が正常に動作していることをご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13920, '内容）管理情報の登録に失敗しました。
対処）HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。
      また、HULFT が正常に動作していることをご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13940, '内容）管理情報の削除に失敗しました。
対処）HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。
      また、HULFT が正常に動作していることをご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 13960, '内容）履歴情報の取得に失敗しました。
対処）HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。
      また、HULFT が正常に動作していることをご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 14000, '内容）共通イベントが設定されていません。
対処）共通イベントを実行する場合は、共通イベントを設定してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 14001, '内容）共通イベントの実行に失敗しました。
対処）HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 14002, '内容）共通イベントの実行結果がエラーです。
対処）HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 14003, '内容）共通イベントの実行中にタイムアウトが発生しました。
対処）共通イベントのタイムアウト値を変更する場合は、システム動作環境設定のイベントタイムアウトの値を更新してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 14004, '内容）共通イベントに $OPTION1～ 3ではじまる文字列が設定されているため実行できません。
対処）共通イベントの設定をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 14300, '内容）ログインエラーです。証明書が確認できません。
対処）証明書がブラウザにインポートされているか、証明書の有効期限が切れていないかご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 14301, '内容）ログインエラーです。証明書がシステムに登録されていません。
対処）管理者に連絡をしてシステムに証明書を登録してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 14302, '内容）ログインエラーです。指定したユーザのログイン方法が不正です。
対処）管理者に連絡をしてログイン方法を確認してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 14303, '内容）ログインエラーです。証明書に複数のユーザが登録されている場合、クライアント証明書ログインは使用できません。
対処）管理者に連絡をして証明書の登録ユーザを変更するか、ログイン方法を変更してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 14304, '内容）ログインエラーです。指定したユーザは証明書の関連付けユーザとして登録されていません。
対処）管理者に連絡をしてシステムに証明書の関連ユーザを登録してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 14305, '内容）ログインエラーです。証明書に関連付けユーザが登録されていません。
対処）管理者に連絡をしてシステムに証明書の関連ユーザを登録してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 14306, '内容）ログインエラーです。ログイン方法がクライアント証明書の場合は、グループ ID、ユーザ ID、パスワードは設定できません。
対処）クライアント証明書でログインを行う場合は、グループ ID、ユーザ ID、パスワードを未設定で行ってください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 14320, '内容）証明書ファイルが不正のため、システムに登録できません。
対処）アップロードしたファイルが証明書ファイルでない可能性があります。
      証明書ファイルをご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 14321, '内容）同一の証明書ファイル情報（発行者、シリアル番号）は、すでにシステムに登録されています。
対処）システムには同一の証明書ファイルは登録できません。
      アップロードした証明書ファイルをご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 14322, '内容）ログイン方法がクライアント証明書のユーザは、証明書情報に 1 ユーザしか関連付けできません。
対処）証明書に登録するログイン方法がクライアント証明書のユーザは１ユーザにして再度証明書登録を行ってください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 14323, '内容）証明書情報にすでに関連付けされているユーザが存在しています。ログイン方法をクライアント証明書に変更することはできません。
対処）証明書に登録するログイン方法がクライアント証明書のユーザは１ユーザにして再度ユーザ変更を行ってください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 19100, '内容）通信プロトコルが正しくありません。
対処）SSL 通信の設定とリクエストされたプロトコルが一致していません。システム設定の SSL 通信をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 19200, '内容）プロパティファイル (env.properties) の検証処理でエラーが発生しました。
      管理者に連絡してください。
対処）プロパティファイル (env.properties) の検証処理でエラーが発生しました。
      プロパティファイル（env.properties）の値をご確認の上、正しい値を設定してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 19300, '内容）サーバ上のコマンドクライアントがみつかりません。
      管理者に連絡してください。
対処）サーバ上のコマンドクライアントがありません。
      設定ファイル (env.properties) の hccommand.path をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 19301, '内容）サーバ上のコマンドクライアントの正当性が確認できません。
      管理者に連絡してください。
対処）サーバ上のファイルがコマンドクライアントではありません。
      設定ファイル (env.properties) の hccommand.path をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 19990, '内容）システムエラーです。ユーザの所属するグループに割り当てられたシステム動作環境設定がみつかりません。
対処）ユーザの所属するグループに割り当てたシステム動作環境設定が存在するか確認してください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 19999, '内容）システムエラーです。管理者に連絡してください。
対処）HULFT-WebFileTransfer のアプリケーションログ（webft.log）をご確認ください。', NULL, NULL, NULL, 'サーバサイドのエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 20000, '内容）パラメータエラーです。
対処）同一パラメータを重複して指定していないかご確認ください。
      また、不正なパラメータを指定していないかご確認ください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 20001, '内容）パラメータエラーです。
      cmd パラメータの指定が不正です。
対処）cmd パラメータの指定をご確認ください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 20002, '内容）パラメータエラーです。
      config パラメータの指定が不正です。
対処）config パラメータの指定をご確認ください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 20003, '内容）パラメータエラーです。
      パラメータの指定値に「-」（ハイフン）ではじまる値を指定することはできません。
対処）パラメータの指定をご確認ください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21000, '内容）パラメータエラーです。object_id、parent_object_id、および parent_object_pathを組み合わせて指定することはできません。
対処）パラメータの指定をご確認ください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21001, '内容）パラメータエラーです。
      object_id、parent_object_id、または parent_object_pathを省略することはできません。
対処）object_id、parent_object_id、または parent_object_path のいずれかをパラメータに指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21002, '内容）パラメータエラーです。
      parent_object_id に不正な値が含まれています。
対処）parent_object_id をご確認ください。parent_object_id は半角（0-9、a-f、A-F）で指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21003, '内容）パラメータエラーです。
      parent_object_id の長さが不正です。
対処）parent_object_id をご確認ください。
      parent_object_id は 16 バイトで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21004, '内容）パラメータエラーです。
      object_id に不正な値が含まれています。
対処）object_id をご確認ください。
      object_id は半角（0-9、a-f、A-F）で指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21005, '内容）パラメータエラーです。
      object_id の長さが不正です。
対処）object_id をご確認ください。
      object_id は 16 バイトで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21006, '内容）パラメータエラーです。
      registered_file_name に不正な値が含まれています。
対処）registered_file_name パラメータの指定をご確認ください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21007, '内容）パラメータエラーです。
      registered_file_name の長さが不正です。
対処）registered_file_name をご確認ください。
      registered_file_name は最大 255 バイトで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21008, '内容）パラメータエラーです。
      upload_file を省略することはできません。
対処）upload_file をパラメータに指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21009, '内容）パラメータエラーです。
      upload_file に不正な値が含まれています。
対処）upload_file パラメータの指定をご確認ください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21010, '内容）パラメータエラーです。
      upload_file の長さが不正です。
対処）upload_file をご確認ください。upload_file は最大 255 バイトで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21011, '内容）パラメータエラーです。
      local_directory を省略することはできません。
対処）local_directory をパラメータに指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21012, '内容）パラメータエラーです。
      local_directory の長さが不正です。
対処）local_directory をご確認ください。
      local_directory は最大 255 バイトで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21013, '内容）パラメータエラーです。
      local_file_name の長さが不正です。
対処）local_file_name をご確認ください。
      local_file_name は最大 255 バイトで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21014, '内容）パラメータエラーです。
      local_directory と local_file_name を合わせた長さが不正です。
対処）local_directory と local_file_name をご確認ください。
      local_directory と local_file_name を合わせて最大 255 バイトで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21016, '内容）パラメータエラーです。
      object_id を指定した場合、registered_file_name を指定することはできません。
対処）registered_file_name を指定する場合は、parent_object_id、または parent_object_path を指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21017, '内容）パラメータエラーです。
      parent_object_path に不正な値が含まれています。
対処）parent_object_path をご確認ください。
      parent_object_path に含まれるフォルダ名は1 ～ 255 バイトで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21018, '内容）パラメータエラーです。
      parent_object_path の長さが不正です。
対処）parent_object_path をご確認ください。
      parent_object_path は 1024 バイト以内で指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21019, '内容）パラメータエラーです。
      parent_object_id または parent_object_path を指定し、registered_file_name を省略した場合、local_file_name を指定することはできません。
対処）フォルダを指定してダウンロードを行う場合、指定したフォルダ直下のすべてのファイルがダウンロード対象になるため、local_file_name を指定することはできません。
      parent_object_id または parent_object_path を指定し、registered_file_name を省略した場合、フォルダ指定によるダウンロードになります。 
      ファイルを指定してダウンロードを行う場合、registered_file_name を指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21020, '内容）パラメータエラーです。
      option1 の長さが不正です。
対処）option1 をご確認ください。
      option1 は最大 255 バイトで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21021, '内容）パラメータエラーです。
      option2 の長さが不正です。
対処）option2 をご確認ください。
      option2 は最大 255 バイトで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21022, '内容）パラメータエラーです。
      option3 の長さが不正です。
対処）option3 をご確認ください。
      option3 は最大 255 バイトで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21023, '内容）パラメータエラーです。
      update_flag に不正な値が含まれています。
対処）update_flagをご確認ください。
      update_flagは trueまたは falseで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21024, '内容）パラメータエラーです。
      import_file を省略することはできません。
対処）import_file をパラメータに指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21025, '内容）パラメータエラーです。
      import_file の長さが不正です。
対処）import_file をご確認ください。
      import_file は最大 255 バイトで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21026, '内容）パラメータエラーです。
      export_directory を省略することはできません。
対処）export_directory をパラメータに指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21027, '内容）パラメータエラーです。
      export_directory の長さが不正です。
対処）export_directory をご確認ください。
      export_directory は最大 255 バイトで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21028, '内容）パラメータエラーです。
      export_file_name を省略することはできません。
対処）export_file_name をパラメータに指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21029, '内容）パラメータエラーです。
      export_file_name の長さが不正です。
対処）export_file_name をご確認ください。
      export_file_name は最大 255 バイトで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21030, '内容）パラメータエラーです。
      export_directory と export_file_name を合わせた長さが不正です。
対処）export_directory と export_file_name をご確認ください。
      export_directory とexport_file_name を合わせて最大 255 バイトで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21031, '内容）パラメータエラーです。
      group_id に不正な値が含まれています。
対処）group_id をご確認ください。
      group_id は半角で指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21032, '内容）パラメータエラーです。
      group_id の長さが不正です。
対処）group_id をご確認ください。
      group_id は 32 バイト以内で指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21033, '内容）パラメータエラーです。
      auth_id に不正な値が含まれています。
対処）auth_id をご確認ください。
      auth_id は半角で指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21034, '内容）パラメータエラーです。
      auth_id の長さが不正です。
対処）auth_id をご確認ください。
      auth_id は 32 バイト以内で指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21035, '内容）パラメータエラーです。
      date_from に不正な値が含まれています。
対処）date_from をご確認ください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21036, '内容）パラメータエラーです。
      date_to に不正な値が含まれています。
対処）date_to をご確認ください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21037, '内容）パラメータエラーです。
      date_to に date_from より前の日時を指定することをはできません。
対処）date_from、date_to をご確認ください。date_from は date_to 以前の日時を指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21038, '内容）パラメータエラーです。
      object_id、および object_path を組み合わせて指定することはできません。
対処）object_id、または object_path のいずれかを指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21040, '内容）パラメータエラーです。
      object_id、または object_path はフォルダではありません。
対処）object_id、または object_path をご確認ください。
      object_id、または object_pathはフォルダを指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21041, '内容）パラメータエラーです。
      object_path に不正な値が含まれています。
対処）object_path をご確認ください。
      object_path に含まれる各フォルダおよびファイル名は 1 ～ 255 バイトで指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21042, '内容）パラメータエラーです。
      object_path の長さが不正です。
対処）object_path をご確認ください。
      object_path は 1024 バイト以内で指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 21043, '内容）パラメータエラーです。
      parent_object_id、または parent_object_path はフォルダではありません。
対処）parent_object_id、または parent_object_path をご確認ください。
      parent_object_id、または parent_object_path はフォルダを指定してください。', NULL, NULL, NULL, 'パラメータに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30000, '内容）設定ファイルの読み込みに失敗しました。
対処）設定ファイルについて、以下の点をご確認ください。
      ・設定ファイルが存在するか
      ・設定ファイルを他の処理で使用していないか
      ・設定ファイルの文字コードおよびエンコードの指定が UTF-8 になっているか 上記に問題がなかった場合、OS の異常等が考えられますので、環境をご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30001, '内容）設定ファイルのフォーマットが不正です。
対処）設定ファイルが XML 形式であることをご確認ください。
      また、設定ファイルの記述内容をご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30002, '内容）設定ファイルが不正です。
      webft タグが見つかりません。
対処）設定ファイルに webft タグが正しく記述されていることをご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30003, '内容）設定ファイルが不正です。common タグが見つかりません。
対処）設定ファイルに common タグが正しく記述されていることをご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30004, '内容）設定ファイルが不正です。
      info_connection タグが見つかりません。
対処）設定ファイルに info_connection タグが正しく記述されていることをご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30005, '内容）設定ファイルが不正です。
      info_login タグが見つかりません。
対処）設定ファイルに info_login タグが正しく記述されていることをご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30006, '内容）設定ファイルが不正です。
      server_url が未入力です。
対処）設定ファイルに server_url タグが正しく記述されていることをご確認ください。
      また、設定ファイルの server_url に値が指定されていることをご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30007, '内容）設定ファイルが不正です。
      group_id が未入力です。
対処）設定ファイルに group_id タグが正しく記述されていることをご確認ください。
      また、設定ファイルの group_id に値が指定されていることをご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30008, '内容）設定ファイルが不正です。
      user_id が未入力です。
対処）設定ファイルに user_id タグが正しく記述されていることをご確認ください。
      また、設定ファイルの user_id に値が指定されていることをご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30009, '内容）設定ファイルが不正です。
      passwd が未入力です。
対処）設定ファイルに passwd タグが正しく記述されていることをご確認ください。
      また、設定ファイルの passwd に値が指定されていることをご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30010, '内容）設定ファイルが不正です。
      server_url に不正な値が含まれています。
対処）設定ファイルの server_urlをご確認ください。
      server_urlは半角で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30011, '内容）設定ファイルが不正です。
      server_url の長さが不正です。
対処）設定ファイルの server_url をご確認ください。
      server_url は 255 バイト以内で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30012, '内容）設定ファイルが不正です。
      proxy_server および proxy_port のいずれか一方のみを省略することはできません。
対処）設定ファイルの proxy_server および proxy_port をご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30013, '内容）設定ファイルが不正です。
      proxy_connection_id を指定した場合、proxy_server および proxy_port を省略することはできません。
対処）設定ファイルの proxy_server および proxy_port をご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30014, '内容）設定ファイルが不正です。
      proxy_connection_passwd を指定した場合、proxy_connection_id を省略することはできません。
対処）設定ファイルの proxy_connection_id をご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30015, '内容）設定ファイルが不正です。
      proxy_server に不正な値が含まれています。
対処）設定ファイルの proxy_server をご確認ください。
      proxy_server は半角で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30016, '内容）設定ファイルが不正です。
      proxy_server の長さが不正です。
対処）設定ファイルの proxy_server をご確認ください。
      proxy_server は 255 バイト以内で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30017, '内容）設定ファイルが不正です。proxy_port に不正な値が含まれています。
対処）設定ファイルの proxy_port をご確認ください。proxy_port は 1 ～ 65535 で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30018, '内容）設定ファイルが不正です。
      proxy_connection_id に不正な値が含まれています。
対処）設定ファイルの proxy_connection_id をご確認ください。
      proxy_connection_id は半角で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30019, '内容）設定ファイルが不正です。
      proxy_connection_id の長さが不正です。
対処）設定ファイルの proxy_connection_id をご確認ください。
      proxy_connection_id は 255バイト以内で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30020, '内容）設定ファイルが不正です。
      proxy_connection_passwd に不正な値が含まれています。
対処）設定ファイルの proxy_connection_passwd をご確認ください。
      proxy_connection_passwd は半角で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30021, '内容）設定ファイルが不正です。
      proxy_connection_passwd の長さが不正です。
対処）設定ファイルの proxy_connection_passwd をご確認ください。
      proxy_connection_passwd は 255 バイト以内で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30022, '内容）設定ファイルが不正です。
      request_timeout に不正な値が含まれています。
対処）設定ファイルの request_timeout をご確認ください。
      request_timeout は 0 ～ 3600 で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30023, '内容）設定ファイルが不正です。
      connection_retry_count に不正な値が含まれています。
対処）設定ファイルの connection_retry_count をご確認ください。
      connection_retry_countは 0 ～ 10 で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30024, '内容）設定ファイルが不正です。
      connection_retry_interval に不正な値が含まれています。
対処）設定ファイルの connection_retry_interval をご確認ください。
      connection_retry_interval は 0 ～ 3600 で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30025, '内容）設定ファイルが不正です。
      group_id に不正な値が含まれています。
対処）設定ファイルの group_id をご確認ください。
      group_id は半角で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30026, '内容）設定ファイルが不正です。
      group_id の長さが不正です。
対処）設定ファイルの group_id をご確認ください。
      group_id は 32 バイト以内で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30027, '内容）設定ファイルが不正です。
      user_id に不正な値が含まれています。
対処）設定ファイルの user_id をご確認ください。
      user_id は半角で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30028, '内容）設定ファイルが不正です。
      user_id の長さが不正です。
対処）設定ファイルの user_id をご確認ください。
      user_id は 32 バイト以内で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30029, '内容）設定ファイルが不正です。
      passwd に不正な値が含まれています。
対処）設定ファイルの passwd をご確認ください。
      passwd は半角で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30030, '内容）設定ファイルが不正です。
      passwd の長さが不正です。
対処）設定ファイルの passwd をご確認ください。
      passwd は 99 バイト以内で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30031, '内容）設定ファイルが不正です。
      login_type に不正な値が含まれています。
対処）設定ファイルの login_type をご確認ください。
      login_type は 0、1、または 2 で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30032, '内容）設定ファイルが不正です。
      login_type に 1 を指定した場合、group_id、user_id、passwd は設定できません。
対処）設定ファイルの group_id、user_id、passwd をご確認ください。
      login_type に 1 を指定した場合、group_id、user_id、passwd は未設定にしてください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 30033, '内容）設定ファイルが不正です。
      language_type に不正な値が含まれています。
対処）設定ファイルの language_type をご確認ください。
      language_type は 0 または 1 で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31000, '内容）設定ファイルが不正です。
      specific タグが見つかりません。
対処）設定ファイルに specific タグが正しく記述されていることをご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31001, '内容）設定ファイルが不正です。
      output_type に不正な値が含まれています。
対処）設定ファイルの output_type をご確認ください。
      output_type は 0 または 1 で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31002, '内容）設定ファイルが不正です。
      output_header_flag に不正な値が含まれています。
対処）設定ファイルの output_header_flag をご確認ください。
      output_header_flag は trueまたは false で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31003, '内容）設定ファイルが不正です。
      output_item タグが見つかりません。
対処）設定ファイルに output_item タグが正しく記述されていることをご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31004, '内容）設定ファイルが不正です。
      出力項目が未入力です。
対処）設定ファイルの output_item タグ内に出力項目を指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31005, '内容）設定ファイルが不正です。
      date_from に不正な値が含まれています。
対処）設定ファイルの date_from をご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31006, '内容）設定ファイルが不正です。
      date_to に不正な値が含まれています。
対処）設定ファイルの date_to をご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31007, '内容）設定ファイルが不正です。
      object_id に不正な値が含まれています。
対処）設定ファイルの object_id をご確認ください。
      object_id は半角（0-9、a-f、A-F）で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31008, '内容）設定ファイルが不正です。
      object_id の長さが不正です。
対処）設定ファイルの object_id をご確認ください。
      object_id は 16 バイトで指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31009, '内容）設定ファイルが不正です。
      parent_object_id に不正な値が含まれています。
対処）設定ファイルの parent_object_id をご確認ください。
      parent_object_id は半角（0-9、a-f、A-F）で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31010, '内容）設定ファイルが不正です。
      parent_object_id の長さが不正です。
対処）設定ファイルの parent_object_id をご確認ください。
      parent_object_id は 16 バイトで指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31011, '内容）設定ファイルが不正です。
      load_type に不正な値が含まれています。
対処）設定ファイルの load_type をご確認ください。
      load_type は 0、1、または 2 で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31012, '内容）設定ファイルが不正です。
      status に不正な値が含まれています。
対処）設定ファイルの statusをご確認ください。
      statusは 0、1、または 2で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31013, '内容）設定ファイルが不正です。
      folder_event_id に不正な値が含まれています。
対処）設定ファイルの folder_event_id をご確認ください。
      folder_event_id は半角で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31014, '内容）設定ファイルが不正です。
      folder_event_id の長さが不正です。
対処）設定ファイルの folder_event_id をご確認ください。
      folder_event_id は 32 バイト以内で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31015, '内容）設定ファイルが不正です。
      operation_id に不正な値が含まれています。
対処）設定ファイルの operation_id をご確認ください。
      operation_id は半角（0-9、a-f、A-F）で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31016, '内容）設定ファイルが不正です。
      operation_id の長さが不正です。
対処）設定ファイルの operation_id をご確認ください。
      operation_id は 34 バイトで指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31017, '内容）設定ファイルが不正です。
      operation_type に不正な値が含まれています。
対処）設定ファイルの operation_type をご確認ください。
      operation_type は 0 または 1 で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31018, '内容）設定ファイルが不正です。
      operation_name に不正な値が含まれています。
対処）設定ファイルの operation_name をご確認ください。
      operation_name は半角で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31019, '内容）設定ファイルが不正です。
      operation_name の長さが不正です。
対処）設定ファイルの operation_name をご確認ください。
      operation_name は 64 バイト以内で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31020, '内容）設定ファイルが不正です。
      update_flag に不正な値が含まれています。
対処）設定ファイルの update_flag をご確認ください。
      update_flag は true または false で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31021, '内容）設定ファイルが不正です。
      comment の長さが不正です。
対処）設定ファイルの comment をご確認ください。
      comment は 255 バイト以内で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31022, '内容）設定ファイルが不正です。
      date_to に date_from より前の日時を指定することをはできません。
対処）設定ファイルの date_from、date_to をご確認ください。
      date_from は date_to 以前の日時を指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31023, '内容）設定ファイルが不正です。
      object_id、および object_path を組み合わせて指定することはできません。
対処）設定ファイルの object_id、または object_path のいずれかを指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31024, '内容）設定ファイルが不正です。
      object_id、または object_path を省略することはできません。
対処）設定ファイルの object_id、または object_path のいずれかを指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31025, '内容）設定ファイルが不正です。
      object_id、または object_path はフォルダではありません。
対処）設定ファイルの object_id、または object_path をご確認ください。
      object_id、または object_path はフォルダを指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31026, '内容）設定ファイルが不正です。
      import_file を省略することはできません。
対処）設定ファイルに import_file タグが正しく記述されていることをご確認ください。
      また、設定ファイルの import_file に値を指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31027, '内容）設定ファイルが不正です。
      import_file の長さが不正です。
対処）設定ファイルの import_file をご確認ください。
      import_file は最大 255 バイトで指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31028, '内容）設定ファイルが不正です。
      export_directory を省略することはできません。
対処）設定ファイルに export_directory タグが正しく記述されていることをご確認ください。
      また、設定ファイルの export_directory に値を指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31029, '内容）設定ファイルが不正です。
      export_directory の長さが不正です。
対処）設定ファイルの export_directory をご確認ください。
      export_directory は最大 255バイトで指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31030, '内容）設定ファイルが不正です。
      export_file_name を省略することはできません。
対処）設定ファイルに export_file_name タグが正しく記述されていることをご確認ください。
      また、設定ファイルの export_file_name に値を指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31031, '内容）設定ファイルが不正です export_file_name の長さが不正です。
対処）設定ファイルの export_file_name をご確認ください。
      export_file_name は最大 255バイトで指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31032, '内容）設定ファイルが不正です export_directory と export_file_name を合わせた長さが不正です。
対処）設定ファイルの export_directory と export_file_name をご確認ください。
      export_directory と export_file_name を合わせて最大 255 バイトで指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31033, '内容）設定ファイルが不正です。
      auth_id に不正な値が含まれています。
対処）設定ファイルの auth_id をご確認ください。
      auth_id は半角で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 31034, '内容）設定ファイルが不正です。
      auth_id の長さが不正です。
対処）設定ファイルの auth_id をご確認ください。
      auth_id は 32 バイト以内で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 40000, '内容）設定ファイルが不正です。
      data_check_flag に不正な値が含まれています。
対処）設定ファイルの data_check_flag をご確認ください。
      data_check_flag は true または false で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 40001, '内容）設定ファイルが不正です。
      client_compress_flag に不正な値が含まれています。
対処）設定ファイルの client_compress_flag をご確認ください。
      client_compress_flag は true または false で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 40002, '内容）設定ファイルが不正です。
      client_cipher_flag に不正な値が含まれています。
対処）設定ファイルの client_cipher_flag をご確認ください。
      client_cipher_flag は true または false で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 40003, '内容）設定ファイルが不正です。
      client_cipher_type に不正な値が含まれています。
対処）設定ファイルの client_cipher_type をご確認ください。
      client_cipher_type は 0 または 1 で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 40004, '内容）設定ファイルが不正です。
      client_cipher_key に不正な値が含まれています。
対処）設定ファイルの client_cipher_key をご確認ください。
      client_cipher_key は半角で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 40005, '内容）設定ファイルが不正です。
      client_cipher_key の長さが不正です。
対処）設定ファイルの client_cipher_key をご確認ください。
      client_cipher_key は 255 バイト以内で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 40006, '内容）ファイルのハッシュ値の算出に失敗しました。
対処）OS の異常等が考えられますので、環境をご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 40007, '内容）設定ファイルが不正です。
      client_cipher_flag に true を指定した場合は client_cipher_key を省略することはできません。
対処）設定ファイルの client_cipher_key を指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 42000, '内容）設定ファイルが不正です。info_download タグが見つかりません。
対処）設定ファイルの info_download タグが正しく記述されていることをご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 42001, '内容）設定ファイルが不正です。
      local_file_update_flag に不正な値が含まれています。
対処）設定ファイルの local_file_update_flag をご確認ください。
      local_file_update_flagは true または false で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 42002, '内容）設定ファイルが不正です。
      download_retry_count に不正な値が含まれています。
対処）設定ファイルの download_retry_count をご確認ください。
      download_retry_count は 0 ～ 10 で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 42003, '内容）設定ファイルが不正です。
      download_retry_interval に不正な値が含まれています。
対処）設定ファイルの download_retry_interval をご確認ください。
      download_retry_interval は 0 ～ 3600 で指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 42004, '内容）ファイルの書込みに失敗しました。
対処）【ダウンロードコマンドの場合】 以下の点をご確認ください。
      ・local_directory パラメータで指定したフォルダが存在するか
      ・local_directory パラメータで指定したフォルダの出力先ファイルを他の処理で使用していないか 上記に問題がなかった場合、OS の異常等が考えられますので、環境をご確認ください。
       【各エクスポートコマンドの場合】 以下の点をご確認ください。
      ・export_directory パラメータで指定したフォルダが存在するか
      ・export_directory パラメータで指定したフォルダの出力先ファイルを他の処理で使用していないか 上記に問題がなかった場合、OS の異常等が考えられますので、環境をご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 42005, '内容）ファイルの解凍に失敗しました。
対処）解凍するファイルサイズが大きすぎます。
      ダウンロードの設定を見直してください。
      また、OS の異常等が考えられますので、環境をご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 42006, '内容）ファイルの復号化に失敗しました。
対処）Java の暗号強度制限を解除してください。
      また、OS の異常等が考えられますので、環境をご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 42007, '内容）ハッシュ値が一致しません。
対処）データが転送中に欠損した可能性があります。
      OS またはネットワーク環境等を確認し、データを再転送してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 42008, '内容）設定ファイルが不正です。local_file_name を指定した場合は client_compress_flag に false を指定してください。
対処）設定ファイルの client_compress_flag をご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 42009, '内容）保存先のファイルまたはフォルダが既に存在しています。
対処）【ダウンロードコマンドの場合】既存のデータを上書きする場合は、local_file_update_flag を true に指定してください。
      【エクスポートコマンドの場合】既存のデータを上書きする場合は、update_flag を true に指定してください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 42010, '内容）指定したフォルダにファイルが存在しません。
対処）parent_object_id または parent_object_path で指定したフォルダ直下にファイルが存在するかご確認ください。', NULL, NULL, NULL, '設定ファイルに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 60000, '内容）該当する情報は見つかりませんでした。
対処）情報が存在するかご確認ください。
      また、指定した検索条件をご確認ください。', NULL, NULL, NULL, 'コマンドクライアントの共通エラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 80000, '内容）リクエストタイムアウトが発生しました。
対処）サーバが正常に稼働しているか管理者にお問い合わせください。
      また、ネットワークの異常等が考えられますので、環境をご確認ください。', NULL, NULL, NULL, 'ネットワークに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 80001, '内容）ネットワークに接続できませんでした。
対処）OS またはネットワークの異常等が考えられますので、環境をご確認ください。
      また、サーバ側でリクエストを拒否している場合がありますので、リクエストを拒否していないかサーバ管理者にお問い合わせください。', NULL, NULL, NULL, 'ネットワークに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 80002, '内容）ネットワークエラーが発生しました。
対処）OS またはネットワークの異常等が考えられますので、環境をご確認ください。', NULL, NULL, NULL, 'ネットワークに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 80003, '内容）レスポンスの読み込みに失敗しました。
対処）OS またはネットワークの異常等が考えられますので、環境をご確認ください。', NULL, NULL, NULL, 'ネットワークに関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 81000, '内容）ロックファイルのフォルダ作成に失敗しました。
対処）二重起動または OS の異常等が考えられますので、環境をご確認ください。', NULL, NULL, NULL, 'ファイル I/O に関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 81001, '内容）ロックファイルの作成に失敗しました。
対処）二重起動または OS の異常等が考えられますので、環境をご確認ください。', NULL, NULL, NULL, 'ファイル I/O に関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 81002, '内容）ロックファイルのチャネル取得に失敗しました。
対処）二重起動または OS の異常等が考えられますので、環境をご確認ください。', NULL, NULL, NULL, 'ファイル I/O に関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 81003, '内容）ロックファイルのロックに失敗しました。
対処）二重起動または OS の異常等が考えられますので、環境をご確認ください。', NULL, NULL, NULL, 'ファイル I/O に関するエラー', 0, NOW(), 9, NOW(), 9, '1');
INSERT INTO M_Code (CodeId, KeyCode, Note, Class1, Class2, Class3, KeyContent, SystemFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg)
VALUES (204, 99999, '内容）システムエラーが発生しました。
対処）OS の異常等が考えられますので、環境をご確認ください。', NULL, NULL, NULL, 'システムエラー', 0, NOW(), 9, NOW(), 9, '1');
