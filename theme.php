<?php
require 'info.php';

function recent_media_build($action, $settings, $board) {
	// Possible values for $action:
	//	- all (rebuild everything, initialization)
	//	- news (news has been updated)
	//	- boards (board list changed)
	//	- post (a post has been made)
	//	- post-thread (a thread has been made)

	if ($action == 'news') {
		return;
	};

	try {
		$b = new RecentMedia();
		$b->build($action, $settings);
	}
	catch (Exception $e) {
		error_log("vichan_theme_recent_media: " . $e->getMessage());
	};
}

// Wrap functions in a class so they don't interfere with normal Tinyboard operations
class RecentMedia {
	public function get_template_html_path() {
		// compute relative path to vichan-root/templates/.

		$path = dirname(__FILE__);
		$pos = strrpos($path, '/templates/themes/');
		if ($pos === false) {
			return 'templates/themes/recent_media/recent_media.html';
		};

		$p = substr($path, $pos + strlen('/templates/'));
		$p .= '/recent_media.html';
		return $p;
	}

	public function build($action, $settings) {
		global $config;

		$this->excluded = explode(' ', $settings['exclude']);

		if ($action == 'all' || $action == 'post' || $action == 'post-thread' || $action == 'post-delete') {
			$action = generation_strategy('sb_recent_media', array());
			if ($action == 'delete') {
				file_unlink($config['dir']['home'] . $settings['html']);
			}
			elseif ($action == 'rebuild') {
				file_write($config['dir']['home'] . $settings['html'], $this->generate_html($settings));
			};
		};
	}

	// Build news page
	public function generate_html($settings) {
		global $config;

		$query = '';

		foreach (listBoards() as $board) {
			$b_uri = $board['uri'];

			if (in_array($b_uri, $this->excluded)) {
				continue;
			};
			$query .= sprintf("SELECT *, '%s' AS `board` FROM ``posts_%s`` ", $b_uri, $b_uri)
				   . "WHERE `files` LIKE '%\"type\":\"image\\\\\\\\\\\\/%' "
				   . "   OR `files` LIKE '%\"type\":\"video\\\\\\\\\\\\/%' "
				   . "UNION ALL ";
		};

		if ($query == '') {
			error(_("Can't build the RecentMedia theme, because there are no boards to be fetched."));
		};

		$query = preg_replace('/UNION ALL $/', 'ORDER BY `time` DESC LIMIT ' . (int)$settings['limit_media'], $query);

		$query = query($query) or error(db_error());

		$recent_media = $this->pick_media($query, $settings);

		$template_path = $this->get_template_html_path();
		return Element($template_path, Array(
			'settings' => $settings,
			'config' => $config,
			'recent_media' => $recent_media,
		));
	}

	public function pick_media($query, $settings) {
		global $config;

		$media_limit = (int)$settings['limit_media'];
		$recent_media = Array();

		while ($post = $query->fetch(PDO::FETCH_ASSOC)) {
			openBoard($post['board']);

			if (! isset($post['files'])) {
				continue;
			};

			$files = json_decode($post['files']);

			foreach ($files as $postfile) {
				if ($postfile->file == 'deleted'
					|| $postfile->thumb == 'file'
					|| $postfile->thumb == 'spoiler') {
					continue;
				};

				$pass = Array();

				// $board['dir']
				$pass['link'] = $config['root'] . $post['board'] . '/' . $config['dir']['res']
				. link_for($post) . '#' . $post['id'];

				$pass['src'] = $config['root'] . $post['board'] . '/' . $config['dir']['thumb'] . $postfile->thumb;
				$pass['thumbwidth'] = $postfile->thumbwidth;
				$pass['thumbheight'] = $postfile->thumbheight;

				$recent_media[] = $pass;
				if ($media_limit <= count($recent_media)) {
					return $recent_media;
				};
			};
		};

		return $recent_media;
	}
};

?>
