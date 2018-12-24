Feature: Combined types

  Scenario: GET /entity | no parameters | 200 entity returned
    Given I have OpenAPI specification file "combined-types.yaml"
    When I send a "GET" request to "/entity"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "oneOfProperty" should exist
    And the JSON node "oneOfProperty.id" should exist
    And the JSON node "allOfProperty" should exist
    And the JSON node "allOfProperty.id" should exist
    And the JSON node "allOfProperty.name" should exist
    And the JSON node "allOfProperty.title" should exist
