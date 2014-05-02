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
			throw new Exception($this->volarObject->error);

		$returnVals = array(
			'tmp_file_id' => $res['id'],
			'tmp_file_name' => $res['path']
		);
		try
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $res['signed_url']);
			curl_setopt($ch, CURLOPT_PUT, 1);
			$fh = fopen($file_path, 'r');
			curl_setopt($ch, CURLOPT_INFILE, $fh);
			curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file_path));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if(!$res = curl_exec($ch))
			{
				curl_close($ch);
				fclose($fh);
				throw new Exception("cURL error: ($url) ".$error);
			}
			curl_close($ch);
			fclose($fh);
			if(class_exists('DOMDocument'))
			{
				$err_string = '';
				$doc = new DOMDocument('1.0');
				$doc->loadXML($xml);
				$errors = $doc->getElementsByTagName('Error');
				if(count($errors) > 0)
				{
					foreach($errors as $err)
					{
						$code = $err->getElementsByTagName('Code');
						$message = $err->getElementsByTagName('Message');
						if($code->length)
							$error_string .= $code->item(0)->textContent.': ';
						if($message->length)
							$error_string .= $message->item(0)->textContent;
					}
				}
				if($err_string)
					throw new Exception($err_string);
			}
			echo htmlspecialchars($res);

			// $aws = \Aws\Common\Aws::factory(array('key' => 'fake', 'secret' => 'fake'));
			// $s3 = $aws->get('s3');
			// $req = $s3->put($res['signed_url'], null, fopen($file_path, 'r'));
			// $response = $req->send();
			// if($response->isError())
			// {
			// 	throw new Exception("Error when attempting to upload $file_path: \n".$response->json());
			// }
		}
		catch(Exception $e)
		{
			throw $e;
		}
		return $returnVals;
	}

}