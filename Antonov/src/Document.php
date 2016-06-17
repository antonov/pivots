<?php

namespace Antonov\Pivots;
use DOMWrap;

class Document {
  protected $document;
  protected $matches;

  public function __construct() {
    $this->setDocument(new DOMWrap\Document());
  }

  public function setDocumentHtml($html) {
    $this->getDocument()->html($html);
  }

  protected function performSelection($selectors) {
    foreach ($selectors as $selector) {
      $this->matches = $this->getDocument()->find($selector);
      if(count($this->matches) > 0) {
        break;
      }
    }

    return count($this->matches) > 0;
  }

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

  public function getAllLanguagesUrls($language_selector) {
    $this->performSelection($language_selector);
    $langs = [];
    foreach ($this->matches as $match) {
      $langs[$match->attr('lang')] = $match->attr('href');
    }
    return $langs;
  }

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

  protected function getProcessedMarkup () {
    $value = $this->matches[0]->html();
    $value = empty($value) ? $this->matches[0]->text() : $value;
    return $value;
  }

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

  protected function performRemoveUnnecessaryMarkup($selectors) {
    foreach ($selectors as $selector) {
      $this->matches->find($selector)->remove();
    }
  }

  protected function performExtractImage() {
    $image = $this->matches[0]->attr('style');
    $pattern = '/(\/.*?\.\w{3})/i';
    $matches = [];
    preg_match($pattern, $image, $matches);
    if (count($matches > 0)) {
      $this->matches[0]->text($matches[0]);
    }
  }

  protected function setDocument(DOMWrap\Document $document) {
    $this->document = $document;
  }

  /**
   * @return DOMWrap\Document
   */
  protected function getDocument(){
    return $this->document;
  }
}