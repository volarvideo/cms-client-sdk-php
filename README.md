cms-client-sdk-php
==================

PHP SDK for Volar's client sdk, Version 2 (pre-alpha)

This is a rework of the existing [PHP SDK](https://github.com/volarvideo/cms-client-sdk).  Primary purpose of the rework was to eliminate the step of uploading files directly to the volar servers - instead, when videos are archived or posters are uploaded, the files are uploaded to our remote storage and enqueued for transcode, relieving a lot of the work our servers have to do to bring content to viewers.

The downside is that the PHP sdk now has a new dependancy - the Amazon AWS SDK.  However, a composer.json file has been included in this repository, which should make things simpler.  In additions, current users of composer can easily add this to their composer.json file by adding the following:

```js
	// ....
	"repositories": [
		// ....
		{
			"type": "vcs",
			"url": "https://github.com/volarvideo/cms-client-sdk-php"
		}
	],
	// ....

	"require": {
		// ....
		"volarvideo/cms-client-sdk-php": "dev-master"
	}
	// ....
```
