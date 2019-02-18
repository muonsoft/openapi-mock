Feature: Common path parameters

  Scenario: GET /entity/{id}
    Given I have OpenAPI specification file "common-path-parameters.yaml"
    When I send a "GET" request to "/entity/123"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "key" should be equal to the string "getEntity"

  Scenario: PUT /entity/{id}
    Given I have OpenAPI specification file "common-path-parameters.yaml"
    When I send a "PUT" request to "/entity/123"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "key" should be equal to the string "putEntity"
