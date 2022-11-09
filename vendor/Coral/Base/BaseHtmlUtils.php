<?php
namespace Coral\Base;

class BaseHtmlUtils
{
	/**
	 * SELECT タグセット作成
	 *
	 * @param string $tagName タグ名
	 * @param array $items オプションアイテムのアレイ
	 * @param mixed $selectedValue セレクト初期値
	 * @param string $tagOption タグオプション
	 */
	public static function SelectTag($tagName, $items, $selectedValue = null, $tagOption = null)
	{
		$html = sprintf('<SELECT name="%s" id="%s" %s>', f_e($tagName), f_e($tagName), $tagOption);

		foreach($items as $value => $label)
		{
			$selected = "";
			if ($selectedValue != null && $value == $selectedValue)
			{
				$selected = "selected";
			}

			$html .= sprintf('<option value="%s" %s>%s</option>', f_e($value), $selected, f_e($label));
		}

		$html .= '</SELECT>';

		return $html;
	}

	/**
	 * INPUT RADIO タグセット作成
	 *
	 * @param string $tagName タグ名
	 * @param array $items オプションアイテムのアレイ
	 * @param mixed $checkedValue チェック初期値
	 */
	public static function InputRadioTag($tagName, $items, $checkedValue = null, $isVertical = false, $isDisabled = false, $source = null)
	{
		$html = '<span class="radio_set">';
		$id = 1;

		if ($isDisabled)
		{
			$disabled = "disabled";
		}
		else
		{
			$disabled = "";
		}

		foreach($items as $value => $label)
		{
			$checked = "";
			if ($checkedValue != null && $value == $checkedValue)
			{
				$checked = "checked";
			}

			if ($isVertical && $id > 1)
			{
				$html .= "<br />";
			}

			// ラベル部分をクリックしても選択できるようlabel要素で囲うように変更（08.03.26）
			//$html .= sprintf('<input name="%s" id="%s%d" type="radio" value="%s" %s />%s　', $tagName, $tagName, $id, $value, $checked, $label);

			//社内与信のNG表記を赤くするように修正
			if("NG" == $label && "Rwcredit" == $source) {
				$html .= "<label for=\"{$tagName}{$id}\"><input name=\"$tagName\" id=\"{$tagName}{$id}\" type=\"radio\" value=\"$value\" $checked $disabled /> <font color=\"#ff0000\">{$label}</font>　</label>";
			} else {
				$html .= "<label for=\"{$tagName}{$id}\"><input name=\"$tagName\" id=\"{$tagName}{$id}\" type=\"radio\" value=\"$value\" $checked $disabled />{$label}　</label>";
			}
			$id++;
		}

		$html .= "</span>";

		return $html;
	}

	/**
	 * INPUT CHECKBOX タグセット作成
	 *
	 * @param $tagNamePrefix 各チェックボックスのタグ名接頭文字列
	 * @param $items オプションアイテムのアレイ
	 * @param $checkedValues チェック初期値の配列
	 * @param $isVertical 5アイテムごとに改行するか否か
	 */
	public static function InputCheckBoxTag($tagNamePrefix, $items, $checkedValues = null, $isVertical = true)
	{
		$id = 1;
		$cr = 0;

		foreach($items as $value => $label)
		{
			$checked = "";
			if ($checkedValues != null)
			{
				foreach($checkedValues as $cvalue)
				{
					if ($cvalue == $value)
					{
						$checked = "checked";
					}
				}
			}

			if ($isVertical && $cr >= 5)
			{
				$html .= "<br />";
				$cr = 0;
			}

			$tn = $tagNamePrefix . '_' . $value;
			$html .= sprintf('<input name="%s" id="%s" type="checkbox" %s />%s　', $tn, $tn, $checked, $label);
			$id++;
			$cr++;
		}

		return $html;
	}
}
