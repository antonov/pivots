<?php

namespace Antonov\Pivots;
use GuzzleHttp;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Psr;

class Caller {
  protected $url;
  protected $client ;
  protected $response;
  protected $domain;

  /**
  * Constructor function which defines the URL, domain name and instantiate the Guzzle client.
  * @param string $url URL to request 
  * @param string $domain Domain to request (e.g. http://domain.com)
  */
  public function __construct($url, $domain) {
    $this->setUrl($url);
    $this->setDomain($domain);
    $this->client = new GuzzleHttp\Client([
      'base_uri' => $this->getDomain(),
    ]);
  }

  /**
   * Perform the request and save the response.
   *
   * @return bool If status is 200 (OK) it will save the response
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

  /**
   * Return request body string. 
   *
   * @return string HTML of the performed request.
   */
  public function getResponseBody(){
    return (string) $this->getResponse()->getBody();
  }

  /**
   * Get client object to perform the request.
   *
   * @return GuzzleHttp\Client
   */
  public function getClient() {
    return $this->client;
  }

  /**
   * Set current URL to extract.
   *
   * @param string $url URL address to extract (e.g. /section/index_en.htm)
   */
  public function setUrl($url) {
    $this->url = $url;
  }

  /**
   * Return current extraction URL.
   *
   * @return string Current extracted URL.
   */
  public function getUrl() {
    return $this->url;
  }

  /**
  * Return the request response.
  * 
  * @return Psr\Http\Message\ResponseInterface 
  */
  public function getResponse() {
    return $this->response;
  }

  /**
  * Set the response of requested data.
  *
  * @param Psr\Http\Message\ResponseInterface $response Set the respone object from performed request.
  */
  public function setResponse(Psr\Http\Message\ResponseInterface $response) {
    $this->response = $response;
  }

  /**
   * Returns the domain of current request.
   *
   * @return string
   */
  public function getDomain() {
    return $this->domain;
  }

  /**
   * Set the domain name for the current request.
   *
   * @param string $domain
   */
  public function setDomain($domain) {
    $this->domain = $domain;
  }

}