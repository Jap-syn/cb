<?php 

//	require("Common.php");
	require_once("CheckDigit.php");

	/**
	 * Jan8作成クラス
	 */
	class Jan8 {

		/*! 添字(バーコードの下の文字)を描画する・しない */
		var $TextWrite = true;

		/*! 添字(バーコードの下の文字)のフォントファイル名 */
		var $FontName = "./font/mplus-1p-black.ttf";

		/*! 添字のフォントサイズ */
		var $FontSize = 10;

		/*! バー厚み */
		var $BarThick = 1;

		/*! 黒バーの太さ調整ドット数 */
		var $KuroBarCousei = 0;
	
	/**
		 * バーコードの描画を行います。バーコード全体の幅を指定するのではなく、バーを描画する横方向の最小単位のドット数を指定します。(1～)
		 * @param $code 描画を行うバーコードのコード(テキスト)
		 * @param $minWidthDot 横方向の最少描画ドット数
		 * @param $height バーコードのバーの高さ(単位：ドット)
		 * @return バーコードのイメージを返します。
		 */
		function draw($pCode, $minWidthDot, $height) {

		global $TextWrite, $FontName, $FontSize;
		
		
		if(preg_match ("/^[0-9]+$/",$pCode) == false)
		{
			trigger_error("JAN8 : not number in code string.",E_USER_ERROR);
			exit;
		}

		if (strlen($pCode) == 8) {
			if (substr($pCode,7, 1) != modulus10W3(substr($pCode,0, 7))) {
				trigger_error("JAN7 : check digit is wrong. ",E_USER_ERROR);
				exit;
			}
			$code = $pCode;
		} else if (strlen($pCode) == 7) {
			$code = $pCode . modulus10W3(substr($pCode, 0, 7));
		} else {
			trigger_error("JAN8 : length error. (length != 8 or lenght != 7)",E_USER_ERROR);
			exit;
		}

		$codeL = substr($code, 0, 4);
		$codeR = substr($code, 4, 4);

		$x0 = $minWidthDot; //x0:細バー x1:太バー
		$x1 = $minWidthDot * 2.5;
		if($minWidthDot % 2 != 0) {
			$x1 = $minWidthDot * 3;
		}

		$dot = array($x0, $x1);

		$xPos = 0;
		$xPos += $dot[0]*(3+28+5+28+3);

		$gazouHeight = $height;
		if($this->TextWrite == true)
		{
			$gazouHeight = $height + $this->FontSize + 3;
		}
		$img = ImageCreate($xPos, $gazouHeight);
		$white = ImageColorAllocate($img, 0xFF, 0xFF, 0xFF);
		$black = ImageColorAllocate($img, 0x00, 0x00, 0x00);

		imagesetthickness($img, $this->BarThick);

		$left  = array(0x0d, 0x19, 0x13, 0x3d, 0x23, 0x31, 0x2f, 0x3b, 0x37, 0x0b);
		$right = array(0x72, 0x66, 0x6c, 0x42, 0x5c, 0x4e, 0x50, 0x44, 0x48, 0x74);

		$xPos = 0;
		//スタートコード
		for ($j = 0; $j < 3; $j++) {
			if ($j % 2 == 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+$dot[0]-1 + $this->KuroBarCousei,  $height, $black);
			}
			$xPos += $dot[0];
		}

		//Left 4キャラクタ
		for ($i = 0; $i < strlen($codeL); $i++) {
//		//添字の描画
//			if($this->TextWrite) {
//				ImageTTFText($img, $this->FontSize, 0, $xPos, $gazouHeight
//			    	,$black, $this->FontName, substr($codeL, $i, 1));
//			}
			$c = substr($codeL, $i, 1);
			$chk = 0x40;
			$l = 0;
			for ($j = 0; $j < 7; $j++) {
				if (($left[$c] & ($chk >> $j)) != 0) {
					$l++;
				}elseif($l != 0){
					imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0]*$l)-1 + $this->KuroBarCousei,  $height, $black);
					$xPos += $dot[0]*$l + $dot[0];
					$l = 0;
				}else{
					$xPos += $dot[0];
				}
			}
			if($l != 0){
				imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0]*$l)-1 + $this->KuroBarCousei,  $height, $black);
				$xPos += $dot[0]*$l;
			}
		}

		//センターコード
		for ($j = 0; $j < 5; $j++) {
			if ($j % 2 != 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+$dot[0]-1 + $this->KuroBarCousei,  $height, $black);
			}
			$xPos += $dot[0];
		}
		
		//Right 4キャラクタ
		for ($i = 0; $i < strlen($codeR); $i++) {
//			//添字の描画
//			if($this->TextWrite) {
//				ImageTTFText($img, $this->FontSize, 0, $xPos, $gazouHeight
//			    	,$black, $this->FontName, substr($codeR, $i, 1));
//			}
			$c = substr($codeR, $i, 1);
			$chk = 0x40;
			$l = 0;
			for ($j = 0; $j < 7; $j++) {
				if (($right[$c] & ($chk >> $j)) != 0) {
					$l++;
				}elseif($l != 0){
					imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0]*$l)-1 + $this->KuroBarCousei,  $height, $black);
					$xPos += $dot[0]*$l + $dot[0];
					$l = 0;
				}else{
					$xPos += $dot[0];
				}
			}
			if($l != 0){
				imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0]*$l)-1 + $this->KuroBarCousei,  $height, $black);
				$xPos += $dot[0]*$l;
			}
		}

		//エンドコード
		for ($j = 0; $j < 3; $j++) {
			$k = 0;
			if ($j % 2 == 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+$dot[0]-1 + $this->KuroBarCousei,  $height, $black);
			}
			$xPos += $dot[0];
		}

		if($this->TextWrite) {
			$interval = ($xPos - pointTo($this->FontSize - cmTo(0.01))) / (strlen($code) - 1)-1;
			for ($i = 0; $i < strlen($code); $i++) {
				ImageTTFText($img, $this->FontSize, 0, $pX + $i * $interval + cmTo(0.05), $gazouHeight + cmTo(0.01)
					,$black, $this->FontName, substr($code, $i, 1));
			}
		}
		
		//SAMPLE 描画
		//$red = ImageColorAllocate($img, 0xFF, 0x00, 0x00);
		//ImageTTFText($img, 6, 0, 0, 6	,$red, $this->FontName, "SAMPLE");


		return $img;
		
		}
	}

	