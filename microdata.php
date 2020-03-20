<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_thumbnails
 *
 * @copyright	Copyright © 2016 - All rights reserved.
 * @license		GNU General Public License v2.0
 * @author 		Sergio Iglesias (@sergiois)
 */
defined('_JEXEC') or die;

class PlgContentmicrodata extends JPlugin {
	public function onContentPrepare($context, &$article, &$params, $limitstart = 0)
	{
		
		if (!JFactory::getApplication()->isSite()) {
			return;
		}
		$errors = JError::getErrors();
		if(count($errors)){
			return;
		}
		$uriEnds = substr(JRequest::getURI(), -3);
		/*if($uriEnds === "amp") {
			return;
		}*/

		$view = JRequest::getCmd('view');
		
		if ($view == "article" && $context == "com_content.article"){
			$this -> injectMicrodata($article);		
		}
	}

	private function injectMicrodata($article){
			
		$document =  JFactory::getDocument();
		$config = JFactory::getConfig();		

		$siteName=$config->get('sitename');		
		$title=str_replace(array("\n", "\r", "\\"), ' ', htmlspecialchars($article->title));		
		$description=str_replace(array("\n", "\r", "\\"), ' ', htmlspecialchars($article->metadesc));		

		$rating = (int) $article->rating;				
		$ratingCount = (int) $article->rating_count;
		if($ratingCount == 0 && $rating == 0){
			$ratingCount = 1;
			$rating = 5;
		}

		$organizationName = $this->params->get('organizationName');
		$logoURL = $this->params->get('logoURL');
		//var_dump($article);		
		
		$artRoute = ContentHelperRoute::getArticleRoute($article->id, $article->catid);		
		$canonicalLink = JURI::base() . JRoute::_($artRoute);
				
		$document->addCustomTag('<link href="'.$canonicalLink.'" rel="canonical">');

		if(isset(json_decode($article->images)->image_intro)){
			$introImage = json_decode($article->images)->image_intro;
		}
		
		if(isset($introImage) && $introImage !="" ){
			$imageurl = JURI::base() . $introImage;
		}
		if(!isset($imageurl)){
			$imageurl = $logoURL;
		}

		$document->addCustomTag('<meta property="og:title" content="'.$title.'"/>');
		$document->addCustomTag('<meta property="og:description" content="'.$description.'"/>');
		$document->addCustomTag('<meta property="og:type" content="website"/>');

		$document->addCustomTag('<meta property="og:url" content="'.$canonicalLink.'"/>');

		$document->addCustomTag('<meta property="og:site_name" content="'.$siteName.'"/>');
		if(isset($imageurl)){
			$document->addCustomTag('<meta property="og:image" content="'.$imageurl.'"/>');
			$document->addCustomTag('<meta property="og:image:secure_url" content="'.$imageurl.'"/>');
			$document->addCustomTag('<meta property="og:image:type" content="image/jpeg" />');
			$document->addCustomTag('<meta property="og:image:width" content="500" />');
			$document->addCustomTag('<meta property="og:image:height" content="330" />');
		}
		
		$document->addCustomTag('<meta name="twitter:card" content="summary">');
			/*$document->addCustomTag('<meta name="twitter:site" content="' . $this->params->get('tw_card_site') . '">');
			$document->addCustomTag('<meta name="twitter:creator" content="' . $this->params->get('tw_card_site') . '">');*/
			$document->addCustomTag('<meta name="twitter:title" content="'.$title.'">');
			$document->addCustomTag('<meta name="twitter:description" content="'.$description.'">');
			if(isset($imageurl)){
				$document->addCustomTag('<meta name="twitter:image" content="'.$imageurl.'">');
				$document->addCustomTag('<meta name="twitter:image:alt" content="'.$title.'">');
			}

		$json_ld = '<script type="application/ld+json"> {'. PHP_EOL;
		$json_ld .='"@context": "http://schema.org/",'. PHP_EOL;
		$json_ld .='"@type": "Article",'. PHP_EOL;
		$json_ld .='"headline": "'.$title.'",'. PHP_EOL;
		$json_ld .='"alternativeHeadline": "'. $description. '",'. PHP_EOL;
		$json_ld .='"mainEntityOfPage": {"@type": "WebPage","@id": "'.$canonicalLink.'"},'. PHP_EOL;
		$json_ld .='"publisher": {"@type": "Organization", "name": "'.$organizationName.'", "logo": {"@type": "ImageObject","url": "'.$logoURL.'"}},'. PHP_EOL;
		
		$json_ld .='"datePublished": "'.$article->created.'",'. PHP_EOL;
		$json_ld .='"dateModified": "'.$article->modified.'",'. PHP_EOL;
		$json_ld .='"author": "'. $article->author. '",'. PHP_EOL;
		$json_ld .='"description": "'. $description. '",'. PHP_EOL;
		$json_ld .='"url": "'. $canonicalLink. '",'. PHP_EOL;
		$json_ld .='"isAccessibleForFree":true, '. PHP_EOL;
		
		//$json_ld .='"aggregateRating": {"@type": "AggregateRating", "ratingValue": "'. $rating .'", "ratingCount": "'. $ratingCount .'" },';
		
		if(isset($imageurl)){
			$json_ld .='"image":"'. $imageurl .'"'. PHP_EOL;
		}	
		$json_ld .= '}</script>';

		$document->addCustomTag($json_ld);

	}
	
	
}