<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 29.11.17
 * Time: 22:49
 */

namespace AppBundle\Service;


class UploadService
{
	/** @var string */
	private $uploadDir;

	/** @var string */
	private $webDir;


	public function __construct($uploadDir, $webDir)
	{
		$this->uploadDir = $uploadDir;
		$this->webDir = $webDir;
	}

	/**
	 * @return string
	 */
	public function getUploadDir()
	{
		return $this->uploadDir;
	}

	/**
	 * @return string
	 */
	public function getWebDir()
	{
		return $this->webDir;
	}

	/**
	 * @return string
	 */
	public function getFolder()
	{
		return date("Y-m-d");
	}

	/**
	 * @return string
	 */
	public function getUniqueFilename()
	{
		$chars = explode(",", "a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,0,9,8,7,6,5,4,3,2,1");
		$return = "";

		foreach (range(1, 32) as $i) {
			shuffle($chars);
			$return .= $chars[0];
		}

		return $return;
	}

	/**
	 * @param string $extension
	 * @param string|null $name
	 * @return string
	 */
	public function createFile($extension, $name = null)
	{
		$folder = $this->getFolder();
		@mkdir($this->getWebDir() . "/uploads/" . $folder, 0777);
		chmod($this->getWebDir() . "/uploads/" . $folder, 0777);
		if ($name === null) {
			$name = $this->getUniqueFilename();
		}
		$name .= "." . $extension;

		# TODO: /uploads/ tahat z parameters.yml - uz to tam je
		return "/uploads/" . $folder . "/" . $name;
	}
}
