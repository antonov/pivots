<?php

require "vendor/autoload.php";
use GuzzleHttp\Exception\ClientException;

$path = '../../growth-dev/modules/custom/growth_migrate/fixtures/common_page/';
//$docs = json_decode(file_get_contents($path . "list.json"));

$client = new GuzzleHttp\Client();
$langs = ['bg', 'cs', 'da', 'de', 'et', 'el', 'en', 'es', 'fr', 'hr', 'it', 'lv', 'lt', 'hu', 'mt', 'nl', 'pl', 'pt', 'ro', 'sk', 'sl', 'fi', 'sv', 'is', 'no'];
$fields_config = [
  [
    'name' => 'title',
    'selector' => '.layout-content h1'
  ],
  [
    'name' => 'abstract',
    'selector' => '.layout-content .abstract'
  ],
  [
    'name' => 'body',
    'selector' => '.layout-content',
    'remove' => ['h1', '.abstract', 'div.newsroom-item:not(.custom-content):not(.twitterfeed)', '#ec-widget-share-button']
  ]
];

$doc_id = 'cf3043fc0081965dafec0a6ed0e23346';
$target_pivot = $path.'document-'.$doc_id.'.json';
$url_pattern = FALSE;
$pivot = json_decode(file_get_contents($target_pivot));
$urls = (array) $pivot->fields->original_url;
foreach ($urls as $url) {
  $url = reset($url);
  if (substr($url, -3) == 'htm') {
    $url_pattern= substr($url, 0, -6).'LANGPATTERN.htm';
    break;
  }
}

$pivot_updated = FALSE;
foreach ($langs as $lang) {
  $url = preg_replace('/LANGPATTERN/', $lang, $url_pattern);
  try {
    $res = $client->get($url);
    if ($res->getStatusCode() == 200) {
      $document = new DOMWrap\Document();
      $document->html((string)$res->getBody());
      foreach ($fields_config as $field) {
        $field_values = $document->find($field['selector']);
        if (count($field_values) > 0) {
          if (isset($field['remove'])) {
            foreach ($field['remove'] as $field_to_remove) {
              $field_values->find($field_to_remove)->remove();
            }
          }
          $images = $field_values->find('a img');

          if (count($images) > 0) {
            foreach ($images as $image) {
              if ($image->parent('a')->attr('type') == 'pdf' || substr($image->parent('a')->attr('href'), -3) == 'pdf') {
                $image->parent('a')->attr('type', 'pdf');
                $image->remove();
              }
            }
          }
          $docs = $field_values->find('a img');
          if (count($docs) > 0) {
            foreach ($docs as $doc) {
              if ($doc->parent('a')->attr('type') == 'docx') {
                $doc->remove();
              }
            }
          }
          $field_values->find('a.ws-ico')->remove();
          $field_value = $field_values[0]->html();
          $field_value = empty($field_value) ? $field_values[0]->text() : $field_value;
          if (!empty($field_value)) {
            $pivot_updated = TRUE;
            $pivot->fields->{$field['name']}->{$lang}[0] = $field_value;
          }
        }
      }
    }
  } catch (ClientException $e) {}
}
if($pivot_updated) {
  file_put_contents($path.'document-' . $pivot->_id . '.json', json_encode($pivot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}
