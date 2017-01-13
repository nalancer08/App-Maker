<?php

	require dirname(__FILE__) . '/lib/lessc.inc.php';

	class painLESS {

		static function includeStyleHook($item) {
			global $site;
			$ret = false;
			$resource = $item['resource'];
			# Check file extension
			if ( strtolower( substr( $resource, -5 ) ) == '.less' ) {
				# Separate file parts
				$path = substr($resource, 0, strrpos($resource, '/'));
				$file = substr($resource, strrpos($resource, '/') + 1);
				$rel_path = str_replace($site->urlTo('/'), '', $path);
				$comp_file = str_replace('.less', '.css', $file);
				# Generate source/dest file names
				$src_file = $site->baseDir("/{$rel_path}/{$file}");
				$dest_file = $site->baseDir("/{$rel_path}/{$comp_file}");
				# Check files
				if ( file_exists($src_file) ) {
					$less = new lessc;
					$less->checkedCompile($src_file, $dest_file);
					$ret = sprintf('<link rel="stylesheet" type="text/css" href="%s">', $site->urlTo("/{$rel_path}/{$comp_file}"));
				}
			}
			return $ret;
		}

	}

	$site->registerHook('core.includeStyle', 'painLESS::includeStyleHook');

?>