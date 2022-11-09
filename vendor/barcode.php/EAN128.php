<?php

	require_once("Common.php");
	require_once("CheckDigit.php");

/**
	 * GS1-128作成クラス
	 */
	class GS1_128 extends EAN128{


	}

	/**
	 * UCC/EAN-128作成クラス
	 */
	class EAN128 {

		/*! 添字(バーコードの下の文字)を描画する・しない */
		var $TextWrite = true;

		/*! 添字(バーコードの下の文字)のフォントファイル名 */
		var $FontName = "./font/mplus-1p-black.ttf";

		/*! 添字のフォントサイズ */
		var $FontSize = 9;

		/*! バー厚み */
		var $BarThick = 1;

		/*! 黒バーの太さ調整ドット数 */
		var $KuroBarCousei = 0;

		// 添え字のコード
		var $outputCode = "";

		/**
		 * pKintou
		 */
		var $Kintou = true;



		/**
		 * バーコードの描画を行います。バーコード全体の幅を指定するのではなく、バーを描画する横方向の最小単位のドット数を指定します。(1～)
		 * @param $code 描画を行うバーコードのコード(テキスト)
		 * @param $minWidthDot 横方向の最少描画ドット数
		 * @param $height バーコードのバーの高さ(単位：ドット)
		 * @return バーコードのイメージを返します。
		 */
	/**
	 * Intermediate method to draw barcode
	 */
	function draw($code, $minWidthDot, $height) {

		global $TextWrite, $FontName, $FontSize;

		$x0 = $minWidthDot;

		$dot = array($x0, $x0);

		// CODE A
		$escString = "\b\t\n\v\f\r";
		$codeA = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefg\b\t\n\v\f\rnopqrstuvwxyz{|}~";

		$codeB = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~";

		$codeC = array("00", "01", "02", "03", "04", "05", "06", "07", "08", "09"
					 , "10", "11", "12", "13", "14", "15", "16", "17", "18", "19"
					 , "20", "21", "22", "23", "24", "25", "26", "27", "28", "29"
					 , "30", "31", "32", "33", "34", "35", "36", "37", "38", "39"
					 , "40", "41", "42", "43", "44", "45", "46", "47", "48", "49"
					 , "50", "51", "52", "53", "54", "55", "56", "57", "58", "59"
					 , "60", "61", "62", "63", "64", "65", "66", "67", "68", "69"
					 , "70", "71", "72", "73", "74", "75", "76", "77", "78", "79"
					 , "80", "81", "82", "83", "84", "85", "86", "87", "88", "89"
					 , "90", "91", "92", "93", "94", "95", "96", "97", "98", "99");

		$ptn = array("212222", "222122", "222221", "121223", "121322", "131222", "122213", "122312", "132212", "221213"
				   , "221312", "231212", "112232", "122132", "122231", "113222", "123122", "123221", "223211", "221132"
				   , "221231", "213212", "223112", "312131", "311222", "321122", "321221", "312212", "322112", "322211"
				   , "212123", "212321", "232121", "111323", "131123", "131321", "112313", "132113", "132311", "211313"
				   , "231113", "231311", "112133", "112331", "132131", "113123", "113321", "133121", "313121", "211331"
				   , "231131", "213113", "213311", "213131", "311123", "311321", "331121", "312113", "312311", "332111"
				   , "314111", "221411", "431111", "111224", "111422", "121124", "121421", "141122", "141221", "112214"
				   , "112412", "122114", "122411", "142112", "142211", "241211", "221114", "413111", "241112", "134111"
				   , "111242", "121142", "121241", "114212", "124112", "124211", "411212", "421112", "421211", "212141"
				   , "214121", "412121", "111143", "111341", "131141", "114113", "114311", "411113", "411311", "113141"
				   , "114131", "311141", "411131");


		$ptnBorC = array("311141", "114131", "113141");
		$ptnStart = array("211412", "211214", "211232");

		$ptnStop = "2331112";

		$swABC;

		$chkNum = "0123456789";
		$cntNum = 0;
		$cntF = 0;
		$cntAI = 0;

		if ($this->CodeABC == CodeSet128::AUTO)
		{
			for($cntNum=0; $cntNum+$cntF+$cntAI < strlen($code) -1; $cntNum++)
			{
				if((strlen($code) >= $cntNum+$cntF+6) && (substr($code, $cntNum+$cntF,6)=="{FNC1}"))
				{
					$cntF += (strlen("{FNC1}") - 1);
						//continue;
				}
				if((strlen($code) >= $cntNum+$cntAI+4) && (substr($code, $cntNum+$cntAI,4)=="{AI}"))
				{
					$cntAI += (strlen("{AI}") - 1);
					//continue;
				}
				if(strpos($chkNum, substr($code, 1+$cntNum+$cntF+$cntAI,1)) === false)
				{
					break;
				}
			}

			if ($cntNum < 3) {
				$swABC = 1;
			} else {
				$swABC = 2;
			}
		}
		else if ($this->CodeABC == CodeSet128::CODE_A)
		{
			$swABC = 0;
		}
		else if ($this->CodeABC == CodeSet128::CODE_B)
		{
			$swABC = 1;
		}
		else //if ($this->CodeABC == CodeSet128::CODE_C)
		{
			$swABC = 2;
		}



		$s = "";

		$gazouHeight = $height;
		if($this->TextWrite == true)
		{
			$gazouHeight = $height + $this->FontSize + 3;
		}


		$xPos = 0;
		for ($j = 0; $j < 6; $j++) {
			for ($l = 0; $l < (int)substr($ptnStart[$swABC], $j, 1); $l++) {
				$xPos += $dot[0];
			}
		}

		// CODE A
		$flgA = false;

		$strAll = "";
		$stsFnc1 = 0;
		$lenAI = 0;
		$svXPos = 0;
		for ($i = 0; $i < strlen($code); $i++) {
			if (strlen($code) >= $i + 6 && substr($code, $i, 6) == "{FNC1}") {
				$stsFnc1 = 1;
			} else if (strlen($code) >= $i + 4 && substr($code, $i, 4) == "{AI}") {
				$stsFnc1 = 3;
			} else if ($stsFnc1 == 1 || $stsFnc1 == 3) {
				$stsFnc1 = 2;
				$lenAI = $this->getLenAI($code, $i);
			} else if ($stsFnc1 == 2) {
				$stsFnc1 = 0;
			}

			$chgFlg = false;
			if ($this->CodeABC == CodeSet128::AUTO)
			{

				if ($swABC == 1) {
					if (isParseAbleInteger($code, $i, $i + 4)) {
						$swABC = 2;
						if ($i >= 7) {
							$chgFlg = true;
						}
					}
				} else {
					try {
						$c = substr($code, $i, 2);
						if(strlen($c) == 1)
						{
							$swABC = 1;
							$chgFlg = true;
						}
						else
						{
							if ($c != "{F" && $c != "{A") {
								for ($j = 0; $j <= 99; $j++) {
									if ($c == $codeC[$j]) {
										break;
									}
								}
								if ($j >= 100) {
									$swABC = 1;
									$chgFlg = true;
								}
							}
						}
					} catch (Exception $ex) {
						$swABC = 1;
						$chgFlg = true;
					}
				}

				if ($chgFlg) {
					//Codeチェンジ
					for ($j = 0; $j < 6; $j++) {
						for ($l = 0; $l < (int)substr($ptnBorC[$swABC], $j, 1); $l++) {
							$xPos += $dot[0];
						}
					}
				}
			}

			$idx;
			if ($stsFnc1 == 1) {
				$idx = 102;
				$i += 5;
			} else if ($stsFnc1 == 3) {
				$i += 3;
				continue;
			}
			else if ($this->CodeABC == CodeSet128::CODE_A)
			{
				$c = substr($code, $i, 1);
				$idx = strpos($codeA, $c);
			}
			else if ($swABC == 1)
			{
				$c = substr($code, $i, 1);

				if ($this->CodeABC == CodeSet128::CODE_B)
				{
					$idx = strpos($codeB, $c);
				}
				else //if ($this->CodeABC == CodeSet128::AUTO)
				{

					// CODE A
					$pos = strpos($escString, $c);
					if ($pos === false)
					{
						$idx = strpos($codeB, $c);
					}
					else
					{
						$idx = strpos($codeA, $c) - 1;
						if ($flgA == false)
						{
							//Codeチェンジ B->A
							$flgA = true;
							for ($j = 0; $j < 6; $j++)
							{
								for ($l = 0; $l < (int)substr($ptnBorC[2], $j, 1); $l++) {
									$xPos += $dot[0];
								}
							}
						}

					}
				}

			} else {
				$c = substr($code, $i, 2);
				if(strlen($c) == 1) $c = $c."0";


				for ($idx = 0; $idx <= 99; $idx++) {
					if ($c == $codeC[$idx]) {
						break;
					}
				}
				$i++;
			}

			for ($j = 0; $j < 6; $j++) {
				for ($l = 0; $l < (int)substr($ptn[$idx], $j, 1); $l++) {
					$xPos += $dot[0];
				}
			}

			if ($this->CodeABC == CodeSet128::AUTO)
			{
				// CODE A
				if ($flgA == true)
				{
					if ($i + 1 < strlen($code))
					{
						$c_next = substr($code, $i + 1, 1);

						// CODE A
						if (strpos($escString, $c_next) === false)
						{
							//Codeチェンジ A->B
							for ($j = 0; $j < 6; $j++)
							{
								for ($l = 0; $l < (int)substr($ptnBorC[$swABC], $j, 1); $l++) {
									$xPos += $dot[0];
								}
							}

							$flgA = false;
						}
					}
				}
			}

		}
		//チェックディジット
		$bIdx = modulus103W1EAN128($code, $this->CodeABC);

		for ($j = 0; $j < 6; $j++) {
			for ($l = 0; $l < (int)substr($ptn[$bIdx], $j, 1); $l++) {
				$xPos += $dot[0];
			}
		}
		//ストップコード
		for ($j = 0; $j < 7; $j++) {
			for ($l = 0; $l < (int)substr($ptnStop, $j, 1); $l++) {
				$xPos += $dot[0];
			}
		}

		$img = ImageCreate($xPos+10, $gazouHeight);
		$white = ImageColorAllocate($img, 0xFF, 0xFF, 0xFF);
		$black = ImageColorAllocate($img, 0x00, 0x00, 0x00);
		$red = ImageColorAllocate($img, 0xFF, 0x00, 0x00);
		$blue = ImageColorAllocate($img, 0x00, 0x00, 0xFF);

		imagesetthickness($img, $this->BarThick);

		if ($this->CodeABC == CodeSet128::AUTO)
		{
			if ($cntNum < 3) {
				$swABC = 1;
			} else {
				$swABC = 2;
			}
		}

		$xPos = 0;
		$s = "";
		//スタートコード
		$s = "START=" . $ptnStart[$swABC] . ", ";
		for ($j = 0; $j < 6; $j++) {
			$l = (int)substr($ptnStart[$swABC], $j, 1);
			if ($j % 2 == 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
			}
			$xPos += $dot[0] * $l;
		}

		// CODE A
		$flgA = false;

		$strAll = "";
		$stsFnc1 = 0;
		$lenAI = 0;
		$svXPos = 0;
		for ($i = 0; $i < strlen($code); $i++) {

			if (strlen($code) >= $i + 6 && substr($code, $i, 6) == "{FNC1}") {
				$stsFnc1 = 1;
			} else if (strlen($code) >= $i + 4 && substr($code, $i, 4) == "{AI}") {
				$stsFnc1 = 3;
			} else if ($stsFnc1 == 1 || $stsFnc1 == 3) {
				$stsFnc1 = 2;
				$lenAI = $this->getLenAI($code, $i);
			} else if ($stsFnc1 == 2) {
				$stsFnc1 = 0;
			}

			$chgFlg = false;
			if ($this->CodeABC == CodeSet128::AUTO)
			{
				if ($swABC == 1) {
					if (isParseAbleInteger($code, $i, $i + 4)) {
						$swABC = 2;
						if ($i >= 7) {
							$chgFlg = true;
						}
					}
				} else {
					try {
						$c = substr($code, $i, 2);
						if(strlen($c) == 1)
						{
							$swABC = 1;
							$chgFlg = true;
						}
						else
						{
							if ($c != "{F" && $c != "{A") {
								for ($j = 0; $j <= 99; $j++) {
									if ($c == $codeC[$j]) {
										break;
									}
								}
								if ($j >= 100) {
									$swABC = 1;
									$chgFlg = true;
								}
							}
						}
					} catch (Exception $ex) {
						$swABC = 1;
						$chgFlg = true;
					}
				}

				if ($chgFlg) {
					if ($swABC == 1) {
						$s = $s . 'CodeChange B, ';
					} else {
						$s = $s . 'CodeChange C, ';
					}

					//Codeチェンジ
					for ($j = 0; $j < 6; $j++) {
						$l = (int)substr($ptnBorC[$swABC], $j, 1);
						if ($j % 2 == 0) { //黒バー
							imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
						}
						$xPos += $dot[0] * $l;
					}
				}
			}

			//添字の描画
			 {
				if ($stsFnc1 == 0 && $lenAI == 0) {
					if(!$this->Kintou) {
						$aaa = substr($code, $i, 1);
						if($this->TextWrite)
							ImageTTFText($img, $this->FontSize, 0, $xPos, $gazouHeight + cmTo(0.01)
						    	,$black, $this->FontName, substr($code, $i, 1));
						//pG.drawString(code.substring(i, i + 1), xPos, pY + pFont.getSize() + pHeight + cm.cmTo(pG, 0.01f));
						$svXPos = $xPos;
					} else {
						$strAll = $strAll . substr($code, $i, 1);
					}
				} else if ($stsFnc1 == 2) {
					if(!$this->Kintou) {
						$aaa = substr($code, $i, $lenAI);
						if($this->TextWrite)
							ImageTTFText($img, $this->FontSize, 0, $xPos - cmTo(0.02), $gazouHeight + cmTo(0.01)
						    		,$black, $this->FontName, "(" . substr($code, $i, $lenAI) . ")");
						//pG.drawString("(" + code.substring(i, i + lenAI) + ")", xPos - cm.cmTo(pG, 0.2f), pY + pFont.getSize() + pHeight + cm.cmTo(pG, 0.01f));
						$svXPos = $xPos;
					} else {
						$strAll = $strAll . "(" . substr($code, $i, $lenAI) . ")";
					}
				}
				if ($lenAI > 0) {
					$lenAI--;
				}
			}

			$idx;
			if ($stsFnc1 == 1) {
				$idx = 102;
				$i += 5;
			} else if ($stsFnc1 == 3) {
				$i += 3;
				continue;
			}
			else if ($this->CodeABC == CodeSet128::CODE_A)
			{
				$c = substr($code, $i, 1);
				$idx = strpos($codeA, $c);
			}
			else if ($swABC == 1)
			{
				$c = substr($code, $i, 1);

				if ($this->CodeABC == CodeSet128::CODE_B)
				{
					$idx = strpos($codeB, $c);
				}
				else //if ($this->CodeABC == CodeSet128::AUTO)
				{


					// CODE A
					$pos = strpos($escString, $c);
					if ($pos === false)
					{
						$idx = strpos($codeB, $c);
					}
					else
					{
						$idx = strpos($codeA, $c) -1;

						if ($flgA == false)
						{
							//Codeチェンジ B->A
							$flgA = true;
							for ($j = 0; $j < 6; $j++) {
								$l = (int)substr($ptnBorC[2], $j, 1);
								if ($j % 2 == 0) { //黒バー
									imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
								}
								$xPos += $dot[0] * $l;
							}
						}
					}
				}

			} else {
				$c = substr($code, $i, 2);
				if(strlen($c) == 1) $c = $c."0";


				for ($idx = 0; $idx <= 99; $idx++) {
					if ($c == $codeC[$idx]) {
						break;
					}
				}
				$i++;
			}

			$s = $s . "Code=" . $idx . ", ";
			for ($j = 0; $j < 6; $j++) {
				$l = (int)substr($ptn[$idx], $j, 1);
				if ($j % 2 == 0) { //黒バー
					imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
				}
				$xPos += $dot[0] * $l;
			}

			if ($this->CodeABC == CodeSet128::AUTO)
			{
				// CODE A
				if ($flgA == true)
				{
					if ($i + 1 < strlen($code))
					{
						$c_next = substr($code, $i + 1, 1);

						// CODE A
						if (strpos($escString, $c_next) === false)
						{
							//Codeチェンジ A->B
							for ($j = 0; $j < 6; $j++) {
								$l = (int)substr($ptnBorC[$swABC], $j, 1);
								if ($j % 2 == 0) { //黒バー
									imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
								}
								$xPos += $dot[0] * $l;
							}

							$flgA = false;
						}
					}
				}
			}

			if ($swABC == 2) {
				//添字の描画
				{
					if ($stsFnc1 == 0 && $lenAI == 0) {
						if(!$this->Kintou) {
							if($this->TextWrite)
								ImageTTFText($img, $this->FontSize, 0, $svXPos + (($xPos - $svXPos) / 2), $gazouHeight + cmTo(0.01)
								,$black, $this->FontName, substr($code, $i, 1));
							//pG.drawString(code.substring(i, i + 1), svXPos + ((xPos - svXPos) / 2), pY + pFont.getSize() + pHeight + cm.cmTo(pG, 0.01f));
						}else{
							$strAll = $strAll . substr($code, $i, 1);
						}
					}
					if ($lenAI > 0) {
						$lenAI--;
					}
				}
			}
		}

		//チェックディジット
		$bIdx = modulus103W1EAN128($code, $this->CodeABC);

		$s = $s . "CD=" . $bIdx . ", ";
		for ($j = 0; $j < 6; $j++) {
			$l = (int)substr($ptn[$bIdx], $j, 1);
			if ($j % 2 == 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
			}
			$xPos += $dot[0] * $l;
		}

		//ストップコード
		$s = $s . "STOP";
		for ($j = 0; $j < 7; $j++) {
			$l = (int)substr($ptnStop, $j, 1);
			if ($j % 2 == 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
			}
			$xPos += $dot[0] * $l;
		}
		if($this->TextWrite && $this->Kintou) {
			$interval = ($xPos - $this->FontSize) / (strlen($strAll) - 1);
			for($i = 0; $i < strlen($strAll); $i++) {
				ImageTTFText($img, $this->FontSize, 0, $i * $interval + cmTo(0.05), $gazouHeight + cmTo(0.01)
					,$black, $this->FontName, substr($strAll, $i, 1));
			}
		}
		$this->outputCode = $strAll;


		//SAMPLE 描画
		//$red = ImageColorAllocate($img, 0xFF, 0x00, 0x00);
		//ImageTTFText($img, 6, 0, 0, 6	,$red, $this->FontName, "SAMPLE");


		return $img;
	}
	/**
	 * @param code Code to be checked
	 * @param idx Index
	 * @return Len AI
	 */
	function getLenAI($code, $idx) {

		// 医療用医薬品で使用されるAI： 01 17又は7003 30 10又は21
		// 医療用機器で使用されるAI： 01 17 10又は21
		// 牛肉のトレーサビリティ(固体識別管理)
		//  - 食肉標準物流バーコードで使用されるAI： 01 3120 11 21
		//  - 食肉標準物流バーコードで使用されるAI： 01 7002 251 240

		if (substr($code, $idx, 2) == "23") {
			return 3;
		} else if (substr($code, $idx, 2) == "24") {
			return 3;
		} else if (substr($code, $idx, 2) == "25") {
			return 3;
		} else if (substr($code, $idx, 2) == "30") {
			return 2;
		} else if (substr($code, $idx, 2) == "37") {
			return 2;
		} else if (substr($code, $idx, 1) == "3") {
			return 4;
		} else if (substr($code, $idx, 2) == "43") {
			return 2;
		} else if (substr($code, $idx, 1) == "4") {
			return 3;
		} else if (substr($code, $idx, 1) == "7") {
			return 4;
		} else if (substr($code, $idx, 1) == "8") {
			return 4;
		} else {
			return 2;
		}
	}

	function drawConvenience($code, $minWidthDot, $height) {
		global $TextWrite, $FontName, $FontSize;


		if(strlen(str_replace("{FNC1}", "", $code)) == strlen("9190808103021001997007091753011008310005000"))
		{
			$code = $code.modulus10W3(str_replace("{FNC1}", "", $code));
		}

		$x0 = $minWidthDot;

		$dot = array($x0, $x0);

		$xPos = 0;

		$codeB = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~";

		$codeC = array("00","01","02","03","04","05","06","07","08","09"
							 ,"10","11","12","13","14","15","16","17","18","19"
							 ,"20","21","22","23","24","25","26","27","28","29"
							 ,"30","31","32","33","34","35","36","37","38","39"
							 ,"40","41","42","43","44","45","46","47","48","49"
							 ,"50","51","52","53","54","55","56","57","58","59"
							 ,"60","61","62","63","64","65","66","67","68","69"
							 ,"70","71","72","73","74","75","76","77","78","79"
							 ,"80","81","82","83","84","85","86","87","88","89"
							 ,"90","91","92","93","94","95","96","97","98","99");

		$ptn = array("212222","222122","222221","121223","121322","131222","122213","122312","132212","221213"
						   ,"221312","231212","112232","122132","122231","113222","123122","123221","223211","221132"
						   ,"221231","213212","223112","312131","311222","321122","321221","312212","322112","322211"
						   ,"212123","212321","232121","111323","131123","131321","112313","132113","132311","211313"
						   ,"231113","231311","112133","112331","132131","113123","113321","133121","313121","211331"
						   ,"231131","213113","213311","213131","311123","311321","331121","312113","312311","332111"
						   ,"314111","221411","431111","111224","111422","121124","121421","141122","141221","112214"
						   ,"112412","122114","122411","142112","142211","241211","221114","413111","241112","134111"
						   ,"111242","121142","121241","114212","124112","124211","411212","421112","421211","212141"
						   ,"214121","412121","111143","111341","131141","114113","114311","411113","411311","113141"
						   ,"114131","311141","411131");

		$ptnBorC = array("311141", "114131", "113141");
		$ptnStart = array("211412", "211214", "211232");
		$ptnStop = "2331112";

		$swABC;

		$chkNum = "0123456789";

		$cntNum = 0;
		$cntAI = 0;
		if ($this->CodeABC == CodeSet128::AUTO)
		{
			for($cntNum=0; $cntNum+$cntF+$cntAI < strlen($code) -1; $cntNum++)
			{
				if((strlen($code) >= $cntNum+$cntF+6) && (substr($code, $cntNum+$cntF,6)=="{FNC1}"))
				{
					$cntF += (strlen("{FNC1}") - 1);
						//continue;
				}
				if((strlen($code) >= $cntNum+$cntAI+4) && (substr($code, $cntNum+$cntAI,4)=="{AI}"))
				{
					$cntAI += (strlen("{AI}") - 1);
					//continue;
				}
				if(strpos($chkNum, substr($code, 1+$cntNum+$cntF+$cntAI,1)) === false)
				{
					break;
				}
			}

			if ($cntNum < 3) {
				$swABC = 1;
			} else {
				$swABC = 2;
			}
		}
		else if ($this->CodeABC == CodeSet128::CODE_A)
		{
			$swABC = 0;
		}
		else if ($this->CodeABC == CodeSet128::CODE_B)
		{
			$swABC = 1;
		}
		else //if ($this->CodeABC == CodeSet128::CODE_C)
		{
			$swABC = 2;
		}

		$gazouHeight = $height;
		if($this->TextWrite == true)
		{
			$gazouHeight = $height + $this->FontSize + 3;
		}


		$s = "";
		//スタートコード
		$s = "START=" . $ptnStart[$swABC] . ", ";
		for($j=0; $j<6; $j++)
		{
			for($l=0; $l<(int)substr($ptnStart[$swABC], $j,1); $l++) {
				$xPos += $dot[0];
			}
		}

		$strAll = "";
		$stsFnc1 = 0;
		$lenAI = 0;
		$svXPos = 0;
		for($i=0; $i<strlen($code); $i++) {

			if (strlen($code) >= $i + 6 && substr($code, $i, 6) == "{FNC1}") {
				$stsFnc1 = 1;
			} else if (strlen($code) >= $i + 4 && substr($code, $i, 4) == "{AI}") {
				$stsFnc1 = 3;
			} else if ($stsFnc1 == 1 || $stsFnc1 == 3) {
				$stsFnc1 = 2;
				$lenAI = $this->getLenAI($code, $i);
			} else if ($stsFnc1 == 2) {
				$stsFnc1 = 0;
			}

			$chgFlg = false;
			if ($this->CodeABC == CodeSet128::AUTO)
			{

				if($swABC == 1)
				{
					try
					{
						$wk = (int)substr($code, $i, 4);
						if ($wk >= 0)
						{
							$swABC = 2;
							if ($i >= 7)
							{
								$chgFlg = true;
							}
						}
					}
					catch(Exception $e)
					{
					}
				}
				else
				{
					try
					{
						$c = substr($code, $i,2);
						if(strlen($c) == 1)
						{
							$swABC = 1;
							$chgFlg = true;
						}
						else
						{
							if ($c != "{F" && $c != "{A")
							{
								for($j=0; $j<=99; $j++)
								{
									if($c == $codeC[$j])
									{
										break;
									}
								}
								if($j >= 100)
								{
									$swABC = 1;
									$chgFlg = true;
								}
							}
						}
					}
					catch(Exception $e)
					{
						$swABC = 1;
						$chgFlg = true;
					}
				}

				if($chgFlg)
				{
					if($swABC == 1)
					{
						$s = $s . "CodeChange B, ";
					}
					else
					{
						$s = $s . "CodeChange C, ";
					}

					//Codeチェンジ
					for($j=0; $j<6; $j++) {
						for($l=0; $l<(int)substr($ptnBorC[$swABC], $j,1); $l++) {
							$xPos += $dot[0];
						}
					}
				}
			}
			$idx;
			if($stsFnc1 == 1){
				$idx = 102;
				$i+=5;
			} else if ($stsFnc1 == 3) {
				$i += 3;
				continue;
			}
			else if($swABC == 1){
				$c = substr($code, $i,1);
				$idx = strpos($codeB, $c);
			} else {
				$c = substr($code, $i,2);
				for($idx=0; $idx<=99; $idx++)
				{
					if($c == $codeC[$idx])
					{
						break;
					}
				}
				$i++;
			}

			$s = $s . "Code=".$idx.", ";
			for($j=0; $j<6; $j++) {
				for($l=0; $l<(int)substr($ptn[$idx], $j,1); $l++) {
					$xPos += $dot[0];
				}
			}
		}
			//チェックディジット
		$bIdx = modulus103W1EAN128($code, $this->CodeABC);

		$s = $s . "CD=".$bIdx.", ";
		for($j=0; $j<6; $j++)
		{
			for($l=0; $l < (int)substr($ptn[$bIdx], $j,1); $l++)
			{
				$xPos += $dot[0];
			}
		}

		//ストップコード
		$s = $s. "STOP";
		for($j=0; $j<7; $j++){
			for($l=0; $l < (int)substr($ptnStop, $j,1); $l++){
				$xPos += $dot[0];
			}
		}

		//$img = ImageCreate($xPos, $gazouHeight + $this->mm2Pixel(1) + $this->point2Pixel($this->FontSize));
		if($this->TextWrite == true)
		{
			$img = ImageCreate($xPos, $gazouHeight+$this->FontSize+10);
		}
		else
		{
			$img = ImageCreate($xPos, $gazouHeight);
		}

		imagesetthickness($img, $this->BarThick);

		$white = ImageColorAllocate($img, 0xFF, 0xFF, 0xFF);
		$black = ImageColorAllocate($img, 0x00, 0x00, 0x00);

		$xPos = 0;

		$s = "";

		//スタートコード
		$s = "START=" . $ptnStart[$swABC] . ", ";
		for($j=0; $j<6; $j++)
		{
			$l = (int)substr($ptnStart[$swABC], $j, 1);
			if ($j % 2 == 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
			}
			$xPos += $dot[0] * $l;
		}

		$strAll = "";
		$stsFnc1 = 0;
		$lenAI = 0;
		$svXPos = 0;
		for($i=0; $i<strlen($code); $i++)
		{

			if (strlen($code) >= $i + 6 && substr($code, $i, 6) == "{FNC1}") {
				$stsFnc1 = 1;
			} else if (strlen($code) >= $i + 4 && substr($code, $i, 4) == "{AI}") {
				$stsFnc1 = 3;
			} else if ($stsFnc1 == 1 || $stsFnc1 == 3) {
				$stsFnc1 = 2;
				$lenAI = $this->getLenAI($code, $i);
			} else if ($stsFnc1 == 2) {
				$stsFnc1 = 0;
			}

			$chgFlg = false;
			if ($this->CodeABC == CodeSet128::AUTO)
			{
				if($swABC == 1)
				{
					try
					{
						$wk = (int)substr($code, $i, 4);
						if ($wk >= 0)
						{
							$swABC = 2;
							if ($i >= 7)
							{
								$chgFlg = true;
							}
						}
					}
					catch(Exception $e)
					{
					}
				}
				else
				{
					try
					{
						$c = substr($code, $i,2);
						if(strlen($c) == 1)
						{
							$swABC = 1;
							$chgFlg = true;
						}
						else
						{
							if ($c != "{F" && $c != "{A")
							{
								for($j=0; $j<=99; $j++)
								{
									if($c == $codeC[$j])
									{
										break;
									}
								}
								if($j >= 100)
								{
									$swABC = 1;
									$chgFlg = true;
								}
							}
						}
					}
					catch(Exception $e)
					{
						$swABC = 1;
						$chgFlg = true;
					}
				}

				if($chgFlg)
				{
					if($swABC == 1)
					{
						$s = $s . "CodeChange B, ";
					}
					else
					{
						$s = $s . "CodeChange C, ";
					}

					//Codeチェンジ
					for($j=0; $j<6; $j++)
					{
						$l = (int)substr($ptnBorC[$swABC], $j, 1);
						if ($j % 2 == 0) { //黒バー
							imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
						}
						$xPos += $dot[0] * $l;
					}
				}
			}

			//添字の描画
			if(true)
			{
				if($stsFnc1 == 0 && $lenAI == 0) {
					$strAll = $strAll . substr($code, $i,1);
				} else if($stsFnc1 == 2) {
					$svXPos = $xPos;
				} if($lenAI > 0) {
					$lenAI--;
				}
			}

			$idx;
			if($stsFnc1 == 1)
			{
				$idx = 102;
				$i+=5;
			} else if ($stsFnc1 == 3) {
				$i += 3;
				continue;
			}
			else if($swABC == 1)
			{
				$c = substr($code, $i,1);
				$idx = strpos($codeB, $c);
			}
			else
			{
				$c = substr($code, $i,2);
				for($idx=0; $idx<=99; $idx++)
				{
					if($c == $codeC[$idx])
					{
						break;
					}
				}
				$i++;
			}

			$s = $s."Code=".$idx.", ";
			for($j=0; $j<6; $j++)
			{
				$l = (int)substr($ptn[$idx], $j, 1);
				if ($j % 2 == 0) { //黒バー
					imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
				}
				$xPos += $dot[0] * $l;
			}

			if($swABC == 2)
			{
				//添字の描画
				if(true)
				{
					if(false)
					{
						//							if(stsFnc1 == 0 && lenAI == 0)
						//								pG.DrawString(code.Substring(i,1), pFont, Brushes.Black, svXPos + ((xPos-svXPos)/2), pY+pHeight+cm.CmTo(pG,0.01f));
					}
					else
					{
						$strAll = $strAll . substr($code, $i,1);
					}
					if($lenAI > 0)
					{
						$lenAI--;
					}
				}
			}
		}

		//チェックディジット
		$bIdx = modulus103W1EAN128($code, $this->CodeABC);

		$s = $s. "CD=".$bIdx.", ";
		for($j=0; $j<6; $j++)
		{
			$l = (int)substr($ptn[$bIdx], $j, 1);
			if ($j % 2 == 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
			}
			$xPos += $dot[0] * $l;
		}

		//ストップコード
		$s = $s."STOP";
		for($j=0; $j<7; $j++)
		{
			$l = (int)substr($ptnStop, $j, 1);
			if ($j % 2 == 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
			}
			$xPos += $dot[0] * $l;
		}

		if($this->TextWrite) {
			$xPos = 0;
			//添え字の描画
			$label = "(" . substr($code, 6, 2) . ")" . substr($code, 8, 6) . "-" . substr($code, 14, 22);
			ImageTTFText($img, $this->FontSize, 0, $xPos, $gazouHeight
				    	,$black, $this->FontName, $label);
			$fontHeight = 18;
			$label = substr($code, 36, 6) . "-" . substr($code, 42, 1) . "-" . substr($code, 43, 6) . "-" . substr($code, 49, 1);
			ImageTTFText($img, $this->FontSize, 0, $xPos, $gazouHeight + $fontHeight
				    	,$black, $this->FontName, $label);
		}

		$this->outputCode = $code;

		//SAMPLE 描画
		//$red = ImageColorAllocate($img, 0xFF, 0x00, 0x00);
		//ImageTTFText($img, 6, 0, 0, 6	,$red, $this->FontName, "SAMPLE");

		return $img;
	}
		/**
		 *
		 * @param mm
		 * @return
		 */
//		function mm2Pixel($mm){
	//		$inch = $mm / 25.4;
		//	return $inch * $this->Dpi;
		//}
		/**
	 *
		 * @param point
		 * @return
		 */
		//function point2Pixel($point) {
			//$inch = $point / 72;
			//return $inch * $this->Dpi;
		//}
	}
