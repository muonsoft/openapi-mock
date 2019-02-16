Feature: Routing

  Scenario: GET /resources
    Given I have OpenAPI specification file "routing.yaml"
    When I send a "GET" request to "/resources"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "key" should be equal to the string "value"

  Scenario: GET /resources/{resourceId}
    Given I have OpenAPI specification file "routing.yaml"
    When I send a "GET" request to "/resources/resourceId"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "key" should be equal to the string "value"
