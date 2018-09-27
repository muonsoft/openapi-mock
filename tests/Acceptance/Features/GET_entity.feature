Feature: GET /entities

  Scenario: GET /api/entities | no parameters | 200 entity list returned
    And I send a "GET" request to "/api/entities"
    Then the response status code should be 200
