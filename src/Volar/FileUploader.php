<?php

namespace Volar;

class FileUploader {
	public $volarObject = null;
	function __construct($volarObject)
	{
		$this->volarObject = $volarObject;
	}

	function upload($file_name)
	{
		$res = $this->volarObject->request('api/client/broadcast/s3handshake', 'GET', array('filename' => basename($file_path)));
		if(!$res)
			throw new Exception($this->volarObject->error);

		$returnVals = array(
			'tmp_file_id' => $res['id'],
			'tmp_file_name' => $res['path']
		);
		try
		{
			$aws = Aws\Common\Aws::factory(array());
			$s3 = $aws->get('s3');
			$req = $s3->put($res['signed_url'], null, fopen($file_path, 'r'));
			$response = $req->send();
			if($response->isError())
			{
				throw new Exception("Error when attempting to upload $file_path: \n".$response->json());
			}
		}
		catch(Exception $e)
		{
			throw $e;
		}
		return $returnVals;
	}
}