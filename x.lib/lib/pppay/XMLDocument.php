<?php
/**
 * 解析XML文档
 * 
 */
class XMLDocument {
	private $xmlStr;
	public function __construct($xmlStr) {
		$this->xmlStr = $xmlStr;
	}
	public  function getValueAt($tag) {
		$startTag = "<" . $tag . ">";
		$endTag = "</" . $tag . ">";
		$startIndex = stripos ( $this->xmlStr, $startTag );
		$endIndex = stripos ( $this->xmlStr, $endTag );
		if ($startIndex >= 0 && $endIndex > 0 && $startIndex < $endIndex) {
			$childXml = substr ( $this->xmlStr, $startIndex + strlen ( $startTag ), $endIndex - $startIndex - strlen ( $startTag ) );
			return new XMLDocument ( $childXml );
		}
		return null;
	}
	public function getDetailValueAt($tag, $index) {
		$startTag = "<" . $tag . ">";
		$endTag = "</" . $tag . ">";
		$data = explode ( $endTag . $startTag, $this->xmlStr );
		$count = count ( $data );
		if ($data !== null && $count > 0) {
			$value = $data [$index];
			if ($index == 0)
				$value = str_ireplace ( $startTag, "", $value );
			else if ($index == ($count - 1))
				$value = str_ireplace ( $endTag, "", $value );
			return new XMLDocument ( $value );
		}
		return null;
	}
	
	/**
	 * 明细个数
	 * xmlStr:
	 * <op><a>11</a><b>12</b><c>13</c></op><op><a>21</a><b>22</b><c>23</c></op>
	 *
	 * getCount("op")
	 * 返回 2
	 *
	 * @param
	 *        	$tag
	 * @return
	 *
	 */
	public function getCount($tag) {
		$startCount = substr_count ( $this->xmlStr, "<" . $tag . ">" );
		return $startCount;
	}
	public  function __toString() {
		return $this->xmlStr;
	}
}
?>