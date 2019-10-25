<?php
namespace wcf\util;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;

/**
 * Provides methods for JWT-Token.
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 */
final class JWTToken {
	

	/**
	 * Grace time for not exact server clocks
	 * @var integer
	 */
	public static $graceTime = 60;
	
	/**
	 * Supported Algorithms
	 * 
	 * @var array
	 */
	public static $algorithmns = array(
			'HS256' => array('hash_hmac', 'SHA256'),
			'HS512' => array('hash_hmac', 'SHA512'),
			'HS384' => array('hash_hmac', 'SHA384'),
	);
	
	/**
	 * Converts and signs a PHP object or array into a JWT string.
	 *
	 * @param object|array  $payload    PHP object or array
	 * @param string        $key        The secret key.
	 *                                  If the algorithm used is asymmetric, this is the private key
	 * @param string        $alg        The signing algorithm.
	 *                                  Supported algorithms are 'HS256', 'HS384', 'HS512'
	 *
	 * @return string A signed JWT
	 *
	 */
	public static function encode($payload, $key, $alg='HS256')
	{
		$header = array('typ' => 'JWT', 'alg' => $alg);
		
		$segments = array();
		$segments[] = static::base64encodeUrlsafe(JSON::encode($header));
		$segments[] = static::base64encodeUrlsafe(JSON::encode($payload));
		$signing_input = implode('.', $segments);
		
		$signature = static::sign($signing_input, $key, $alg);
		$segments[] = static::base64encodeUrlsafe($signature);
		
		return implode('.', $segments);
	}
	
	
	/**
	 * Decodes a JWT string and returns header and body
	 * 
	 * @param string $payload
	 * @throws SystemException
	 * @return mixed[] array with header and body
	 */
	public static function decode($payload){
		$tokenParts = explode('.', $payload);
		if (count($tokenParts) != 3) {
			throw new SystemException('Wrong number of segments');
		}
		list($header, $body, $crypto) = $tokenParts;
		
		return array('header' => json_decode(static::base64decodeUrlsafe($header)), 'body' => json_decode(static::base64decodeUrlsafe($body)));
	}
	
	
	/**
	 * Verifies a JWT string and returns true if the signature matches
	 * 
	 * @param string $payload
	 * @param string $key
	 * @param string $alg
	 * @throws SystemException
	 * @return boolean
	 */
	public static function verify($payload, $key, $alg='HS256'){
		$tokenParts = explode('.', $payload);
		if (count($tokenParts) != 3) {
			throw new SystemException('Wrong number of segments');
		}
		list($header, $body, $crypto) = $tokenParts;
		
		$signature = static::sign($header.'.'.$body, $key, $alg);
		
		//Check signature
		if (!hash_equals($signature, static::base64decodeUrlsafe($crypto))) return false;
		
		$arrBodyParts = json_decode(static::base64decodeUrlsafe($body));
		if (!$arrBodyParts) return false;
		
		//Check iss
		if (!isset($arrBodyParts->iss) || $arrBodyParts->iss !== LinkHandler::getInstance()->getLink()) return false;
		
		//Check iat
		if (isset($arrBodyParts->iat) && $arrBodyParts->iat > (TIME_NOW + static::$graceTime)) return false;
		
		//Check nbf
		if (isset($arrBodyParts->nbf) && $arrBodyParts->nbf > (TIME_NOW + static::$graceTime)) return false;
		
		//Check exp		
		if (isset($arrBodyParts->exp) && (TIME_NOW - static::$graceTime) >= $arrBodyParts->exp) return false;
		
		return true;
	}
	
	
	/**
	 * Sign a string with a given key and algorithm.
	 *
	 * @param string            $msg    The message to sign
	 * @param string|resource   $key    The secret key
	 * @param string            $alg    The signing algorithm.
	 *                                  Supported algorithms are 'HS256', 'HS384', 'HS512'
	 *
	 * @return string An encrypted message
	 *
	 * @throws SystemException Unsupported algorithm was specified
	 */
	public static function sign($msg, $key, $alg = 'HS256')
	{
		if (empty(static::$algorithmns[$alg])) {
			throw new SystemException('Algorithm not supported');
		}
		list($function, $algorithm) = static::$algorithmns[$alg];
		
		return hash_hmac($algorithm, $msg, $key, true);
	}
	
	
	/**
	 * Encode a string with URL-safe Base64.
	 *
	 * @param string $input The string you want encoded
	 * @return string The base64 encode of what you passed in
	 */
	public static function base64encodeUrlsafe($input)
	{
		return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
	}
	
	
	/**
	 * Decode a string with URL-safe Base64.
	 * 
	 * @param string $input The string you want decoded
	 * @return string The base64 decode of what you passed in
	 */
	public static function base64decodeUrlsafe($input)
	{
		return base64_decode(str_replace(array('-', '_'), array('+', '/'), $input));
	}
	
}