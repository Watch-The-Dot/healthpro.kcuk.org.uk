<?php

namespace WatchTheDot\Plugins\RSSImporter\Fetch\Media;

class FetchedMedia {
    public function __construct(
        private string $url,
		private string $filename,
    ) {}

    public function get_url() {
        return $this->url;
    }

	public function get_filename() {
		return $this->filename;
	}
}