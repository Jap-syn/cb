-- [001-IL-278]�R�[�h�}�X�^�ւ�INERT��
INSERT INTO M_Code VALUES(72,284 ,'���Ǝғd�b�ԍ�','{Phone}' ,'16' , NULL ,NULL ,1, NOW(), 1, NOW(), 1, 1);


-- [001-IL-272]���֐��Z�߂����u���Ȃ��v�ɍX�V����
UPDATE T_Site
SET    PayingBackFlg = 0    -- �u���Ȃ��v�֍X�V����
;

-- [001-IL-265]�`�[�ԈႢ���[�������Ұ���C��
UPDATE T_MailTemplate SET Body = REPLACE(Body, '{NameKj}', '{CustomerNameKj}') WHERE Class = 15;

