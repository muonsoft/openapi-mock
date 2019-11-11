Feature: Servers url in routing

  Scenario Outline: Sending GET to endpoints with global, path and endpoint servers
    Given I have OpenAPI specification file "servers-url-in-routing.yaml"
    When I send a "GET" request to "<url>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "key" should be equal to "value"

    Examples:
      | url                                        |
      | /global/base/path/endpoint                 |
      | /another-global/base/path/endpoint         |
      | /local/base/path/second-endpoint           |
      | /another-local/base/path/second-endpoint   |
      | /endpoint/base/path/third-endpoint         |
      | /another-endpoint/base/path/third-endpoint |


  Scenario Outline: Sending POST to non-existent not overridden endpoints
    Given I have OpenAPI specification file "servers-url-in-routing.yaml"
    When I send a "POST" request to "<url>"
    Then the response status code should be 404

    Examples:
      | url                                        |
      | /endpoint/base/path/third-endpoint         |
      | /another-endpoint/base/path/third-endpoint |


  Scenario Outline: Sending POST to existent not overridden endpoints
    Given I have OpenAPI specification file "servers-url-in-routing.yaml"
    When I send a "POST" request to "<url>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "key" should be equal to "value"

    Examples:
      | url                                      |
      | /global/base/path/third-endpoint         |
      | /another-global/base/path/third-endpoint |
