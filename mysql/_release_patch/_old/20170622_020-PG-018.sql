-- 事業者一覧データCSV項目追加(ラベル修正)
UPDATE M_TemplateField SET LogicalName = '不払い率（件数）一週間'       WHERE TemplateSeq = 45 AND PhysicalName = 'NpRateCount1';
UPDATE M_TemplateField SET LogicalName = '不払い率（件数）一ヶ月'       WHERE TemplateSeq = 45 AND PhysicalName = 'NpRateCount2';
UPDATE M_TemplateField SET LogicalName = '不払い率（件数）三ヶ月'       WHERE TemplateSeq = 45 AND PhysicalName = 'NpRateCount3';
UPDATE M_TemplateField SET LogicalName = '不払い率（件数）六ヶ月'       WHERE TemplateSeq = 45 AND PhysicalName = 'NpRateCount4';
UPDATE M_TemplateField SET LogicalName = '不払い率（件数）一年'         WHERE TemplateSeq = 45 AND PhysicalName = 'NpRateCount5';
UPDATE M_TemplateField SET LogicalName = '不払い率（件数）全体'         WHERE TemplateSeq = 45 AND PhysicalName = 'NpRateCount6';
UPDATE M_TemplateField SET LogicalName = '不払い率（金額）１５日'       WHERE TemplateSeq = 45 AND PhysicalName = 'NpRateMoney1';
UPDATE M_TemplateField SET LogicalName = '不払い率（金額）６０日'       WHERE TemplateSeq = 45 AND PhysicalName = 'NpRateMoney2';
UPDATE M_TemplateField SET LogicalName = '不払い率（金額）１２０日'     WHERE TemplateSeq = 45 AND PhysicalName = 'NpRateMoney3';
UPDATE M_TemplateField SET LogicalName = '不払い率（金額）２１０日'     WHERE TemplateSeq = 45 AND PhysicalName = 'NpRateMoney4';
UPDATE M_TemplateField SET LogicalName = '不払い率（金額）３９０日'     WHERE TemplateSeq = 45 AND PhysicalName = 'NpRateMoney5';
UPDATE M_TemplateField SET LogicalName = '不払い率（金額）全体'         WHERE TemplateSeq = 45 AND PhysicalName = 'NpRateMoney6';
UPDATE M_TemplateField SET LogicalName = '事業者収益（３ケ月）手数料率' WHERE TemplateSeq = 45 AND PhysicalName = 'SiteProfitFeeRate';
UPDATE M_TemplateField SET LogicalName = '事業者収益（３ケ月）収益率'   WHERE TemplateSeq = 45 AND PhysicalName = 'SiteProfitRate';
UPDATE M_TemplateField SET LogicalName = '事業者収益（３ケ月）損益額'   WHERE TemplateSeq = 45 AND PhysicalName = 'SiteProfitAndLoss';

