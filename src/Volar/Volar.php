<?php
/**
 * This requires the AWS SDK.  You can install it by downloading the volar SDK following the instructions in the README.md file
 */

namespace Volar;


class Volar {
	/**
	 *	api key provided by the Volar Video system for your account
	 *	@var string $api_key
	 */
	public $api_key = null;

	/**
	 *	secret key provided by the Volar Video system for your account
	 *	@var string $secret
	 */
	public $secret = null;

	/**
	 *	domain name that the api needs to point to.  Typically vcloud.volarvideo.com, which is the default
	 *	@var string $base_url
	 */
	public $base_url = null;

	/**
	 *	set to true if you wish to use https to communicate with the api.
	 *	@var bool $secure
	 */
	public $secure = false;

	/**
	 *	will contain debug information about the last communication made to the api.
	 *	@var string $debug
	 */
	public $debug = null;

	/**
	 *	@internal
	 *	error container
	 *	@var string $error
	 */
	private $error = null;

	/**
	 *	Constructor for the volar video sdk
	 *	@example construct object
	 *		$v = new Volar($api_key, $secret, $base_url);
	 *	@param string $api_key api key provided by the Volar Video system for your account
	 *	@param string $secret secret key provided by the Volar Video system for your account
	 *	@param string $base_url domain name that the api needs to point to.  Typically vcloud.volarvideo.com, which is the default
	 */

	public function __construct($api_key = '', $secret = '', $base_url = 'vcloud.volarvideo.com')
	{
		$this->api_key = $api_key;
		$this->secret = $secret;
		$this->base_url = $base_url;
	}

	/**
	 *	gets last error
	 *	@return string last error, or null if no errors
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 *	gets list of sites
	 *	@param array $params associative array
	 *		recognized parameters in array:
	 *			- optional -
	 *			'page'				current page of listings.  pages begin at '1'
	 *			'per_page'			number of broadcasts to display per page
	 *			'id'				id of site - useful if you only want to get details of a single site
	 *			'slug'				slug of site.  useful for searches, as this accepts incomplete titles and returns all matches.
	 *			'title'				title of site.  useful for searches, as this accepts incomplete titles and returns all matches.
	 *			'sort_by'			data field to use to sort.  allowed fields are status, id, title, description. defaults to title
	 *			'sort_dir'			direction of sort.  allowed values are 'asc' (ascending) and 'desc' (descending). defaults to asc
	 *	@return false on failure, array on success.  if failed, $volar->getError() can be used to get last error string
	 */
	public function sites($params = array())
	{
		return $this->request('api/client/info', 'GET', $params);
	}

	/**
	 *	@category Broadcasts
	 */

	/**
	 *	gets list of broadcasts
	 *	@example this example pulls page 1 of broadcasts from site 'mysite' in the 'scheduled' state
	 *		if($result = $v->broadcasts(array(
	 *				'site' => 'mysite',
	 *				'list' => 'scheduled',
	 *				'page' => 1
	 *			)))
	 *		{
	 *			var_dump($result['broadcasts']);
	 *		}
	 *	@param array $params associative array
	 *			recognized parameters in array:
	 *				- required -
	 *				'site'				slug of site to filter to.
	 *				- optional -
	 *				'list'				type of list.  allowed values are 'all', 'archived', 'scheduled' or 'upcoming', 'upcoming_or_streaming', 'streaming' or 'live'
	 *				'page'				current page of listings.  pages begin at '1'
	 *				'per_page'			number of broadcasts to display per page
	 *				'section_id'		id of section you wish to limit list to
	 *				'playlist_id'		id of playlist you wish to limit list to
	 *				'id'				id of broadcast - useful if you only want to get details of a single broadcast
	 *				'title'				title of broadcast.  useful for searches, as this accepts incomplete titles and returns all matches.
	 *				'autoplay'			true or false.  defaults to false.  used in embed code to prevent player from immediately playing
	 *				'embed_width'		width (in pixels) that embed should be.  defaults to 640
	 *				'before' 			return broadcasts that occur before specified date.  can be a date string or integer timestamp.  note that date strings should be in standard formats.
	 *				'after' 			return broadcasts that occur after specified date.  can be a date string or integer timestamp.  note that date strings should be in standard formats.
	 *										note - if both before and after are included, broadcasts between the supplied dates are returned.
	 *				'sort_by'			data field to use to sort.  allowed fields are date, status, id, title, description. defaults to date
	 *				'sort_dir'			direction of sort.  allowed values are 'asc' (ascending) and 'desc' (descending). defaults to desc
	 *	@return false on failure, array on success.  if failed, $volar->getError() can be used to get last error string
	 */
	public function broadcasts($params = array())
	{
		return $this->request('api/client/broadcast', 'GET', $params);
	}

