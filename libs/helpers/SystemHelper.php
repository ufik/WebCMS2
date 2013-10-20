<?php

namespace WebCMS;

class SystemHelper {
	
	const VERSION_FILE = '../libs/webcms2/webcms2/AdminModule/version';
	
	private static $baseUrl;
	
	private static $infoEmail;
	
	/**
	 * Helper loader.
	 * @param type $helper
	 * @return type
	 */
	 public static function loader($helper){
        if (method_exists(__CLASS__, $helper)){
            return callback(__CLASS__, $helper);
        }
	 }
	
	 public static function setVariables($variables){
		 foreach($variables as $key => $v){
				 self::$$key = $v;
		 }
	 }
	 
	/**
	 * Gets actual version of the system.
	 * @return Array[revision, date]
	 */
	public static function getVersion(){
		$packages = self::getPackages();
		
		return array_key_exists('webcms2/webcms2', $packages) ? $packages['webcms2/webcms2'] : 'unknown';
	}
	
	public static function getPackages(){
		$handle = @fopen(self::VERSION_FILE, "r");
		$packages = array();
		if ($handle) {
			while (($buffer = fgets($handle, 4096)) !== false) {
				$parsed = explode(' ', preg_replace('!\s+!', ' ', $buffer));
				$vendorPackage = explode('/', $parsed[0]);
				
				$vendor = $vendorPackage[0];
				$package = $vendorPackage[1];
				$version = $parsed[1];
				$versionHash = ($parsed[1] == 'dev-master' ? $parsed[2] : '');
				
				$description = implode(' ', str_replace(array(
					$parsed[0],
					$version,
					$versionHash
				), '', $parsed));
				
				$packages[$parsed[0]] = array(
							'vendor' => $vendor,
							'package' => $package,
							'versionHash' => $versionHash,
							'version' => $version,
							'system' => $vendor == 'webcms2' ? FALSE : TRUE,
							'description' => $description,
							'module' => $vendor == 'webcms2' && $package != 'webcms2' ? TRUE : FALSE,
						);
			}
			
			if (!feof($handle)) {
				// error
			}
			fclose($handle);
		}

		return $packages;
	}
	
	/**
	 * Gets content of the defined file, if not exists its create empty file and returns it.
	 * @param String $filename
	 * @return String
	 */
	private static function getFileContent($filename){
		if (!file_exists($filename)) {
			file_put_contents($filename, '');
		}
		
		return file_get_contents($filename);
	}
	
	/**
	 * 
	 * @param type $user
	 * @param type $permission
	 */
	public static function checkPermissions($user, $permission){
		$roles = $user->getRoles();
		
		if($roles[0] === 'superadmin')
			return true;
		
		$identity = $user->getIdentity();
		
		if(is_object($identity)){
			if(array_key_exists($permission, $identity->data['permissions'])){
				return $identity->data['permissions'][$permission];
			}
		}
		
		return false;
	}
	
	/**
	 * Checks whether user has role superadmin or not.
	 * @param \Nette\Security\User $user
	 * @return Boolean
	 */
	public static function isSuperAdmin(\Nette\Security\User $user){
		$roles = $user->getIdentity()->getRoles();
		
		return in_array('superadmin', $roles);
	}
	
	/**
	 * Returns system resources.
	 * @return Array
	 */
	public static function getResources(){
		return array(
			'admin:Settings' => 'admin:Settings',
			'admin:Users' => 'admin:Users',
			'admin:Languages' => 'admin:Languages',
			'admin:Modules' => 'admin:Modules',
			'admin:Pages' => 'admin:Pages',
			'admin:Filesystem' => 'admin:Filesystem',
			'admin:Update' => 'admin:Update'
		);
	}
	
	public static function strlReplace($search, $replace, $subject){
		$pos = strrpos($subject, $search);

		if($pos !== false)
		{
			$subject = substr_replace($subject, $replace, $pos, strlen($search));
		}

		return $subject;
	}
	
	/**
	 * Recursively removes given directory.
	 * @param string $dir
	 */
	public static function rrmdir($dir) {
		
		if (is_dir($dir)) {
		  $objects = scandir($dir);
		  foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
			  if (filetype($dir."/".$object) == "dir") self::rrmdir($dir."/".$object); else unlink($dir."/".$object);
			}
		  }
		  reset($objects);
		  rmdir($dir);
		}
	 }
	 
	/**
	 * Create relative path from an absolute path.
	 * @param string $path absolute path
	 */
	public static function relative($path){
		
		return str_replace(WWW_DIR, '', $path);
	}
	
	public static function thumbnail($path, $thumbnailKey){
		
		if(strpos($path, "\\") !== FALSE){
			echo 'asd';
			$path = str_replace("/", "\\", $path);
		}
		
		$path = self::relative($path);
		$path = str_replace("upload", 'thumbnails', $path);
		
		$array = explode('/', $path);
		$filename = $array[count($array) - 1];
		
		$path = str_replace($filename, '', $path);
		
		return $path . $thumbnailKey . $filename;
	}
	
	public static function price($price){
		return PriceFormatter::format($price);
	}
	
	/**
	 * 
	 * @param String $string
	 * @return String
	 */
	public static function replaceStatic($string, $fromPush = array(), $toPush = array()){
				
		$from = array(
				"[BASE_URL]",
				"[INFO_EMAIL]"
				);
		
		$to = array(
				self::$baseUrl,
				self::$infoEmail
				);
		
		$from = array_merge($from, $fromPush);
		$to = array_merge($to, $toPush);

		return str_replace($from, $to, $string);
	}	
	
}