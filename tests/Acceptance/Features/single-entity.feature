Feature: Single entity

  Scenario: GET /entity | no parameters | 200 entity returned
    Given I have OpenAPI specification file "single-entity.yaml"
    When I send a "GET" request to "/entity"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "id" should exist
    And the JSON node "name" should exist
    And the JSON node "price" should exist
    And the JSON node "isActive" should exist
    And the JSON node "tags" should exist