	/**
	 *	gets list of broadcasts that have been deleted
	 *		Note that this list is always sorted in descending order of when they were deleted, and the returned information is substantially more
	 *		sparse than what the broadcasts() call returns.  This is primarily due to the fact that the record of the broadcast has been removed from
	 *		the server, and only a subset of information remains.  This call is useful to those that want to get a list of broadcasts that need
	 *		to be removed from their own servers
	 *	@example this example pulls page 1 of deleted broadcasts from site 'mysite' that have been deleted since june 1st, 2014 (UTC time)
	 *		if($result = $v->broadcasts_deleted(array(
	 *				'site' => 'mysite',
	 *				'page' => 1,
	 *				'date_deleted_after' => '2014-06-01'
	 *			)))
	 *		{
	 *			var_dump($result['deleted']);
	 *		}
	 *	@param array $params associative array
	 *			recognized parameters in array:
	 *				- required if not authenticated.  If you use the sdk, it will authenticate you anyway :) -
	 *				'site'				slug of site to filter to.
	 *				OR
	 *				'sites'				comma-delimited list of site slugs to filter to.
	 *				- optional -
	 *				'page'				current page of listings.  pages begin at '1', and this is the default value
	 *				'per_page'			number of deleted broadcasts to display per page.  Default is '25'
	 *				'id'				id of deleted broadcast - useful if you only want to get details of a single deleted broadcast
	 *				'date_deleted_after'	Instructs the CMS to return broadcast that have been deleted AFTER the given date.  Can be a date string or integer timestamp.  Note that date strings should be in standard formats.
	 *				'date_deleted_before'	Instructs the CMS to return broadcast that have been deleted BEFORE the given date.  Can be a date string or integer timestamp.  Note that date strings should be in standard formats.
	 *	@return false on failure, array on success.  if failed, $volar->getError() can be used to get last error string
	 */
	public function broadcasts_deleted($params = array())
	{
		return $this->request('api/client/broadcast/deleted', 'GET', $params);
	}

	/**
	 *	creates a new broadcast
	 *	@param mixed $params associative array or json string
	 *		recognized parameters:
	 *			- required -
	 *				'site' OR 'sites'	slug of site to filter to.
	 *										if passing 'sites', users can include a comma-delimited list of sites.
	 *										results will reflect all broadcasts in the listed sites.
	 *				'title'				title of the broadcast
	 *				'contact_name'		contact name of person we should contact if we detect problems with this broadcast
	 *				'contact_phone'		phone we should use to contact contact_name person
	 *				'contact_sms'		sms number we should use to send text messages to contact_name person
	 *				'contact_email'		email address we should use to send emails to contact_name person
	 *					* note that contact_phone can be omitted if contact_sms is supplied, and vice-versa
	 *			- optional -
	 *				'date'				date of broadcast. Will default to current time.  can be a date string or integer timestamp.  note that date strings should be in standard formats.
	 *				'timezone'			allows you to specify what timezone this date refers to. will default to the UTC timezone. For a list of accepted timezones, see the Supported Timezones api call.
	 *				'description'		html formatted text for the description of the broadcast. matches.
	 *				'section_id'		id of section to assign broadcast to. will default to 'General'.
	 */
	public function broadcast_create($params = '')
	{
		if(is_array($params) && count($params) > 0)
		{
			$params = json_encode($params);
		}
		return $this->request('api/client/broadcast/create', 'POST', array(), $params);
	}

