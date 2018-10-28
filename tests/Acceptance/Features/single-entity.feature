Feature: Single entity

  Scenario: GET /api/entity | no parameters | 200 entity returned
    Given I have OpenAPI specification file "single-entity.yaml"
    When I send a "GET" request to "/entity"
    Then the response status code should be 200
    And the JSON node "id" should exist
    And the JSON node "name" should exist
    And the JSON node "tags" should exist
