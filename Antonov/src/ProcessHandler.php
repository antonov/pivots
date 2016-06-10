<?php

namespace Antonov\Pivots;


class ProcessHandler {
  protected $counter = 0;
  protected $type;
  protected $document;
  protected $pivot;
  protected $config;
  protected $caller;

  public function __construct($url, $type) {
    $this->caller = new Caller($url);
    $this->document = new Document();
    $this->type = $type;
    $this->readConfig();
  }

  public function launch(){
    if (isset($this->config->sitemap_selector)) {
      $this->caller->callResource();
      $this->document->setDocumentHtml($this->caller->getResponseBody());
      $links = $this->document->getUrlsListFromSitemap($this->config->sitemap_selector);
      $links[] = $this->caller->getUrl();
      foreach ($links as $link) {
        $this->pivot = new Pivot($this->type);
        $this->pivot->urlProperty = $link;
        $this->pivot->_idProperty = md5($link);
        $this->caller->setUrl($link);
        if (isset($this->config->language_selector)) {
          if($this->caller->callResource()) {
            $this->document->setDocumentHtml($this->caller->getResponseBody());
            $langs = $this->document->getAllLanguagesUrls($this->config->language_selector);
            $langs['en'] = $this->caller->getUrl();
            foreach ($langs as $lang => $lang_url) {
              $this->caller->setUrl($lang_url);
              $this->pivot->setCurrentLanguage($lang);
              if ($this->caller->callResource()) {
                $this->document->setDocumentHtml($this->caller->getResponseBody());
                foreach ($this->pivot->getFieldConfig() as $field) {
                  $this->pivot->{$field->name} = $this->document->applyFieldSettings($field);
                }
              }
            }
            $this->pivot->save();
          }
        }
      }
    }
  }

  public function launchMenu(){
    $menu_list = [];

    if (isset($this->config->language_selector)) {
      if($this->caller->callResource()) {
        $this->document->setDocumentHtml($this->caller->getResponseBody());
        $langs = $this->document->getAllLanguagesUrls($this->config->language_selector);
        $langs['en'] = $this->caller->getUrl();
        foreach ($langs as $lang => $lang_url) {
          $this->caller->setUrl($lang_url);
          if ($this->caller->callResource()) {
            $this->document->setDocumentHtml($this->caller->getResponseBody());
            $this->document->getMenuList($this->config->menu_selector, $lang, $menu_list);
          }
        }
        foreach ($menu_list as $id => $menu_item) {
          $this->pivot = new Pivot($this->type);
          $this->pivot->_idProperty = $id;
          $this->pivot->default_languageProperty = 'und';
          foreach ($menu_item as $item_lang => $item_fields) {
            $item_lang = $item_lang == 'en' ? 'und' : $item_lang;
            $this->pivot->setCurrentLanguage($item_lang);
            foreach ($item_fields as $item_field_name => $item_field_value) {
              $this->pivot->{$item_field_name} = $item_field_value;
            }
          }
          $this->pivot->save();
        }
      }
    }

  }

  public function readConfig() {
    $this->config = json_decode(file_get_contents( __DIR__ . './config/config.json'));
  }
}