	/**
	 *	update a new broadcast
	 *	@param mixed $params associative array or json string
	 *		recognized parameters:
	 *			- required -
	 *				'site'				slug of site to assign broadcast to. note that if the api user does not have permission to create broadcasts on the given site, an error will be produced.
	 *				'id'				id of broadcast that you're updating
	 *			- optional -
	 *				'title'				title of the broadcast
	 *				'date'				date of broadcast. Will default to current time.  can be a date string or integer timestamp.  note that date strings should be in standard formats.
	 *				'timezone'			allows you to specify what timezone this date refers to. will default to the UTC timezone. For a list of accepted timezones, see the Supported Timezones api call.
	 *				'description'		html formatted text for the description of the broadcast. matches.
	 *				'section_id'		id of section to assign broadcast to. will default to 'General'.
	 *				'contact_name'		contact name of person we should contact if we detect problems with this broadcast
	 *				'contact_phone'		phone we should use to contact contact_name person
	 *				'contact_sms'		sms number we should use to send text messages to contact_name person
	 *				'contact_email'		email address we should use to send emails to contact_name person
	 */
	public function broadcast_update($params = '')
	{
		if(is_array($params) && count($params) > 0)
		{
			$params = json_encode($params);
		}
		return $this->request('api/client/broadcast/update', 'POST', array(), $params);
	}

	/**
	 *	delete a broadcast
	 *	@example
	 *		// find broadcast with id 123 and delete it.
	 *		$result = $v->broadcast_delete(array(
	 *			'id' => 123,
	 *			'site' => 'mysite'
	 *		));
	 *		var_dump($result['success']);	//will be True of False
	 *	@param array $params arguments related to selecting and deleting a broadcast
	 *		The following fields are required
	 *		- 'id' : id of broadcast
	 *		- 'site' : slug of site broadcast is owned by
	 *	@return array indicates success or failure.  if failed, check the getError() function for last error
	 *		'success' => True/False
	*/

	public function broadcast_delete($params = '')
	{
		if(is_array($params) && count($params) > 0)
		{
			$params = json_encode($params);
		}
		return $this->request('api/client/broadcast/delete', 'POST', array(), $params);
	}

	/**
	 *	assign a broadcast to a playlist
	 *	@example
	 *		// assign broadcast with id 1 to playlist with id 12.  note that
	 *		// both must be under the 'mysite' site.
	 *		$result = $v->broadcast_assign_playlist(array(
	 *			'id' => 1,
	 *			'site' => 'mysite',
	 *			'playlist_id' => 12
	 *		));
	 *		var_dump($result['success']);	//will be True of False
	 *	@param array $params arguments related to the attachment of broadcast to playlist.
	 *		All of the following are required
	 *			- 'id' : id of broadcast
	 *			- 'site' : slug of site broadcast and playlist belong to
	 *			- 'playlist_id' : id of playlist
	 *	@return array indicates success or failure.  if failed, check the getError() function for last error
	 *		'success' => True/False
	*/
	public function broadcast_assign_playlist($params = array())
	{
		return $this->request('api/client/broadcast/assignplaylist', 'GET', $params);
	}

	/**
	 *	remove a broadcast from a playlist
	 *	@example
	 *		// remove broadcast with id 1 from playlist with id 12.  note that
	 *		// both must be under the 'mysite' site.
	 *		$result = $v->broadcast_remove_playlist(array(
	 *			'id' => 1,
	 *			'site' => 'mysite',
	 *			'playlist_id' => 12
	 *		));
	 *		var_dump($result['success']);	//will be True of False
	 *
	 *	@param array $params arguments arguments related to the removal of broadcast from a playlist.
	 *		All of the following are required
	 *			- 'id' : id of broadcast
	 *			- 'site' : slug of site broadcast and playlist belong to
	 *			- 'playlist_id' : id of playlist
	 *	@return array indicates success or failure.  if failed, check the getError() function for last error
	 *		'success' => True/False
	 */
	public function broadcast_remove_playlist($params = array())
	{
		return $this->request('api/client/broadcast/removeplaylist', 'GET', $params);
	}

