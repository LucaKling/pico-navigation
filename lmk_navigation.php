<?php

/**
 * navigation plugin which generates a better configurable navigation with endless children navigations
 *
 * @author Luca Kling <hallo@lucakling.de>
 * @former_author Ahmet Topal <http://ahmet-topal.com>
 * @license http://opensource.org/licenses/MIT
 */
class lmk_Navigation {	
	##
	# VARS
	##
	private $settings = array();
	private $navigation = array();
	private $EOL;
	
	##
	# HOOKS
	##
	
	public function get_pages(&$pages, &$current_page, &$prev_page, &$next_page) {
		$navigation = array();
		
		foreach ($pages as $page)
		{
			if (!$this->lmk_exclude($page))
			{
				$_split = explode('/', substr($page['url'], strlen($this->settings['base_url'])+1));
				$navigation = array_merge_recursive($navigation, $this->lmk_recursive($_split, $page, $current_page));
			}
		}
		
		array_multisort($navigation);
		$this->navigation = $navigation;
		$this->EOL = "\r\n";
	}
	
	public function config_loaded(&$settings) {
		$this->settings = $settings;
		
		if(!isset($this->settings['lmk_navigation']['bootstrap']))
			$this->settings['lmk_navigation']['bootstrap'] = false;
		else {
			if($this->settings['lmk_navigation']['bootstrap'] == true) { // Bootstrap additions
				if(!isset($this->settings['lmk_navigation']['activeclass']))
					$this->settings['lmk_navigation']['activeclass'] = 'active';
				if(!isset($this->settings['lmk_navigation']['id']))
					$this->settings['lmk_navigation']['id'] = '';
				if(!isset($this->settings['lmk_navigation']['class']))
					$this->settings['lmk_navigation']['class'] = 'nav navbar-nav';
				if(!isset($this->settings['lmk_navigation']['class_li']))
					$this->settings['lmk_navigation']['class_li'] = '';
				if(!isset($this->settings['lmk_navigation']['class_a']))
					$this->settings['lmk_navigation']['class_a'] = '';
				if(!isset($this->settings['lmk_navigation']['class_child_ul']))
					$this->settings['lmk_navigation']['class_child_ul'] = 'dropdown-menu';
				if(!isset($this->settings['lmk_navigation']['add_child_ul']))
					$this->settings['lmk_navigation']['add_child_ul'] = 'role="menu"';
			} else { // No Bootstrap additions
				if(!isset($this->settings['lmk_navigation']['activeclass']))
					$this->settings['lmk_navigation']['activeclass'] = 'is-active';
				if(!isset($this->settings['lmk_navigation']['id']))
					$this->settings['lmk_navigation']['id'] = 'lmk-navigation';
				if(!isset($this->settings['lmk_navigation']['class']))
					$this->settings['lmk_navigation']['class'] = 'lmk-navigation';
				if(!isset($this->settings['lmk_navigation']['class_li']))
					$this->settings['lmk_navigation']['class_li'] = 'li-item';
				if(!isset($this->settings['lmk_navigation']['class_a']))
					$this->settings['lmk_navigation']['class_a'] = 'a-item';
				if(!isset($this->settings['lmk_navigation']['class_child_ul']))
					$this->settings['lmk_navigation']['class_child_ul'] = '';
				if(!isset($this->settings['lmk_navigation']['add_child_ul']))
					$this->settings['lmk_navigation']['add_child_ul'] = '';
			}
		}
		
		// default excludes
		$this->settings['lmk_navigation']['exclude'] = array_merge_recursive(
			array('single' => array(), 'folder' => array()),
			isset($this->settings['lmk_navigation']['exclude']) ? $this->settings['lmk_navigation']['exclude'] : array()
		);
	}
	
	public function before_render(&$twig_vars, &$twig) {
		$twig_vars['lmk_navigation']['navigation'] = $this->lmk_build_navigation($this->navigation, true);
	}

	##
	# HELPER
	##
	
	private function lmk_build_navigation($navigation = array(), $start = false) {
		$id = $start ? $this->settings['lmk_navigation']['id'] : '';
		$class = $start ? $this->settings['lmk_navigation']['class'] : '';
		$class_li = $this->settings['lmk_navigation']['class_li'];
		$class_a = $this->settings['lmk_navigation']['class_a'];
		$class_child_ul = $this->settings['lmk_navigation']['class_child_ul'];
		$add_child_ul = $this->settings['lmk_navigation']['add_child_ul'];
		$child = '';
		$ul = $start ? '<ul id="%1$s" class="%2$s">' . $this->EOL . '%3$s</ul>' . $this->EOL : '<ul class="%4$s" %5$s>%1$s</ul>' . $this->EOL;
		
		if (isset($navigation['_child']))
		{
			$_child = $navigation['_child'];
			array_multisort($_child);
			
			foreach ($_child as $c)
			{
				$child .= $this->lmk_build_navigation($c);
			}
			
			$child = $start ? sprintf($ul, $id, $class, $child, $class_child_ul, $add_child_ul) : sprintf($ul, $child);
		}
		
		$li = isset($navigation['title'])
			? sprintf(
				'<li class="%1$s %5$s"><a href="%2$s" class="%1$s %6$s" title="%3$s">%3$s</a>%4$s</li>' . $this->EOL,
				$navigation['class'],
				$navigation['url'],
				$navigation['title'],
				$child,
				$class_li,
				$class_a
			)
			: $child;
		
		return $li;
	}
	
	private function lmk_exclude($page) {
		$exclude = $this->settings['lmk_navigation']['exclude'];
		$url = substr($page['url'], strlen($this->settings['base_url'])+1);
		$url = (substr($url, -1) == '/') ? $url : $url.'/';
		
		foreach ($exclude['single'] as $s)
		{	
			$s = (substr($s, -1*strlen('index')) == 'index') ? substr($s, 0, -1*strlen('index')) : $s;
			$s = (substr($s, -1) == '/') ? $s : $s.'/';
			
			if ($url == $s)
			{
				return true;
			}
		}
		
		foreach ($exclude['folder'] as $f)
		{
			$f = (substr($f, -1) == '/') ? $f : $f.'/';
			$is_index = ($f == '' || $f == '/') ? true : false;
			
			if (substr($url, 0, strlen($f)) == $f || $is_index)
			{
				return true;
			}
		}
		
		return false;
	}
	
	private function lmk_recursive($split = array(), $page = array(), $current_page = array()) {
		if (count($split) == 1)
		{			
			$is_index = ($split[0] == '') ? true : false;
			$ret = array(
				'title'			=> $page['title'],
				'url'			=> $page['url'],
				'class'			=> ($page['url'] == $current_page['url']) ? $this->settings['lmk_navigation']['activeclass'] : ''
			);
			
			$split0 = ($split[0] == '') ? '_index' : $split[0];
			return array('_child' => array($split0 => $ret));
			return $is_index ? $ret : array('_child' => array($split[0] => $ret));
		}
		else
		{
			if ($split[1] == '')
			{
				array_pop($split);
				return $this->lmk_recursive($split, $page, $current_page);
			}
			
			$first = array_shift($split);
			return array('_child' => array($first => $this->lmk_recursive($split, $page, $current_page)));
		}
	}
}
?>