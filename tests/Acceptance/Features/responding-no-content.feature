Feature: Responding no content

  Scenario: GET /content | Accept header is empty | 204 no content
    Given I have OpenAPI specification file "responding-no-content.yaml"
    When I add "Accept" header equal to ""
    And I send a "GET" request to "/content"
    Then the response status code should be 204
    And the response should be empty

  Scenario: GET /content | Accept header is "application/json" | 204 no content
    Given I have OpenAPI specification file "responding-no-content.yaml"
    When I add "Accept" header equal to "application/json"
    And I send a "GET" request to "/content"
    Then the response status code should be 204
    And the response should be empty