	/**
	 *	uploads an image file as the poster for a broadcast.
	 *	@param array $params associative array containing references to the broadcast you wish to upload a file for
	 *		- 'id' : id of broadcast
	 *		- 'site' : slug of site broadcast belongs to
	 *	@param string $image_path Path to file you wish to upload.
	 *		if supplied, this file is uploaded to the server and attached
	 *		to the broadcast as an image
	 *	@return array indicates success or failure.  if failed, check the getError() function for last error
	 *		'success' => True/False
	 */
	public function broadcast_poster($params = array(), $image_path = '')
	{
		if(!isset($params['id']))
		{
			$this->error = 'id is required';
			return false;
		}
		if(!file_exists($image_path))
		{
				$this->error = "\"$image_path\" does not appear to exist";
				return false;
		}
		try
		{
			$uploader = new FileUploader($this);
			$post_params = $uploader->upload($image_path);
		}
		catch(\Exception $e)
		{
			echo __LINE__;
			$this->error = $e->getMessage();
			return false;
		}
		return $this->request('api/client/broadcast/poster', 'GET', $params + $post_params);
	}

	/**
	 *	archives a broadcast
	 *	@param array $params associative array
	 *			recognized parameters in array:
	 *				- required -
	 *				'site'				slug of site to filter to.
	 *				'id'				id of broadcast
	 *				- optional -
	 *				'url'				url of video you wish to use to use to archive to this broadcast
	 *									Volar's system will download this video for transcoding, so this video file 
	 *									MUST be accessible by external systems.  If it isn't available, the archival
	 *									process will fail, even if this function returns a success.
	 *	@param string $file_path (optional) path to file you wish to upload.
	 *				Only necessary if you wish to upload a new video file to an existing broadcast and you do not
	 *				have a url to a file (see params).
	 *				If your broadcast was streamed via a different method (RTMP or production truck) & you wish to
	 *				archive the existing video data, omit this argument.
	 *	@return false on failure, array on success.  if failed, $volar->getError() can be used to get last error string
	 */
	public function broadcast_archive($params = array(), $file_path = '')
	{
		if(!isset($params['id']))
		{
			$this->error = 'id is required';
			return false;
		}
		if(empty($file_path))
		{
			return $this->request('api/client/broadcast/archive', 'GET', $params);
		}
		else
		{
			if(!file_exists($file_path))
			{
				$this->error = "\"$file_path\" does not appear to exist";
				return false;
			}
			// $post = array('archive' => '@'.ltrim($file_path,'@'));
			try
			{
				$uploader = new FileUploader($this);
				$post_params = $uploader->upload($file_path);
			}
			catch(\Exception $e)
			{
				$this->error = $e->getMessage();
				return false;
			}
			return $this->request('api/client/broadcast/archive', 'GET', $params + $post_params);
		}
	}
	/**
	 *	@category Video Clips
	 */

	/**
	 *	gets list of video clips
	 *	@param array $params associative array
	 *			recognized parameters in array:
	 *				- required -
	 *				'site'				slug of site to filter to.
	 *				- optional -
	 *				'page'				current page of listings.  pages begin at '1'
	 *				'per_page'			number of broadcasts to display per page
	 *				'section_id'		id of section you wish to limit list to
	 *				'playlist_id'		id of playlist you wish to limit list to
	 *				'id'				id of video clip - useful if you only want to get details of a single video clip
	 *				'title'				title of video clip.  useful for searches, as this accepts incomplete titles and returns all matches.
	 *				'autoplay'			true or false.  defaults to false.  used in embed code to prevent player from immediately playing
	 *				'embed_width'		width (in pixels) that embed should be.  defaults to 640
	 *				'sort_by'			data field to use to sort.  allowed fields are date_modified, id, title, description. defaults to date_modified
	 *				'sort_dir'			direction of sort.  allowed values are 'asc' (ascending) and 'desc' (descending). defaults to desc
	 *	@return false on failure, array on success.  if failed, $volar->getError() can be used to get last error string
	 */
	public function videoclips($params = array())
	{
		return $this->request('api/client/videoclip', 'GET', $params);
	}

