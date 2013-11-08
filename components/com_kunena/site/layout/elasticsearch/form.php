<?php
/**
 * Kunena Component
 * @package Kunena.Site
 * @subpackage Layout.Elasticsearch
 *
 * @copyright (C) 2008 - 2013 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

class KunenaLayoutElasticsearchForm extends KunenaLayout
{
	public function displayModeList($id, $attributes = '') {
		$options	= array();
		$options[]	= JHtml::_('select.option',  '0', 'Search title and message content' );
		$options[]	= JHtml::_('select.option',  '1', JText::_('COM_KUNENA_SEARCH_SEARCH_TITLES') );
		$options[]	= JHtml::_('select.option',  '2', 'Search messages only' );
		$options[]	= JHtml::_('select.option',  '3', 'Search first post of topics only' );
		echo JHtml::_('select.genericlist',  $options, 'searchtype', $attributes, 'value', 'text', $this->state->get('query.searchtype'), $id );
	}

	public function displayDateList($id, $attributes = '') {
		$options	= array();
		$options[]	= JHtml::_('select.option',  '', JText::_('COM_KUNENA_SEARCH_DATE_ANY') );
		$options[]	= JHtml::_('select.option',  'lastvisit', JText::_('COM_KUNENA_SEARCH_DATE_LASTVISIT') );
		$options[]	= JHtml::_('select.option',  '-1d', JText::_('COM_KUNENA_SEARCH_DATE_YESTERDAY') );
		$options[]	= JHtml::_('select.option',  '-1w', JText::_('COM_KUNENA_SEARCH_DATE_WEEK') );
		$options[]	= JHtml::_('select.option',  '-2w',  JText::_('COM_KUNENA_SEARCH_DATE_2WEEKS') );
		$options[]	= JHtml::_('select.option',  '-1M', JText::_('COM_KUNENA_SEARCH_DATE_MONTH') );
		$options[]	= JHtml::_('select.option',  '-3M', JText::_('COM_KUNENA_SEARCH_DATE_3MONTHS') );
		$options[]	= JHtml::_('select.option',  '-6M', JText::_('COM_KUNENA_SEARCH_DATE_6MONTHS') );
		$options[]	= JHtml::_('select.option',  '-12M', JText::_('COM_KUNENA_SEARCH_DATE_YEAR') );

		echo JHtml::_('select.genericlist',  $options, 'searchdate', $attributes, 'value', 'text', $this->state->get('query.searchdate'), $id );
	}

	public function displayBeforeAfterList($id, $attributes = '') {
		$options	= array();
		$options[]	= JHtml::_('select.option',  'after', JText::_('COM_KUNENA_SEARCH_DATE_NEWER') );
		$options[]	= JHtml::_('select.option',  'before', JText::_('COM_KUNENA_SEARCH_DATE_OLDER') );
		echo JHtml::_('select.genericlist',  $options, 'beforeafter', $attributes, 'value', 'text', $this->state->get('query.beforeafter'), $id );
	}
	function displaySortByList($id, $attributes = '') {
		$options	= array();
		$options[]  = JHtml::_('select.option',	 '', JText::_('Score, Date') );
		$options[]	= JHtml::_('select.option',  'lastpost', JText::_('COM_KUNENA_SEARCH_SORTBY_POST') );
		$options[]	= JHtml::_('select.option',  'title', JText::_('COM_KUNENA_SEARCH_SORTBY_TITLE') );
//		$options[]	= JHtml::_('select.option',  'replycount', JText::_('COM_KUNENA_SEARCH_SORTBY_POSTS') );
		$options[]	= JHtml::_('select.option',  'views', JText::_('COM_KUNENA_SEARCH_SORTBY_VIEWS') );
//		$options[]	= JHtml::_('select.option',  'threadstart', JText::_('COM_KUNENA_SEARCH_SORTBY_START') );
		$options[]	= JHtml::_('select.option',  'postusername', JText::_('COM_KUNENA_SEARCH_SORTBY_USER') );
		$options[]	= JHtml::_('select.option',  'forum', JText::_('COM_KUNENA_CATEGORY') );
		echo JHtml::_('select.genericlist',  $options, 'sortby', $attributes, 'value', 'text', $this->state->get('query.sortby'), $id );
	}

	public function displayOrderList($id, $attributes = '') {
		$options	= array();
		$options[]	= JHtml::_('select.option',  'inc', JText::_('COM_KUNENA_SEARCH_SORTBY_INC') );
		$options[]	= JHtml::_('select.option',  'dec', JText::_('COM_KUNENA_SEARCH_SORTBY_DEC') );
		echo JHtml::_('select.genericlist',  $options, 'order', $attributes, 'value', 'text', $this->state->get('query.order'), $id );
	}

	public function displayLimitList($id, $attributes = '') {
		// Limit value list
		$options	= array();
		$options[]	= JHtml::_('select.option',  '5', JText::_('COM_KUNENA_SEARCH_LIMIT5') );
		$options[]	= JHtml::_('select.option',  '10', JText::_('COM_KUNENA_SEARCH_LIMIT10') );
		$options[]	= JHtml::_('select.option',  '15', JText::_('COM_KUNENA_SEARCH_LIMIT15') );
		$options[]	= JHtml::_('select.option',  '20', JText::_('COM_KUNENA_SEARCH_LIMIT20') );
		echo JHtml::_('select.genericlist',  $options, 'limit', $attributes, 'value', 'text',$this->state->get('list.limit'), $id );
	}

	public function displayCategoryList($id, $attributes = '') {
		//category select list
		$options	= array ();
		$options[]	= JHtml::_ ( 'select.option', '0', JText::_('COM_KUNENA_SEARCH_SEARCHIN_ALLCATS') );

		$cat_params = array ('sections'=>true);
		echo JHtml::_('kunenaforum.categorylist', 'catids[]', 0, $options, $cat_params, $attributes, 'value', 'text', $this->state->get('query.catids'), $id);
	}

	public function displaySearchResults() {
		if(isset($this->data)) {
			echo $this->subLayout('Search/Results');
		} else {
			echo "enter keywords and/or a username...";
		}
	}

	// public function getSuggestions($suggestion = 'simple_phrase') {

	// 	xdebug_break();

	// 	if (isset($this->data)) {
	// 		$results = $this->data->results;
	// 		$response = $results->getResponse();
	// 		$datas = $response->getData();
	// 		if (isset($datas['suggest'][$suggestion][0]['options'])) {
	// 			$suggest_data = $datas['suggest'][$suggestion][0]['options'];

	// 			$suggestions = array();
	// 			foreach ($suggest_data as $suggestion) {
	// 				$suggestions[] = ' <a href="'.$this->getSuggestUrl($suggestion['text']).'">'.$suggestion['text'].'</a>';
	// 			}
	// 			return $suggestions;
	// 		}
	// 	}

	// 	return false;
	// }

	// public function getSuggestUrl($suggestion) {

	// 	$uri = JFactory::getURI();
	// 	$query_string = $uri->getQuery();

	// 	// remove the page element of the query if it is set
	// 	parse_str($query_string,$query_array);
	// 	$query_array['q'] = $suggestion;

	// 	return ElasticSearchHelper::generateUrl(JURI::current(),$query_array);
	// }
}
