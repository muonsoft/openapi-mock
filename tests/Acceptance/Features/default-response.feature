Feature: Default response

  Scenario: GET /entity | no parameters | 200 entity returned
    Given I have OpenAPI specification file "default-response.yaml"
    When I send a "GET" request to "/entity"
    Then the response status code should be 500
    And the response should be in JSON
    And the JSON node "id" should exist