	/**
	 *	creates a new video clip
	 *	@param mixed $params associative array or json string
	 *		recognized parameters:
	 *			- required -
	 *				'site' OR 'sites'	slug of site to filter to.
	 *										if passing 'sites', users can include a comma-delimited list of sites.
	 *										results will reflect all broadcasts in the listed sites.
	 *				'title'				title of the video clip
	 *			- optional -
	 *				'description'		html formatted text for the description of the video clip. matches.
	 *				'section_id'		id of section to assign video clip to. will default to 'General'.
	 */
	public function videoclip_create($params = '')
	{
		if(is_array($params) && count($params) > 0)
		{
			$params = json_encode($params);
		}
		return $this->request('api/client/videoclip/create', 'POST', array(), $params);
	}

	/**
	 *	update a video clip
	 *	@param mixed $params associative array or json string
	 *		recognized parameters:
	 *			- required -
	 *				'site'				slug of site to assign video clip to. note that if the api user does not have permission to create video clips on the given site, an error will be produced.
	 *				'id'				id of video clip that you're updating
	 *			- optional -
	 *				'title'				title of the video clip
	 *				'description'		html formatted text for the description of the video clip. matches.
	 *				'section_id'		id of section to assign video clip to.
	 */
	public function videoclip_update($params = '')
	{
		if(is_array($params) && count($params) > 0)
		{
			$params = json_encode($params);
		}
		return $this->request('api/client/videoclip/update', 'POST', array(), $params);
	}

	public function videoclip_delete($params = '')
	{
		if(is_array($params) && count($params) > 0)
		{
			$params = json_encode($params);
		}
		return $this->request('api/client/videoclip/delete', 'POST', array(), $params);
	}

	public function videoclip_assign_playlist($params = array())
	{
		return $this->request('api/client/videoclip/assignplaylist', 'GET', $params);
	}

	public function videoclip_remove_playlist($params = array())
	{
		return $this->request('api/client/videoclip/removeplaylist', 'GET', $params);
	}

	public function videoclip_poster($params = array(), $image_path = '')
	{
		if(!isset($params['id']))
		{
			$this->error = 'id is required';
			return false;
		}
		if(!file_exists($image_path))
		{
				$this->error = "\"$file_path\" does not appear to exist";
				return false;
		}
		try
		{
			$uploader = new FileUploader($this);
			$post_params = $uploader->upload($image_path);
		}
		catch(\Exception $e)
		{
			echo __LINE__;
			$this->error = $e->getMessage();
			return false;
		}
		return $this->request('api/client/videoclip/poster', 'GET', $params + $post_params);
	}

	/**
	 *	archives a video clip
	 *	@param array $params associative array
	 *			recognized parameters in array:
	 *				- required -
	 *				'site'				slug of site to filter to.
	 *				'id'				id of video clip
	 *	@param string $file_path (optional) path to file you wish to upload.
	 *				Only necessary if you wish to upload a new video file to an existing video clip.
	 *	@return false on failure, array on success.  if failed, $volar->getError() can be used to get last error string
	 */
	public function videoclip_archive($params = array(), $file_path = '')
	{
		if(!isset($params['id']))
		{
			$this->error = 'id is required';
			return false;
		}
		if(empty($file_path))
		{
			return $this->request('api/client/videoclip/archive', 'GET', $params);
		}
		else
		{
			if(!file_exists($file_path))
			{
				$this->error = "\"$file_path\" does not appear to exist";
				return false;
			}
			// $post = array('archive' => '@'.ltrim($file_path,'@'));
			try
			{
				$uploader = new FileUploader($this);
				$post_params = $uploader->upload($file_path);
			}
			catch(\Exception $e)
			{
				$this->error = $e->getMessage();
				return false;
			}
			return $this->request('api/client/videoclip/archive', 'GET', $params + $post_params);
		}
	}

	/**
	 *	@category Meta-data Templates
	 */

