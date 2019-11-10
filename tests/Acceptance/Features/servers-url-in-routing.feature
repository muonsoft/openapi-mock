Feature: Servers url in routing

  Scenario Outline: GET /global/base/path/entity
    Given I have OpenAPI specification file "servers-url-in-routing.yaml"
    When I send a "GET" request to "<url>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "key" should be equal to "value"

    Examples:
      | url                                |
      | /global/base/path/endpoint         |
      | /another-global/base/path/endpoint |
