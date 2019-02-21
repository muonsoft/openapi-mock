Feature: Local cross reference resolving

  Scenario: GET /entities
    Given I have OpenAPI specification file "local-cross-reference.yaml"
    When I send a "GET" request to "/entities"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "[0].key" should be equal to "value"