	/**
	 *	gets list of meta-data templates
	 *	@param array $params associative array
	 *			recognized parameters in array:
	 *				- required -
	 *				'site'
	 *				- optional -
	 *				'page'				current page of listings.  pages begin at '1'
	 *				'per_page'			number of broadcasts to display per page
	 *				'broadcast_id'		id of broadcast you wish to limit list to.
	 *				'section_id'		id of section you wish to limit list to.
	 *				'id'				id of template - useful if you only want to get details of a single template
	 *				'title'				title of template.  useful for searches, as this accepts incomplete titles and returns all matches.
	 *				'sort_by'			data field to use to sort.  allowed fields are id, title, description, date_modified. defaults to title
	 *				'sort_dir'			direction of sort.  allowed values are 'asc' (ascending) and 'desc' (descending). defaults to asc
	 *	@return false on failure, array on success.  if failed, $volar->getError() can be used to get last error string
	 */
	public function templates($params = array())
	{
		if(!isset($params['site']))
		{
			$this->error = '"site" parameter is required';
			return false;
		}
		return $this->request('api/client/template', 'GET', $params);
	}

	/**
	 *	creates a new meta-data template
	 *	@param mixed $params associative array or json string
	 *		recognized parameters:
	 *			- required -
	 *				'site'
	 *				'title'				title of the broadcast
	 *				'data'				array of data fields assigned to template.  should be in format:
	 *										array(
	 *											array(
	 *												"title" : (string) "field title",
	 *												"type" : (string) "type of field",
	 *												"options" : array(...)	//only include if type supports
	 *											),
	 *											...
	 *										)
	 *									supported types are:
	 * 										'single-line' - single line of text
	 *										'multi-line' - multiple-lines of text, option 'rows' (not required) is number of lines html should display as.  ex: "options": array('rows' => 4)
	 *										'checkbox' - togglable field.  value will be the title of the field.  no options.
	 *										'checkbox-list' - list of togglable fields.  values should be included in 'options' array.  ex: "options" : array("option 1", "option 2", ...)
	 *										'radio' - list of selectable fields, although only 1 can be selected at at time.  values should be included in 'options' array.  ex: "options" : array("option 1", "option 2", ...)
	 *										'dropdown' - same as radio, but displayed as a dropdown.  values should be included in 'options' array.  ex: "options" : array("option 1", "option 2", ...)
	 *										'country' - dropdown containing country names.  if you wish to specify default value, include "default_select".  this should not be passed as an option, but as a seperate value attached to the field, and accepts 2-character country abbreviation.
	 *										'state' - dropdown containing united states state names.  if you wish to specify default value, include "default_select".  this should not be passed as an option, but as a seperate value attached to the field, and accepts 2-character state abbreviation.
	 *			- optional -
	 *				'description'		text used to describe the template.
	 *				'section_id'		id of section to assign broadcast to. will default to 'General'.
	 */
	public function template_create($params = '')
	{
		if(is_array($params) && count($params) > 0)
		{
			$params = json_encode($params);
		}
		return $this->request('api/client/template/create', 'POST', array(), $params);
	}

	/**
	 *	update an existing broadcast meta-data template
	 *	@param mixed $params associative array or json string
	 *		recognized parameters:
	 *			- required -
	 *				'site'				slug of site to assign broadcast to. note that if the api user does not have permission to create broadcasts on the given site, an error will be produced.
	 *				'id'				id of broadcast that you're updating
	 *			- optional -
	 *				'title'				title of the broadcast
	 *				'data'				array of data fields assigned to template.  see template_create() for format
	 *				'description'		text for the description of the template.
	 *				'section_id'		id of section to assign broadcast to.
	 */
	public function template_update($params = '')
	{
		if(is_array($params) && count($params) > 0)
		{
			$params = json_encode($params);
		}
		return $this->request('api/client/template/update', 'POST', array(), $params);
	}


	/**
	 *	delete an existing broadcast meta-data template.  note that this does not affect template data attached to broadcasts, only the template.
	 *	@param mixed $params associative array or json string
	 *		recognized parameters:
	 *			- required -
	 *				'site'				slug of site to assign broadcast to. note that if the api user does not have permission to create broadcasts on the given site, an error will be produced.
	 *				'id'				id of broadcast that you're updating
	 */
	public function template_delete($params = '')
	{
		if(is_array($params) && count($params) > 0)
		{
			$params = json_encode($params);
		}
		return $this->request('api/client/template/delete', 'POST', array(), $params);
	}
	/**
	 *	@category Misc. Queries
	 */

