<?php

namespace Antonov\Pivots;
use DOMWrap;

class Document {
  /** @var $document DOMWrap\Document **/
  protected $document;
  /** @var $matches **/
  protected $matches;

  /**
  * Create a new Document for perform markup manipulation
  */
  public function __construct() {
    $this->setDocument(new DOMWrap\Document());
  }

  /**
  * Once the content is extracted with caller set the HTML content in DOM wrapper.
  *  
  * @param $html string Set the html of the document object
  */
  public function setDocumentHtml($html) {
    $this->getDocument()->html($html);
  }

  /**
  * Perform a selection on HTML markup with provided array of valid CSS3 selectors.
  *
  * @param $selectors array Array with valid CSS3 selectors.
  *
  * @return bool If selection has matches returns TRUE, otherwise FALSE. 
  */
  protected function performSelection($selectors) {
    foreach ($selectors as $selector) {
      $this->matches = $this->getDocument()->find($selector);
      if(count($this->matches) > 0) {
        break;
      }
    }

    return count($this->matches) > 0;
  }

  /**
  * Perform markup manipulations on extracted field content. 
  * 
  * @param $field stdClass Object with a field configuration
  *
  * @return $string Returns processed markup.
  */
  public function applyFieldSettings($field) {
    if ($this->performSelection($field->selector)) {
      if(isset($field->remove)) {
        $this->performRemoveUnnecessaryMarkup($field->remove);
      }
      if(isset($field->clean_from_icons)) {
        $this->performCleanFromIcons();
      }
      if(isset($field->extract_image)) {
        $this->performExtractImage();
      }

      return $this->getProcessedMarkup();
    }
  }

  /**
  * Returns language versions for the current content.
  *
  * @param $language_selector
  *
  * @return array Array of languages ['en' => 'index_en.htm'...]
  */
  public function getAllLanguagesUrls($language_selector) {
    $this->performSelection($language_selector);
    $langs = [];
    foreach ($this->matches as $match) {
      $langs[$match->attr('lang')] = $match->attr('href');
    }
    return $langs;
  }

  /**
  * Return a list of URIs from the sitemap page.
  *
  * @param $sitemap_selector string CSS3 Selector to retrieve website links. 
  * @param $folder string Folder of the domain to make a match and get only current website links.
  *
  * @return $links array Array of links.
  */
  public function getUrlsListFromSitemap($sitemap_selector, $folder) {
    $this->performSelection([$sitemap_selector . "[href^='" . $folder . "']"]);
    $links = [];
    foreach ($this->matches as $match) {
      $href = $match->attr('href');
      if (strpos($href, '.htm') !== FALSE) {
        $href = parse_url($href, PHP_URL_PATH);
        $links[$href] = $href;
      }
    }

    return $links;
  }

  /**
  * Parse the menu and create an array structure with links.
  *
  * @param $menu_selector string CSS3 selector to get the links.
  * @param $lang string Current request language to store the link.
  * @param $links array Previous array of links, on each request it will store the language version of the links in this array.
  * @param $is_plain bool Define if the current menu is have no depth
  *
  * @return $links array Array of links.
  */
  public function getMenuList($menu_selector, $lang, &$links, $is_plain = FALSE) {
    $this->performSelection($menu_selector);
    $weight = [1 => 0, 2 => 0, 3 => 0];
    $parent = [1 => '', 2 => '', 3 => ''];
    $level = 1;
    foreach ($this->matches as $match) {
      if (!$is_plain) {
        if ($match->parent()->hasClass('nav-item')) {
          $level = 1;
        } elseif ($match->parent()->hasClass('nav-tab-title')) {
          $level = 2;
        } elseif ($match->parent()->parent()->parent()->hasClass('nav-tab-item')){
          $level = 3;
        } else {
          continue;
        }
        $id = md5(substr($match->attr('href'), 0 , -7));
        $parent[$level] = $id;
      } else {
        $id = md5($weight[$level] + 1);
      }

      $weight[$level]++;
      $links[$id][$lang] = [
        'level' => $level,
        'title' => $match->text(),
        'weight' => $weight[$level],
        'url' => $match->attr('href'),
      ];
      if ($level > 1) {
        $links[$id][$lang]['parent'] = $parent[$level - 1];
      }
    }
    return $links;
  }

  /**
  * Get the markup from the current selector match.
  * 
  * @return $markup string The HTML markup or text from queried match.
  */
  protected function getProcessedMarkup () {
    $value = $this->matches[0]->html();
    $value = empty($value) ? $this->matches[0]->text() : $value;
    return $value;
  }

  /**
  * Remove language icons from the markup.
  *
  * @return void
  */
  protected function performCleanFromIcons() {
    $images = $this->matches->find('a img');
    if (count($images) > 0) {
      foreach ($images as $image) {
        if (count($image->parent('a')) > 0) {
          if ($image->parent('a')->attr('type') == 'pdf' || substr($image->parent('a')->attr('href'), -3) == 'pdf') {
            $image->parent('a')->attr('type', 'pdf');
            $image->remove();
          }
        }
      }
    }
    $docs = $this->matches->find('a img');
    if (count($docs) > 0) {
      foreach ($docs as $doc) {
        if (count($doc->parent('a')) > 0){
          if ($doc->parent('a')->attr('type') == 'docx') {
            $doc->remove();
          }
        }
      }
    }
    $this->matches->find('a.ws-ico')->remove();
  }

  /**
  * Remove unnecesary markup from the HTML content.
  *
  * @param $selectors array Array of CSS3 selectors to remove from the match.
  */
  protected function performRemoveUnnecessaryMarkup($selectors) {
    foreach ($selectors as $selector) {
      $this->matches->find($selector)->remove();
    }
  }

  /**
  * Extract image path from HTML img tag. 
  */
  protected function performExtractImage() {
    $image = $this->matches[0]->attr('src');
    if (!empty($image)) {
      $this->matches[0]->text($image);
    }
  }
  
  /**
  * Extract image path from inline css style.
  */
  protected function performExtractImageFromStyle() {
    $image = $this->matches[0]->attr('style');
    $pattern = '/(\/.*?\.\w{3})/i';
    $matches = [];
    preg_match($pattern, $image, $matches);
    if (count($matches > 0)) {
      $this->matches[0]->text($matches[0]);
    }
  }

  /**
  * Allows to change at runtime the HTML of the document.
  */
  protected function setDocument(DOMWrap\Document $document) {
    $this->document = $document;
  }

  /**
   * @return DOMWrap\Document Returns DOM document object.
   */
  protected function getDocument(){
    return $this->document;
  }
}