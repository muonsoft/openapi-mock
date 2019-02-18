Feature: Local reference resolving

  Scenario: GET /entity/{id}
    Given I have OpenAPI specification file "local-reference-resolving.yaml"
    When I send a "GET" request to "/entity/123456789"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "id" should exist
    And the JSON node "tags[0]" should exist