	/**
	 *	gets list of sections
	 *	@param array $params associative array
	 *			recognized parameters in array:
	 *				- required -
	 *				'site' OR 'sites'	slug of site to filter to.
	 *										if passing 'sites', users can include a comma-delimited list of sites.
	 *										results will reflect all sections in the listed sites.
	 *				- optional -
	 *				'page'				current page of listings.  pages begin at '1'
	 *				'per_page'			number of broadcasts to display per page
	 *				'broadcast_id'		id of broadcast you wish to limit list to.  will always return 1
	 *				'video_id'			id of video you wish to limit list to.  will always return 1.  note this is not fully supported yet.
	 *				'id'				id of section - useful if you only want to get details of a single section
	 *				'title'				title of section.  useful for searches, as this accepts incomplete titles and returns all matches.
	 *				'sort_by'			data field to use to sort.  allowed fields are id, title. defaults to title
	 *				'sort_dir'			direction of sort.  allowed values are 'asc' (ascending) and 'desc' (descending). defaults to asc
	 *	@return false on failure, array on success.  if failed, $volar->getError() can be used to get last error string
	 */
	public function sections($params = array())
	{
		if(!isset($params['site']) && !isset($params['sites']))
		{
			$this->error = '"site" or "sites" parameter is required';
			return false;
		}
		return $this->request('api/client/section', 'GET', $params);
	}

	/**
	 *	gets list of playlists
	 *	@param array $params associative array
	 *			recognized parameters in array:
	 *				- required -
	 *				'site' OR 'sites'	slug of site to filter to.
	 *										if passing 'sites', users can include a comma-delimited list of sites.
	 *										results will reflect all playlists in the listed sites.
	 *				- optional -
	 *				'page'				current page of listings.  pages begin at '1'
	 *				'per_page'			number of broadcasts to display per page
	 *				'broadcast_id'		id of broadcast you wish to limit list to.
	 *				'video_id'			id of video you wish to limit list to.  note this is not fully supported yet.
	 *				'section_id'		id of section you wish to limit list to
	 *				'id'				id of playlist - useful if you only want to get details of a single playlist
	 *				'title'				title of playlist.  useful for searches, as this accepts incomplete titles and returns all matches.
	 *				'sort_by'			data field to use to sort.  allowed fields are id, title. defaults to title
	 *				'sort_dir'			direction of sort.  allowed values are 'asc' (ascending) and 'desc' (descending). defaults to asc
	 *	@return false on failure, array on success.  if failed, $volar->getError() can be used to get last error string
	 */
	public function playlists($params = array())
	{
		return $this->request('api/client/playlist', 'GET', $params);
	}

	public function playlist_create($params = '')
	{
		if(is_array($params) && count($params) > 0)
		{
			$params = json_encode($params);
		}
		return $this->request('api/client/playlist/create', 'POST', array(), $params);
	}

	public function playlist_update($params = '')
	{
		if(is_array($params) && count($params) > 0)
		{
			$params = json_encode($params);
		}
		return $this->request('api/client/playlist/update', 'POST', array(), $params);
	}

	public function playlist_delete($params = '')
	{
		if(is_array($params) && count($params) > 0)
		{
			$params = json_encode($params);
		}
		return $this->request('api/client/playlist/delete', 'POST', array(), $params);
	}

	/**
	 *	@internal
	 *	Get list of timezones
	 *	@deprecated
	 */
	public function timezones($params = array())
	{
		return $this->request('api/client/info/timezones', 'GET', $params);
	}

	/**
	 *	@category Utilities
	 */

