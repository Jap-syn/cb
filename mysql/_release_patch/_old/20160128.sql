UPDATE AT_ReceiptControl SET Rct_CancelFlg = 1 WHERE ReceiptSeq IN (SELECT ReceiptSeq FROM T_ReceiptControl WHERE ReceiptAmount < 0);
