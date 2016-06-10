<?php
/**
 * Created by PhpStorm.
 * User: antartem
 * Date: 06/06/2016
 * Time: 11:23
 */

namespace Antonov\Pivots;
use GuzzleHttp;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Psr;

class Caller {
  protected $url;
  protected $client ;
  protected $response;

  public function __construct($url) {
    $this->setUrl($url);
    $this->client = new GuzzleHttp\Client([
      'base_uri' => 'http://ec.europa.eu',
    ]);
  }

  /**
   * Return resource
   * @return bool
   */
  public function callResource() {
    try {
      $response = $this->getClient()->get($this->getUrl());
      if ($response->getStatusCode() == 200) {
        $this->setResponse($response);
        return TRUE;
      }
    } catch (ClientException $e) {}
      catch (ConnectException $e) {}
    return FALSE;
  }

  public function getResponseBody(){
    return (string) $this->getResponse()->getBody();
  }

  /**
   * Get Client
   * @return GuzzleHttp\Client
   */
  public function getClient() {
    return $this->client;
  }

  public function setUrl($url) {
    $this->url = $url;
  }

  public function getUrl() {
    return $this->url;
  }

  /** @return Psr\Http\Message\ResponseInterface */
  public function getResponse() {
    return $this->response;
  }

  public function setResponse(Psr\Http\Message\ResponseInterface $response) {
    $this->response = $response;
  }

}