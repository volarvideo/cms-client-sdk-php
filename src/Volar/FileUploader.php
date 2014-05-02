<?php

namespace Volar;

class FileUploader {
	public $volarObject = null;
	function __construct($volarObject)
	{
		$this->volarObject = $volarObject;
	}

	function upload($file_path)
	{
		$res = $this->volarObject->request('api/client/broadcast/s3handshake', 'GET', array('filename' => basename($file_path)));
		if(!$res)
			throw new \Exception($this->volarObject->error);

		try
		{
			$returnVals = array(
				'tmp_file_id' => $res['id'],
				'tmp_file_name' => $res['key']
			);
			$factory_args = array(
				'key' => $res['access_key'],
				'secret' => $res['secret'],
				'token'  => $res['token']
			);
			$put_args = array(
				'ACL' => 'public-read',
				'Key' => $res['key'],
				'Bucket' => $res['bucket'],
				'SourceFile' => $file_path,
				'ContentDisposition' => "attachment; filename=\"".str_replace(array('"'), "", basename($file_path))."\"",
			);
			$s3 = \Aws\S3\S3Client::factory($factory_args);
			$put_res = $s3->putObject($put_args);
			if(!$put_res['ObjectURL'])
			{
				throw new \Exception("Could not upload file");
			}
		}
		catch(\Exception $e)
		{
			throw $e;
		}
		return $returnVals;
	}

}