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

		$b = new RecentMedia();
		$b->build($action, $settings);
	}

	// Wrap functions in a class so they don't interfere with normal Tinyboard operations
	class RecentMedia {
		public function build($action, $settings) {
			global $config;

			// if ($action == 'all') {
            // copy('templates/themes/recent_media/' . $settings['basecss'], $config['dir']['home'] . $settings['css']);
            // }

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
				$query .= sprintf("SELECT *, '%s' AS `board` FROM ``posts_%s`` WHERE `files` IS NOT NULL UNION ALL ", $b_uri, $b_uri);
			};
			$query = preg_replace('/UNION ALL $/', 'ORDER BY `time` DESC LIMIT ' . (int)$settings['limit_media'], $query);

			if ($query == '') {
				error(_("Can't build the RecentMedia theme, because there are no boards to be fetched."));
			};

			$query = query($query) or error(db_error());

            $media_limit = (int)$settings['limit_media'];

            $recent_media = pick_media($query);

			return Element('themes/recent_media/recent_media.html', Array(
				'settings' => $settings,
				'config' => $config,
				'recent_media' => $recent_media,
			));
		}

        public function pick_media($query, $settings) {
            global $config;

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
        };
	};

?>
