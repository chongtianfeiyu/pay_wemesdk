<?php
	require_once 'Crypt3Des.php'; // 3DES Counter Mode implementation
?>
<?php

// namespace PPSec;
class PPSecurity {
	public static function hex2bin($data) {
		$len = strlen ( $data );
		return pack ( "H" . $len, $data );
	}
	public static function encData($data, $randKey, $EncMode, $HashMode, $ZipMode) {
		$key = base64_decode ( $randKey );
		if (0 == strcmp ( $EncMode, "1" ) || 0 == strcmp ( $EncMode, "DESede" )) {
			$crypt = new Crypt3Des ( $key );
			$encrypted_data = $crypt->encrypt ( $data );
		} else
			return "";
			
			// echo $encrypted_data."<br/>";
		
		$hash = "";
		if (0 == strcmp ( $HashMode, "1" ) || 0 == strcmp ( $HashMode, "md5" ) || 0 == strcmp ( $HashMode, "MD5" ))
			$hash = base64_encode ( self::hex2bin ( md5 ( $data ) ) );
		else if (0 == strcmp ( $HashMode, "2" ) || 0 == strcmp ( $HashMode, "sha1" ) || 0 == strcmp ( $HashMode, "SHA1" ))
			$hash = base64_encode ( self::hex2bin ( sha1 ( $data ) ) );
		else
			return "";
			
			// echo $hash."<br/>";
		
		return $encrypted_data . ";" . $hash;
	}
	public static function decData($data, $randKey, $EncMode, $HashMode, $ZipMode) {
		$key = base64_decode ( $randKey );
		// get enced data from str;
		$data_fix = strtok ( $data, ";" );
		$data_hash = strtok ( ";" );
		
		//echo "data_fix:".$data_fix."</br>";
		
		//echo "data_hash:".$data_hash."</br>";
		
		if ($data_fix == false || $data_hash == false)
			return "";
		
		$data_fix = str_replace ( " ", "+", $data_fix );
		if (0 == strcmp ( $EncMode, "1" ) || 0 == strcmp ( $EncMode, "DESede" )) {
			$crypt = new Crypt3Des ( $key );
			$decrypted_data = $crypt->decrypt ( $data_fix );
		} else
			return "";
			
		//echo "decrypted_data:".$decrypted_data."</br>";
		// 计算摘要
		$hash = "";
		if (0 == strcmp ( $HashMode, "1" ) || 0 == strcmp ( $HashMode, "md5" ) || 0 == strcmp ( $HashMode, "MD5" ))
			$hash = base64_encode ( self::hex2bin ( md5 ( $decrypted_data ) ) );
		else if (0 == strcmp ( $HashMode, "2" ) || 0 == strcmp ( $HashMode, "sha1" ) || 0 == strcmp ( $HashMode, "SHA1" ))
			$hash = base64_encode ( self::hex2bin ( sha1 ( $decrypted_data ) ) );
		else
			return "";
		//echo "hash:".$hash."</br>";
		
		if (0 == strcmp ( $data_hash, $hash ))
			return $decrypted_data;
		else
			return "";
	}
}

?>