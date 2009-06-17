<?php

	require_once(TOOLKIT . '/class.devkit.php');
	
	class Content_ProfileDevKit extends DevKit {
		protected $_view = '';
		protected $_xsl = '';
		protected $_records = array();
		
		public function __construct(){
			parent::__construct();
			
			$this->_title = __('Profile');
			$this->_query_string = parent::__buildQueryString(array('profile'));
			
			if (!empty($this->_query_string)) {
				$this->_query_string = '&amp;' . General::sanitize($this->_query_string);
			}
		}
		
		protected function appendJump() {
			$list = new XMLElement('ul');
			$list->setAttribute('id', 'jump');
			
			if (is_array($this->_records['general']) && !empty($this->_records['general'])) {
				$list->appendChild($this->buildJumpItem(
					__('General Details'),
					'?profile=general' . $this->_query_string,
					($this->_view == 'general')
				));
			}
			
			if (is_array($this->_records['data-sources']) && !empty($this->_records['data-sources'])) {
				$list->appendChild($this->buildJumpItem(
					__('Datasource Execution'),
					'?profile=data-sources' . $this->_query_string,
					($this->_view == 'data-sources')
				));
			}
			
			if (is_array($this->_records['events']) && !empty($this->_records['events'])) {
				$list->appendChild($this->buildJumpItem(
					__('Event Execution'),
					'?profile=events' . $this->_query_string,
					($this->_view == 'events')
				));
			}
			
			$list->appendChild($this->buildJumpItem(
				__('Full Page Render Statistics'),
				'?profile=render-statistics' . $this->_query_string,
				($this->_view == 'render-statistics')
			));
			
			if (is_array($this->_records['slow-queries']) && !empty($this->_records['slow-queries'])) {
				$list->appendChild($this->buildJumpItem(
					__('Slow Query Details'),
					'?profile=slow-queries' . $this->_query_string,
					($this->_view == 'slow-queries')
				));
			}
			
			$this->Body->appendChild($list);
		}
		
		public function appendContent() {
			$this->_view = (strlen(trim($_GET['profile'])) == 0 ? 'general' : $_GET['profile']);
			$this->_xsl = @file_get_contents($this->_pagedata['filelocation']);
			
			// Build statistics:
			$profiler = $this->_page->_Parent->Profiler;
			$dbstats = $this->_page->_Parent->Database->getStatistics();
			$this->_records = array(
				'general'			=> $profiler->retrieveGroup('General'),
				'data-sources'		=> $profiler->retrieveGroup('Datasource'),
				'events'			=> $profiler->retrieveGroup('Event'),
				'slow-queries	'	=> array()
			);
			
			if (is_array($dbstats['slow-queries']) && !empty($dbstats['slow-queries'])) {
				foreach ($dbstats['slow-queries'] as $q) {
					$records['slow-queries'][] = array($q['time'], $q['query'], null, null, false);
				}
			}
			
			$this->appendHeader();
			$this->appendNavigation();
			$this->appendJump();
			
			// Full render statistics:
			if ($this->_view == 'render-statistics') {
				$xml_generation = $profiler->retrieveByMessage('XML Generation');
				$xsl_transformation = $profiler->retrieveByMessage('XSLT Transformation');
				
				$event_total = 0;
				foreach ($records['events'] as $r) $event_total += $r[1];
				
				$ds_total = 0;
				foreach ($records['data-sources'] as $r) $ds_total += $r[1];
				
				$records = array(
					array(__('Total Database Queries'), $dbstats['queries'], NULL, NULL, false),
					array(__('Slow Queries (> 0.09s)'), count($dbstats['slow-queries']), NULL, NULL, false),
					array(__('Total Time Spent on Queries'), $dbstats['total-query-time']),
					array(__('Time Triggering All Events'), $event_total),
					array(__('Time Running All Data Sources'), $ds_total),
					array(__('XML Generation Function'), $xml_generation[1]),
					array(__('XSLT Generation'), $xsl_transformation[1]),
					array(__('Output Creation Time'), $profiler->retrieveTotalRunningTime()),
				);
				
				$dl = new XMLElement('dl', NULL, array('id' => 'render-statistics'));
				
				foreach ($records as $r) {
					$dl->appendChild(new XMLElement('dt', $r[0]));
					$dl->appendChild(new XMLElement('dd', $r[1] . (isset($r[4]) && $r[4] == false ? '' : ' s')));
				}
				
				$this->Body->appendChild($dl);
				
			} else if ($records = $this->_records[$this->_view]) {
				$ds_total = 0;
				
				$dl = new XMLElement('dl');
				$dl->setAttribute('id', $this->_view);
				
				foreach ($records as $r) {
					$dl->appendChild(new XMLElement('dt', $r[0]));
					
					if ($this->_view == 'general') {
						$dl->appendChild(new XMLElement('dd', $r[1] . ' s'));
						
					} else if ($this->_view == 'slow-queries') {
						$dl->appendChild(new XMLElement('dd', $r[1] . (isset($r[4]) && $r[4] == false ? '' : ' s')));
						
					} else {
						$dl->appendChild(new XMLElement('dd', $r[1] . ' s from ' . $r[4] . ' ' . ($r[4] == 1 ? 'query' : 'queries')));
					}
					
					$ds_total += $r[1];
				}
				
				$this->Body->appendChild($dl);
			}
		}
	}
	
?>