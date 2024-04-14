<?php

if (!class_exists('RecentMedia')) {
	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class RecentMedia {
		public static function build($action, $settings, $board) {
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
				$b->build_main($action, $settings);
			}
			catch (Exception $e) {
				error_log("vichan_theme_recent_media: " . $e->getMessage());
			};
		}

		public function get_template_html_path($config, $settings) {
			// compute relative path from vichan-root/templates/.

			$path = $settings['themedir'];
			$pos = strrpos($path, '/templates/themes/');
			if ($pos === false) {
				return 'templates/themes/recent_media/' . $settings['template'];
			};

			$p = substr($path, $pos + strlen('/templates/'));
			$p .= '/' . $settings['template'];
			return $p;
		}

        static function explode_noempty($str) {
            return array_filter(explode(' ', $str), function($x) {
                return $x !== '';
            });
        }

        static function join_path() {
            $paths = array();

            foreach (func_get_args() as $arg) {
                if ($arg !== '') { $paths[] = $arg; }
            }

            return preg_replace('#/+#','/',join('/', $paths));
        }

		public function build_main($action, $settings) {
			global $config;

			try {
				$this->excluded = self::explode_noempty($settings['exclude']);
				$this->included = self::explode_noempty($settings['include']);

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
			catch (Exception $e) {
                $outputpath = join_path($config['dir']['home'], $settings['html']);
				file_write($outputpath, $e->getMessage());
			};
		}

		// Build news page
		public function generate_html($settings) {
			global $config;

			$query = '';

			foreach (listBoards() as $board) {
				$b_uri = $board['uri'];

                if (0 !== count($this->included)) {
                    if (!in_array($b_uri, $this->included)) {
                        continue;
                    };
                };

				if (in_array($b_uri, $this->excluded)) {
					continue;
				};

				$query .= sprintf("SELECT *, '%s' AS `board` FROM ``posts_%s`` ", $b_uri, $b_uri)
					   . "WHERE `files` LIKE '%\"type\":\"image\\\\\\\\\\\\/%' "
					   . "   OR `files` LIKE '%\"type\":\"video\\\\\\\\\\\\/%' "
					   . "UNION ALL ";
			};

			if ($query == '') {
				error(_("Can't build the RecentMedia theme, because there are no boards to be fetched. Check 'Included boards' and 'Excluded boards'"));
			};

			$query = preg_replace('/UNION ALL $/', 'ORDER BY `time` DESC LIMIT ' . (int)$settings['limit_media'], $query);

			$query = query($query) or error(db_error());

			$recent_media = $this->pick_media($query, $settings);

			$template_path = $this->get_template_html_path($config, $settings);
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

				if (is_null($files)) {
					continue;
				};

				if ($settings['files_sort_asc'] !== '1') {
					$files = array_reverse($files);
				};

				foreach ($files as $postfile) {
					if ($postfile->file == 'deleted'
						|| $postfile->thumb == 'file'
						|| $postfile->thumb == 'spoiler') {
						continue;
					};

					$pass = Array();

					$pass['isvideo'] = str_starts_with($postfile->type, 'video');

					$pass['link'] = self::join_path(
                        $config['root'],
                        $post['board'],
                        $config['dir']['res'],
                        link_for($post) . '#' . $post['id']);

					$pass['srcimg'] = self::join_path(
                        $config['root'],
                        $post['board'],
                        $config['dir']['img'],
                        $postfile->file);

					$pass['thumbimg'] = self::join_path(
                        $config['root'],
                        $post['board'],
                        $config['dir']['thumb'],
                        $postfile->thumb);

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
};

?>