	/**
	 *	@internal
	 *	submits request to $base_url through $route
	 *	@param string 	$route		api uri path (not including base_url!)
	 *	@param string 	$type		type of request.  only GET and POST are supported.  if blank, GET is assumed
	 *	@param array 	$params		associative array containing the GET parameters for the request
	 *	@param mixed 	$post_body	either a string or an array for post requests.  only used if $type is POST.  if left null, an error will be returned
	 *	@return false on failure, array on success.  if failed, $volar->getError() can be used to get last error string
	 */
	public function request($route, $type = '', $params = array(), $post_body = null)
	{
		$type = strtoupper($type ? $type : 'GET');
		$params['api_key'] = $this->api_key;
		$signature = $this->buildSignature($route, $type, $params, $post_body);

		$url = ($this->secure ? 'https://' : 'http://').$this->base_url.'/'.trim($route, '/');
		$query_string = '';
		foreach($params as $key => $value)
		{
			if(is_array($value))
			{
				foreach($value as $v_key => $v_value)
				{
					$query_string .= ($query_string ? '&' : '?') .$key .'['.urlencode($v_key).']='. urlencode($v_value);
				}
			}
			else
				$query_string .= ($query_string ? '&' : '?') .$key .'='. urlencode($value);
		}
		$query_string .= '&signature='.$signature;	//signature doesn't need to be urlencoded, as the buildSignature function does it for you.

		if(!$response = $this->execute($url.$query_string, $type, $post_body))
		{
			//error string should have already been set
			return false;
		}
		$this->debug = $url.$query_string."\n".$response;
		$json = json_decode($response, true);
		if(isset($json['error']) && !empty($json['error']))
		{
			$this->error = '('.$json['error']['code'].') '.$json['error']['message'];
			return false;
		}
		return $json;
	}

	/**
	 *	@internal
	 *	creates a signature
	 *	@param string $route		api uri path (not including base_url!)
	 *	@param string $type			type of request.  only GET and POST are supported.  if blank, GET is assumed
	 *	@param array $get_params	associative array containing the GET parameters for the request
	 *	@param mixed $post_body		either a string or an array for post requests.  only used if $type is POST, AND only used if a string
	 *	@return string urlencoded signature that should be used with requests
	 */
	public function buildSignature($route, $type = '', $get_params = array(), $post_body = '')
	{
		$type = strtoupper($type ? $type : 'GET');
		ksort($get_params);
		$stringToSign = $this->secret.$type.trim($route, '/');

		foreach($get_params as $key => $value)	//note that get_params are NOT urlencoded
		{
			if(is_array($value))
			{
				ksort($value);
				foreach($value as $v_key => $v_value)
				{
					$stringToSign .= $key.'['.$v_key.']='.$v_value;
				}
			}
			else
			{
				$stringToSign .= $key.'='.$value;
			}
		}

		if(!is_array($post_body))
			$stringToSign .= $post_body;

		$signature = base64_encode(hash('sha256', $stringToSign, true));
		$signature = urlencode(substr($signature, 0, 43));
		$signature = rtrim($signature, '=');

		return $signature;
	}
	/**
	 *	@internal
	 *	execute connection & transmission of parameters
	 */
	public function execute($url, $type, $post_body, $content_type = '', $curl_options = array())
	{
		$type = strtoupper($type);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	//need the cURL request to come back with response so sdk code can handle it.
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);	//set request type
		if($content_type)
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $content_type);
		}
		// if(is_array($post_body))
		// {
		// 	$post_fields = array();
		// 	foreach($post_body as $key => $value)
		// 	{
		// 		$post_fields[] = $key.'='.urlencode($value);
		// 	}
		// 	$post_body = implode('&', $post_fields);
		// }
		if(!empty($post_body) && $type == 'POST')
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
		}
		elseif($type == 'POST')	//$post_body is empty
		{
			$this->error = 'If type is POST, post_body is expected to be populated as an array or as a non-empty string';
			return false;
		}

		if(count($curl_options) > 0)
		{
			curl_setopt_array($ch, $curl_options);
		}

		$response = curl_exec($ch);
		if(!$response)
		{
			$error = curl_error($ch);
			curl_close($ch);
			$this->error = "cURL error: ($url) ".$error;
			return false;
		}

		curl_close($ch);

        return $response;
	}

}
