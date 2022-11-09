/* メールテンプレート削除 */
DELETE FROM T_MailTemplate WHERE Class IN (13, 14) AND OemId IN (1, 3, 4);